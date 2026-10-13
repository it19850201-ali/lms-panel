<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('klass_id')->constrained('klasses')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bell_id')->constrained('bells')->cascadeOnDelete();
            $table->string('subject');
            $table->tinyInteger('day_of_week');       // 0=شنبه ... 6=جمعه
            $table->string('room')->nullable();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->timestamps();

            $table->unique(['klass_id', 'day_of_week', 'bell_id'], 'unique_class_slot');
            $table->unique(['teacher_id', 'day_of_week', 'bell_id'], 'unique_teacher_slot');
            $table->index(['school_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_slots');
    }
};
