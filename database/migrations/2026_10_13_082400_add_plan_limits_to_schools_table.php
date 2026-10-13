<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->integer('plan_max_total_users')->default(500)->after('is_active');
            $table->integer('plan_max_users_per_class')->default(50)->after('plan_max_total_users');
            $table->integer('plan_max_cameras')->default(5)->after('plan_max_users_per_class');
            $table->integer('plan_max_concurrent')->default(5)->after('plan_max_cameras');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'plan_max_total_users',
                'plan_max_users_per_class',
                'plan_max_cameras',
                'plan_max_concurrent',
            ]);
        });
    }
};
