<?php

namespace App\Services;

use App\Models\Klass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

class StudentImporter
{
    protected int $schoolId;
    protected int $klassId;

    protected array $stats = [
        'students_created' => 0,
        'students_updated' => 0,
        'errors' => [],
    ];

    public function __construct(int $schoolId, int $klassId)
    {
        $this->schoolId = $schoolId;
        $this->klassId  = $klassId;
    }

    public function import(UploadedFile $file): array
    {
        $rows = Excel::toArray([], $file)[0] ?? [];

        if (empty($rows)) {
            $this->stats['errors'][] = 'فایل خالی است';
            return $this->stats;
        }
        // ✅ چک حداکثر کاربران مدرسه
        $school = \App\Models\School::find($this->schoolId);
        if ($school) {
            $newCount = count($rows) - 1; // منهای هدر
            $currentCount = $school->current_users_count;
            $limit = $school->plan_max_total_users;

            if (($currentCount + $newCount) > $limit) {
                $remaining = max(0, $limit - $currentCount);
                $this->stats['errors'][] = "ظرفیت کاربران مدرسه پر شده. الان: {$currentCount} کاربر از {$limit}. تنها {$remaining} کاربر جدید می‌توانید اضافه کنید.";
                return $this->stats;
            }
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // هدر
                $this->processRow($row, $index + 1);
            }
        });

        return $this->stats;
    }

    protected function processRow(array $row, int $rowNum): void
    {
        // ستون‌ها: نام | نام خانوادگی | کد ملی | موبایل
        $firstName = $this->normalizeDigits(trim((string)($row[0] ?? '')));
        $lastName  = trim((string)($row[1] ?? ''));
        $codeMelli = $this->normalizeDigits(trim((string)($row[2] ?? '')));
        $mobile    = $this->normalizeDigits(trim((string)($row[3] ?? '')));

        // ردیف خالی → skip
        if ($firstName === '' && $lastName === '' && $codeMelli === '' && $mobile === '') {
            return;
        }

        // اعتبارسنجی
        if ($firstName === '' || $lastName === '' || $codeMelli === '' || $mobile === '') {
            $this->stats['errors'][] = "ردیف {$rowNum}: همه‌ی ستون‌ها الزامی هستند";
            return;
        }

        // ترمیم هوشمند صفر اول
        if (preg_match('/^\d{9}$/', $codeMelli)) {
            $codeMelli = '0' . $codeMelli;
        }
        if (preg_match('/^9\d{9}$/', $mobile)) {
            $mobile = '0' . $mobile;
        }

        if (!preg_match('/^\d{10}$/', $codeMelli)) {
            $this->stats['errors'][] = "ردیف {$rowNum}: کد ملی باید ۱۰ رقم باشد (فعلی: {$codeMelli})";
            return;
        }
        if (!preg_match('/^09\d{9}$/', $mobile)) {
            $this->stats['errors'][] = "ردیف {$rowNum}: موبایل باید ۱۱ رقم و با ۰۹ شروع شود (فعلی: {$mobile})";
            return;
        }

        $fullName = trim($firstName . ' ' . $lastName);

        // جستجوی دانش‌آموز بر اساس کد ملی
        $student = User::where('code_melli', $codeMelli)->first();

        if ($student) {
            if ($student->role !== 'student') {
                $this->stats['errors'][] = "ردیف {$rowNum}: کد ملی {$codeMelli} قبلاً برای یک {$student->role} ثبت شده";
                return;
            }

            // چک تکراری نبودن موبایل
            $mobileTakenByOther = User::where('mobile', $mobile)
                ->where('id', '!=', $student->id)
                ->exists();

            if ($mobileTakenByOther) {
                $this->stats['errors'][] = "ردیف {$rowNum}: موبایل {$mobile} متعلق به کاربر دیگری است";
                return;
            }

            $student->update([
                'name'      => $fullName,
                'mobile'    => $mobile,
                'email'     => $mobile . '@placeholder.local',
                'school_id' => $this->schoolId,
                'klass_id'  => $this->klassId,
            ]);

            $this->stats['students_updated']++;
            $this->syncToMoodle($student->fresh());
            return;
        }

        // دانش‌آموز جدید
        if (User::where('mobile', $mobile)->exists()) {
            $this->stats['errors'][] = "ردیف {$rowNum}: موبایل {$mobile} قبلاً در سیستم ثبت شده";
            return;
        }

        $newStudent = User::create([
            'name'                => $fullName,
            'mobile'              => $mobile,
            'code_melli'          => $codeMelli,
            'email'               => $mobile . '@placeholder.local',
            'password'            => Hash::make($codeMelli),
            'role'                => 'student',
            'school_id'           => $this->schoolId,
            'status'              => 'approved',
            'klass_id'            => $this->klassId,
            'password_changed_at' => null,
        ]);

        $this->stats['students_created']++;
        $this->syncToMoodle($newStudent);
    }

    /**
     * Enrol دانش‌آموز در Moodle course مربوط به کلاس
     */
    protected function syncToMoodle(User $student): void
    {
        if (!$student->klass_id) {
            return;
        }

        try {
            $klass = Klass::find($student->klass_id);
            if (!$klass) return;

            $sync = new KlassMoodleSync();
            $sync->enrollStudentInKlass($student, $klass);
        } catch (\Throwable $e) {
            \Log::warning("Failed to sync student {$student->id} to Moodle: " . $e->getMessage());
        }
    }

    protected function normalizeDigits(string $value): string
    {
        return str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹', '٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9', '0','1','2','3','4','5','6','7','8','9'],
            $value
        );
    }
}
