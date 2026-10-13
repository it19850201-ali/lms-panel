<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('student');
            $table->foreignId('school_id')->nullable()
                  ->constrained('schools')->nullOnDelete();
            $table->string('status', 20)->default('approved');
            $table->integer('moodle_user_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn(['role', 'school_id', 'status', 'moodle_user_id']);
        });
    }
};
