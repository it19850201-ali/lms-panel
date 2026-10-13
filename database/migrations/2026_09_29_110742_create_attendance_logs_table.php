<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_slot_id')->constrained('schedule_slots')->cascadeOnDelete();
            $table->date('session_date');        // برای گروه‌بندی جلسات
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('bbb_user_id')->nullable(); // برای مهمان‌ها
            $table->string('full_name');
            $table->string('role', 20);          // MODERATOR یا VIEWER
            $table->timestamp('first_joined_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();

            $table->index(['schedule_slot_id', 'session_date']);
            $table->unique(
                ['schedule_slot_id', 'session_date', 'bbb_user_id'],
                'unique_attendance_per_session'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
