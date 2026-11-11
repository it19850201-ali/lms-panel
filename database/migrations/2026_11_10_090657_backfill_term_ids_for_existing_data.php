<?php

use App\Models\School;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // برای هر مدرسه، یه ترم پیش‌فرض بساز
        School::all()->each(function ($school) {
            $term = Term::create([
                'school_id' => $school->id,
                'name'      => 'ترم فعلی',
                'starts_at' => now()->subMonths(6)->toDateString(),
                'ends_at'   => now()->addMonths(6)->toDateString(),
                'is_active' => true,
            ]);

            // همه‌ی کلاس‌های این مدرسه رو به این ترم وصل کن
            DB::table('schedule_slots')
                ->where('school_id', $school->id)
                ->whereNull('term_id')
                ->update(['term_id' => $term->id]);
        });

        // همه‌ی دوره‌های ad-hoc رو به ترم اولین مدرسه‌شون وصل کن
        DB::table('courses')->whereNull('term_id')->update([
            'term_id' => Term::orderBy('id')->value('id'),
        ]);
    }

    public function down(): void
    {
        //
    }
};
