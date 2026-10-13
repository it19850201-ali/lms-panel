<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_events', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('bbb_attendee_id');
            $table->string('full_name');
            $table->string('role')->default('viewer');
            $table->enum('event_type', ['join', 'leave']);
            $table->timestamp('occurred_at');
            $table->string('source')->default('webhook');
            $table->timestamps();

            $table->index(['meeting_id', 'bbb_attendee_id', 'occurred_at'], 'idx_ae_meeting_attendee_time');
            $table->index(['user_id', 'meeting_id'], 'idx_ae_user_meeting');

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_events');
    }
};
