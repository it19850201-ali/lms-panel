<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_slots', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_group_id')->nullable(); // برای گروه‌بندی جلسات
            $table->integer('recurrence_index')->nullable();   // مثلاً جلسه ۳
            $table->integer('recurrence_total')->nullable();   // از ۱۶ جلسه
        });
    }

    public function down(): void
    {
        Schema::table('schedule_slots', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'recurrence_group_id', 'recurrence_index', 'recurrence_total']);
        });
    }
};
