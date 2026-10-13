<?php

use App\Helpers\PersianHelper;
use App\Models\AcademicLevel;
use App\Models\Klass;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // === ۱. ادغام پایه‌های تکراری (هفتم فارسی / هفتم انگلیسی) ===
        $levels = AcademicLevel::orderBy('id')->get();
        $seen = [];

        foreach ($levels as $level) {
            $normalized = PersianHelper::normalizeName($level->name);
            $key = $level->school_id . '|' . $normalized;

            if (isset($seen[$key])) {
                // این تکراریه → ادغام با اون یکی
                $masterLevel = $seen[$key];

                // کلاس‌های این level تکراری رو منتقل کن به masterLevel
                Klass::where('academic_level_id', $level->id)->update([
                    'academic_level_id' => $masterLevel->id,
                ]);

                // حذف level تکراری
                $level->delete();
            } else {
                // این master هست
                $seen[$key] = $level;

                // نرمال‌سازی نام
                if ($level->name !== $normalized) {
                    $level->update(['name' => $normalized]);
                }
            }
        }

        // === ۲. ادغام کلاس‌های تکراری (۷۰۱ فارسی / 701 انگلیسی) ===
        $klasses = Klass::orderBy('id')->get();
        $seenKlasses = [];

        foreach ($klasses as $klass) {
            $normalizedName = PersianHelper::normalizeName($klass->name);
            $key = $klass->academic_level_id . '|' . $normalizedName;

            if (isset($seenKlasses[$key])) {
                $masterKlass = $seenKlasses[$key];

                // دانش‌آموزان این کلاس تکراری → منتقل کن
                \App\Models\User::where('klass_id', $klass->id)->update([
                    'klass_id' => $masterKlass->id,
                ]);

                // ScheduleSlot های این کلاس → منتقل کن
                \App\Models\ScheduleSlot::where('klass_id', $klass->id)->update([
                    'klass_id' => $masterKlass->id,
                ]);

                // حذف کلاس تکراری
                $klass->delete();
            } else {
                $seenKlasses[$key] = $klass;

                if ($klass->name !== $normalizedName) {
                    $klass->update(['name' => $normalizedName]);
                }
            }
        }
    }

    public function down(): void
    {
        // برگشت‌پذیر نیست
    }
};
