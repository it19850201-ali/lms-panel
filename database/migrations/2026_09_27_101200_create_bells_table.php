<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');                 // "زنگ اول"، "تفریح"
            $table->time('start_time');             // 07:30:00
            $table->time('end_time');               // 08:15:00
            $table->integer('order')->default(0);
            $table->boolean('is_break')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bells');
    }
};
