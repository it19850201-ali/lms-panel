<?php

namespace App\Services;

use App\Models\AcademicLevel;
use App\Helpers\PersianHelper;
use App\Models\Bell;
use App\Models\Klass;
use App\Models\ScheduleSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

class ScheduleImporter
{
    protected int $schoolId;
    protected array $stats = [
        'slots_created'   => 0,
        'slots_updated'   => 0,
        'levels_created'  => 0,
        'klasses_created' => 0,
        'teachers_created' => 0,
        'errors'          => [],
    ];

    // نقشه‌ی روزها
    protected array $dayMap = [
        'شنبه'      => 0,
        'یکشنبه'    => 1,
        'یک‌شنبه'   => 1,
        'دوشنبه'    => 2,
        'سه‌شنبه'   => 3,
        'سه شنبه'   => 3,
        'سهشنبه'    => 3,
        'چهارشنبه'  => 4,
        'چهار شنبه' => 4,
        'چهار‌شنبه'  => 4,
        'پنجشنبه'   => 5,
        'پنج‌شنبه'  => 5,
        'پنج شنبه'  => 5,
        'جمعه'      => 6,
    ];

    public function __construct(int $schoolId)
    {
        $this->schoolId = $schoolId;
    }

    public function import(UploadedFile $file): array
    {
        $rows = Excel::toArray([], $file)[0] ?? [];

        if (empty($rows)) {
            $this->stats['errors'][] = 'فایل خالی است';
            return $this->stats;
        }

        $bbb = new BBBClient();
        $sync = new KlassMoodleSync();

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;
            $this->processRow($row, $index + 1, $bbb, $sync);
        }

