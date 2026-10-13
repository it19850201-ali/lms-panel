<?php

namespace App\Console\Commands;

use App\Models\AcademicLevel;
use App\Models\Klass;
use App\Models\ScheduleSlot;
use App\Models\User;
use App\Services\BBBClient;
use Illuminate\Console\Command;

class CleanupDuplicateKlasses extends Command
{
    protected $signature = 'cleanup:duplicate-klasses {--dry-run : فقط نمایش بده، پاک نکن}';
    protected $description = 'ادغام و پاک‌سازی کلاس‌های تکراری';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // نقشه: کلاس تکراری → کلاس اصلی
        $map = [
            8 => 1, // هفتم 701 → پایه هفتم 701
            9 => 3, // هشتم 801 → پایه هشتم 801
        ];

        $bbb = new BBBClient();

        foreach ($map as $dupId => $masterId) {
            $dup = Klass::find($dupId);
            $master = Klass::find($masterId);

            if (!$dup || !$master) {
                $this->warn("Skip: Klass {$dupId} or {$masterId} not found");
                continue;
            }

            $this->info("=== Klass {$dupId} ({$dup->full_name}) → Klass {$masterId} ({$master->full_name}) ===");

            // ۱. انتقال دانش‌آموزان (اگه داشت)
            $studentCount = User::where('klass_id', $dupId)->count();
            if ($studentCount > 0) {
                $this->line("  Moving {$studentCount} students...");
                if (!$dryRun) {
                    User::where('klass_id', $dupId)->update(['klass_id' => $masterId]);
                }
            }

            // ۲. انتقال یا حذف slot ها
            $slots = ScheduleSlot::where('klass_id', $dupId)->get();
            foreach ($slots as $slot) {
                // چک تداخل با master
                $conflict = ScheduleSlot::where('klass_id', $masterId)
                    ->where('day_of_week', $slot->day_of_week)
                    ->where('bell_id', $slot->bell_id)
                    ->exists();

                if ($conflict) {
                    $this->warn("  Skip slot {$slot->id} ({$slot->subject}) - تداخل با master");
                    if (!$dryRun) {
                        try {
                            if ($slot->bbb_meeting_id) $bbb->endMeeting($slot->bbb_meeting_id);
                        } catch (\Throwable $e) {}
                        $slot->delete();
                    }
                    continue;
                }

                // چک تداخل معلم
                $teacherConflict = ScheduleSlot::where('teacher_id', $slot->teacher_id)
                    ->where('day_of_week', $slot->day_of_week)
                    ->where('bell_id', $slot->bell_id)
                    ->where('id', '!=', $slot->id)
                    ->exists();

                if ($teacherConflict) {
                    $this->warn("  Skip slot {$slot->id} ({$slot->subject}) - تداخل معلم");
                    if (!$dryRun) {
                        try {
                            if ($slot->bbb_meeting_id) $bbb->endMeeting($slot->bbb_meeting_id);
                        } catch (\Throwable $e) {}
                        $slot->delete();
                    }
                    continue;
                }

                $this->line("  Move slot {$slot->id} ({$slot->subject})");
                if (!$dryRun) {
                    $slot->update(['klass_id' => $masterId]);
                }
            }

            // ۳. حذف کلاس تکراری
            $this->line("  Delete Klass {$dupId}");
            if (!$dryRun) {
                $dup->delete();
            }
        }

        // ۴. حذف پایه‌های خالی
        foreach ([4, 5] as $levelId) {
            $level = AcademicLevel::find($levelId);
            if (!$level) continue;

            if ($level->klasses()->count() === 0) {
                $this->line("Delete empty Level {$levelId} ({$level->name})");
                if (!$dryRun) {
                    $level->delete();
                }
            } else {
                $this->warn("Level {$levelId} still has klasses, skipping");
            }
        }

        // ۵. تمیز کردن نام پایه‌ها (پایه هفتم → هفتم)
        foreach ([1, 2, 3] as $levelId) {
            $level = AcademicLevel::find($levelId);
            if (!$level) continue;

            if (str_starts_with($level->name, 'پایه ')) {
                $newName = str_replace('پایه ', '', $level->name);

                $exists = AcademicLevel::where('school_id', $level->school_id)
                    ->where('name', $newName)
                    ->where('id', '!=', $level->id)
                    ->exists();

                if ($exists) {
                    $this->warn("Level {$levelId}: '{$newName}' already exists, skipping rename");
                    continue;
                }

                $this->line("Rename Level {$levelId}: '{$level->name}' → '{$newName}'");
                if (!$dryRun) {
                    $level->update(['name' => $newName]);
                }
            }
        }

        $this->info("\n" . ($dryRun ? '✅ Dry-run کامل شد' : '✅ پاک‌سازی کامل شد'));

        return self::SUCCESS;
    }
}
