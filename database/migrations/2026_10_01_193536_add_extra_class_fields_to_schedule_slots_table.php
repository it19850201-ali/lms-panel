<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_slots', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_slots', 'is_extra')) {
                $table->boolean('is_extra')->default(false);
            }
            if (!Schema::hasColumn('schedule_slots', 'starts_at')) {
                $table->timestamp('starts_at')->nullable();
            }
            if (!Schema::hasColumn('schedule_slots', 'ends_at')) {
                $table->timestamp('ends_at')->nullable();
            }
            if (!Schema::hasColumn('schedule_slots', 'extra_scope')) {
                $table->string('extra_scope', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedule_slots', function (Blueprint $table) {
            $table->dropColumn(['is_extra', 'starts_at', 'ends_at', 'extra_scope']);
        });
    }
};
