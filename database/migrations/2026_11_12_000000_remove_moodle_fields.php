<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('ALTER TABLE courses MODIFY moodle_course_id INT NULL');
        }

        Schema::table('klasses', function (Blueprint $table) {
            if (Schema::hasColumn('klasses', 'moodle_course_id')) {
                $table->dropColumn('moodle_course_id');
            }
            if (Schema::hasColumn('klasses', 'moodle_synced_at')) {
                $table->dropColumn('moodle_synced_at');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'moodle_user_id')) {
                $table->dropColumn('moodle_user_id');
            }
        });
    }

    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('ALTER TABLE courses MODIFY moodle_course_id INT NOT NULL');
        }

        Schema::table('klasses', function (Blueprint $table) {
            $table->integer('moodle_course_id')->nullable();
            $table->timestamp('moodle_synced_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('moodle_user_id')->nullable();
        });
    }
};
