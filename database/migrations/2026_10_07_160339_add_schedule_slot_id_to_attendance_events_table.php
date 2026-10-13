<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_events', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_slot_id')->nullable()->after('session_id');
            $table->foreign('schedule_slot_id')
                  ->references('id')->on('schedule_slots')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_events', function (Blueprint $table) {
            $table->dropForeign(['schedule_slot_id']);
            $table->dropColumn('schedule_slot_id');
        });
    }
};
