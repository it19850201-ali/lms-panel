<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klass_id')->constrained('klasses')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['جزوه', 'تکلیف', 'ویدیو', 'سایر'])->default('جزوه');
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size');
            $table->string('file_type', 100);
            $table->timestamps();

            $table->index(['klass_id', 'subject']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_materials');
    }
};
