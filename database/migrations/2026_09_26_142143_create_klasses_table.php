<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klasses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_level_id')->constrained()->cascadeOnDelete();
            $table->string('name');           // ۷۰۱، ۷۰۲، گروه A، ...
            $table->timestamps();

            $table->unique(['academic_level_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klasses');
    }
};
