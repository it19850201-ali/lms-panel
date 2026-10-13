<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_slots', function (Blueprint $table) {
            $table->string('bbb_meeting_id')->nullable()->unique();
            $table->timestamp('bbb_created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('schedule_slots', function (Blueprint $table) {
            $table->dropColumn(['bbb_meeting_id', 'bbb_created_at']);
        });
    }
};
