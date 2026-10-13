<?php

namespace App\Services;

use App\Models\Klass;
use App\Models\ScheduleSlot;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class KlassMoodleSync
{
    protected MoodleClient $moodle;

    // roleid در Moodle
    const ROLE_STUDENT        = 5;
    const ROLE_TEACHER        = 4;
    const ROLE_EDITING_TEACHER = 3;

    public function __construct(?MoodleClient $moodle = null)
    {
        $this->moodle = $moodle ?? new MoodleClient();
    }

    /**
     * ساخت دوره در Moodle اگر وجود نداره
     */
    public function ensureCourse(Klass $klass): ?int
    {
        if ($klass->moodle_course_id) {
            return $klass->moodle_course_id;
        }

        try {
            $shortName = 'klass-' . $klass->id . '-' . time();

            $result = $this->moodle->createCourse(
                $klass->full_name,
                $shortName
            );

            $courseId = $result[0]['id'] ?? null;

            if ($courseId) {
                $klass->update(['moodle_course_id' => $courseId]);
                Log::info("Moodle course created for Klass {$klass->id}", ['courseid' => $courseId]);
                return $courseId;
            }

            Log::error("Moodle course creation returned no ID", ['result' => $result]);
            return null;

        } catch (\Throwable $e) {
            Log::error("Moodle course creation failed: {$e->getMessage()}", ['klass_id' => $klass->id]);
            return null;
        }
    }

    /**
     * اطمینان از وجود کاربر در Moodle
     */
    protected function ensureMoodleUser(User $user): ?int
    {
        if ($user->moodle_user_id) {
            return $user->moodle_user_id;
        }

        try {
            $nameParts = explode(' ', trim($user->name), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '.';

            $result = $this->moodle->createOrGetUser($user->email, $firstName, $lastName);
            $moodleUserId = $result['id'] ?? null;

            if ($moodleUserId) {
                $user->update(['moodle_user_id' => $moodleUserId]);
                return $moodleUserId;
            }

            return null;

        } catch (\Throwable $e) {
            Log::warning("Failed to ensure Moodle user for user {$user->id}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Enrol یک کاربر در یک دوره با نقش مشخص
     */
    protected function enroll(User $user, int $courseId, int $roleId): bool
    {
        $moodleUserId = $this->ensureMoodleUser($user);

        if (!$moodleUserId) {
            return false;
        }

        try {
            $this->moodle->enrolUser($moodleUserId, $courseId, $roleId);
            return true;
        } catch (\Throwable $e) {
            // اگه قبلاً enrol شده، Moodle ممکنه خطا بده که نادیده می‌گیریم
            Log::info("Enrol attempt for user {$user->id} in course {$courseId}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * همگام‌سازی کامل یک Klass
     */
    public function syncKlass(Klass $klass): array
    {
        $stats = [
            'course_created'  => false,
            'students_synced' => 0,
            'teachers_synced' => 0,
            'errors'          => [],
        ];

        // ۱. ساخت course
        $wasNew = empty($klass->moodle_course_id);
        $courseId = $this->ensureCourse($klass);

        if (!$courseId) {
            $stats['errors'][] = 'خطا در ساخت دوره در Moodle';
            return $stats;
        }

        $stats['course_created'] = $wasNew;

        // ۲. دانش‌آموزان
        $klass->load('students');
        foreach ($klass->students as $student) {
            if ($this->enroll($student, $courseId, self::ROLE_STUDENT)) {
                $stats['students_synced']++;
            } else {
                $stats['errors'][] = "دانش‌آموز: {$student->name}";
            }
        }

        // ۳. معلم‌ها (از ScheduleSlots)
        $teacherIds = ScheduleSlot::where('klass_id', $klass->id)
            ->pluck('teacher_id')
            ->unique()
            ->filter();

        foreach ($teacherIds as $teacherId) {
            $teacher = User::find($teacherId);
            if (!$teacher) continue;

            if ($this->enroll($teacher, $courseId, self::ROLE_EDITING_TEACHER)) {
                $stats['teachers_synced']++;
            } else {
                $stats['errors'][] = "معلم: {$teacher->name}";
            }
        }

        // ۴. آپدیت زمان آخرین sync
        $klass->update(['moodle_synced_at' => now()]);

        return $stats;
    }

    /**
     * همگام‌سازی همه‌ی Klass های یک مدرسه
     */
    public function syncSchool(int $schoolId): array
    {
        $klasses = Klass::where('school_id', $schoolId)->get();

        $total = [
            'klasses_total'   => $klasses->count(),
            'klasses_ok'      => 0,
            'klasses_failed'  => 0,
            'students_total'  => 0,
            'teachers_total'  => 0,
            'errors'          => [],
        ];

        foreach ($klasses as $klass) {
            try {
                $result = $this->syncKlass($klass);
                $total['students_total'] += $result['students_synced'];
                $total['teachers_total'] += $result['teachers_synced'];

                if (empty($result['errors'])) {
                    $total['klasses_ok']++;
                } else {
                    $total['klasses_failed']++;
                    foreach ($result['errors'] as $e) {
                        $total['errors'][] = "[{$klass->full_name}] {$e}";
                    }
                }
            } catch (\Throwable $e) {
                $total['klasses_failed']++;
                $total['errors'][] = "[{$klass->full_name}] {$e->getMessage()}";
            }
        }

        return $total;
    }

    /**
     * Enrol یک دانش‌آموز تکی (برای استفاده در ایمپورت اکسل)
     */
    public function enrollStudentInKlass(User $student, Klass $klass): bool
    {
        $courseId = $this->ensureCourse($klass);

        if (!$courseId) {
            Log::warning("Cannot enroll student {$student->id}: no Moodle course for klass {$klass->id}");
            return false;
        }

        return $this->enroll($student, $courseId, self::ROLE_STUDENT);
    }
}
