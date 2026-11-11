<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_slots', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->after('school_id')
                  ->constrained('terms')->nullOnDelete();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->after('user_id')
                  ->constrained('terms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schedule_slots', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn('term_id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn('term_id');
        });
    }
};
