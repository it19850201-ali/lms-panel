<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite نمی‌تونه ALTER COLUMN کنه، پس جدول رو دوباره می‌سازیم
        // اما چون داده داریم، این کار رو با یه روش خاص انجام می‌دیم

        // ۱. موقتاً FK رو غیرفعال کن
        DB::statement('PRAGMA foreign_keys = OFF;');

        // ۲. یه جدول موقت با ساختار درست بساز
        DB::statement('
            CREATE TABLE schedule_slots_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                school_id INTEGER NOT NULL,
                klass_id INTEGER NULL,
                teacher_id INTEGER NULL,
                bell_id INTEGER NULL,
                subject VARCHAR NOT NULL,
                day_of_week TINYINT NULL,
                room VARCHAR NULL,
                course_id INTEGER NULL,
                bbb_meeting_id VARCHAR NULL,
                bbb_created_at DATETIME NULL,
                is_extra TINYINT(1) NOT NULL DEFAULT 0,
                starts_at DATETIME NULL,
                ends_at DATETIME NULL,
                extra_scope VARCHAR(20) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
                FOREIGN KEY (klass_id) REFERENCES klasses(id) ON DELETE CASCADE,
                FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (bell_id) REFERENCES bells(id) ON DELETE CASCADE,
                FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
            )
        ');

        // ۳. داده‌ها رو منتقل کن
        DB::statement('
            INSERT INTO schedule_slots_new
            SELECT id, school_id, klass_id, teacher_id, bell_id, subject,
                   day_of_week, room, course_id, bbb_meeting_id, bbb_created_at,
                   is_extra, starts_at, ends_at, extra_scope, created_at, updated_at
            FROM schedule_slots
        ');

        // ۴. جدول قدیمی رو حذف کن
        DB::statement('DROP TABLE schedule_slots');

        // ۵. جدول جدید رو rename کن
        DB::statement('ALTER TABLE schedule_slots_new RENAME TO schedule_slots');

        // ۶. ایندکس‌ها رو بازسازی کن
        DB::statement('CREATE INDEX IF NOT EXISTS schedule_slots_school_id_is_active_index ON schedule_slots (school_id, is_extra)');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS unique_class_slot ON schedule_slots (klass_id, day_of_week, bell_id)');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS unique_teacher_slot ON schedule_slots (teacher_id, day_of_week, bell_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS schedule_slots_school_id_day_of_week_index ON schedule_slots (school_id, day_of_week)');

        // ۷. FK رو دوباره روشن کن
        DB::statement('PRAGMA foreign_keys = ON;');
    }

    public function down(): void
    {
        // برگشت‌پذیر نیست
    }
};