        return $this->stats;
    }

    protected function processRow(array $row, int $rowNum, BBBClient $bbb, KlassMoodleSync $sync): void
    {
        // ستون‌ها: پایه | کلاس | روز | زنگ | نام معلم | موبایل | درس | اتاق
        $levelName   = trim((string)($row[0] ?? ''));
        $klassName   = trim((string)($row[1] ?? ''));
        $dayName     = trim((string)($row[2] ?? ''));
        $bellName    = trim((string)($row[3] ?? ''));
        $teacherName = trim((string)($row[4] ?? ''));
        $teacherMob  = $this->normalizeDigits(trim((string)($row[5] ?? '')));
        $subject     = trim((string)($row[6] ?? ''));
        $room        = trim((string)($row[7] ?? ''));

        // ردیف خالی → skip
        if ($levelName === '' && $klassName === '' && $dayName === '' && $bellName === '') {
            return;
        }

        // اعتبارسنجی
        if ($levelName === '' || $klassName === '' || $dayName === '' || $bellName === ''
            || $teacherMob === '' || $subject === '') {
            $this->stats['errors'][] = "ردیف {$rowNum}: همه‌ی ستون‌ها (به‌جز اتاق) الزامی هستند";
            return;
        }

        // نرمال‌سازی موبایل
        if (preg_match('/^9\d{9}$/', $teacherMob)) {
            $teacherMob = '0' . $teacherMob;
        }

        if (!preg_match('/^09\d{9}$/', $teacherMob)) {
            $this->stats['errors'][] = "ردیف {$rowNum}: موبایل معلم نامعتبر ({$teacherMob})";
            return;
        }

        // روز
        $day = $this->dayMap[$dayName] ?? null;
        if ($day === null) {
            $this->stats['errors'][] = "ردیف {$rowNum}: نام روز نامعتبر ({$dayName})";
            return;
        }

        // زنگ
        $bell = Bell::where('school_id', $this->schoolId)
            ->where('name', $bellName)
            ->first();

        if (!$bell) {
            $this->stats['errors'][] = "ردیف {$rowNum}: زنگ «{$bellName}» یافت نشد. اول زنگ‌ها را تعریف کنید";
            return;
        }

        if ($bell->is_break) {
            $this->stats['errors'][] = "ردیف {$rowNum}: «{$bellName}» زنگ تفریح است، نمی‌تواند درس داشته باشد";
            return;
        }

        // پایه
        // نرمال‌سازی نام‌ها
        $levelName = PersianHelper::normalizeName($levelName);
        $klassName = PersianHelper::normalizeName($klassName);

        // پایه
        $level = AcademicLevel::where('school_id', $this->schoolId)
            ->where('name', $levelName)
            ->first();

        if (!$level) {
            $level = AcademicLevel::create([
                'school_id' => $this->schoolId,
                'name'      => $levelName,
                'order'     => 0,
            ]);
            $this->stats['levels_created']++;
        }

        // کلاس
        $klass = Klass::where('academic_level_id', $level->id)
            ->where('name', $klassName)
            ->first();

        if (!$klass) {
            $klass = Klass::create([
                'school_id'         => $this->schoolId,
                'academic_level_id' => $level->id,
                'name'              => $klassName,
            ]);
            $this->stats['klasses_created']++;

            // ساخت Moodle course
            try {
                $sync->ensureCourse($klass);
            } catch (\Throwable $e) {
                Log::warning("Moodle course creation failed for klass {$klass->id}: {$e->getMessage()}");
            }
        }
        if ($klass->wasRecentlyCreated) {
            $this->stats['klasses_created']++;
            // ساخت Moodle course
            try {
                $sync->ensureCourse($klass);
            } catch (\Throwable $e) {
                Log::warning("Moodle course creation failed for klass {$klass->id}: {$e->getMessage()}");
            }
        }

        // معلم
        $teacher = User::where('mobile', $teacherMob)->first();

        if ($teacher) {
            if ($teacher->role !== 'teacher') {
                $this->stats['errors'][] = "ردیف {$rowNum}: موبایل {$teacherMob} متعلق به یک {$teacher->role} است";
                return;
            }
            if ($teacher->school_id !== $this->schoolId) {
                $this->stats['errors'][] = "ردیف {$rowNum}: معلم {$teacherName} به مدرسه‌ی دیگری تعلق دارد";
                return;
            }
        } else {
            // ساخت معلم جدید
            $teacher = User::create([
                'name'       => $teacherName ?: 'معلم',
                'mobile'     => $teacherMob,
                'code_melli' => $this->extractCodeMelli($teacherMob),
                'email'      => $teacherMob . '@placeholder.local',
                'password'   => Hash::make($this->extractCodeMelli($teacherMob)),
                'role'       => 'teacher',
                'school_id'  => $this->schoolId,
                'status'     => 'approved',
            ]);
            $this->stats['teachers_created']++;
        }

        // چک تداخل معلم
        $teacherConflict = ScheduleSlot::where('teacher_id', $teacher->id)
            ->where('day_of_week', $day)
            ->where('bell_id', $bell->id)
            ->first();

        if ($teacherConflict && $teacherConflict->klass_id !== $klass->id) {
            $this->stats['errors'][] = "ردیف {$rowNum}: معلم {$teacher->name} در این روز/زنگ در کلاس دیگری درس دارد";
            return;
        }

        // بررسی slot موجود
        $existing = ScheduleSlot::where('klass_id', $klass->id)
            ->where('day_of_week', $day)
            ->where('bell_id', $bell->id)
            ->first();

        if ($existing) {
            // آپدیت
            $existing->update([
                'teacher_id' => $teacher->id,
                'subject'    => $subject,
                'room'       => $room ?: null,
            ]);
            $this->stats['slots_updated']++;
            return;
        }

        // ساخت slot جدید
        DB::beginTransaction();
        try {
            // BBB meeting
            $meetingId = $bbb->generateMeetingId('slot', $klass->id, $day, $bell->id);
            $meetingName = "{$klass->full_name} - {$subject}";

            $result = $bbb->createMeeting($meetingId, $meetingName);

            if (($result['returncode'] ?? '') !== 'SUCCESS') {
                DB::rollBack();
                $msg = $result['message'] ?? 'خطای نامشخص';
                $this->stats['errors'][] = "ردیف {$rowNum}: خطا در BBB — {$msg}";
                return;
            }

            ScheduleSlot::create([
                'school_id'      => $this->schoolId,
                'klass_id'       => $klass->id,
                'teacher_id'     => $teacher->id,
                'bell_id'        => $bell->id,
                'subject'        => $subject,
                'day_of_week'    => $day,
                'room'           => $room ?: null,
                'bbb_meeting_id' => $meetingId,
                'bbb_created_at' => now(),
            ]);

            DB::commit();
            $this->stats['slots_created']++;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Schedule import row error: ' . $e->getMessage());
            $this->stats['errors'][] = "ردیف {$rowNum}: {$e->getMessage()}";
        }
    }

    /**
     * چون معلم ممکنه کد ملی نداشته باشه، یه کد ملی موقت از موبایل می‌سازیم
     */
    protected function extractCodeMelli(string $mobile): string
    {
        // ۱۰ رقم آخر موبایل، فقط اگه ۱۰ رقمی بشه
        $digits = preg_replace('/\D/', '', $mobile);
        return str_pad(substr($digits, -10), 10, '0', STR_PAD_LEFT);
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
