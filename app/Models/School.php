<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'city',
        'phone',
        'address',
        'is_active',
        'plan_max_total_users',
        'plan_max_users_per_class',
        'plan_max_cameras',
        'plan_max_concurrent',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function generateSlug(string $name): string
    {
        $slug = preg_replace('/\s+/', '-', trim($name));
        $slug = preg_replace('/[^\p{L}\p{N}\-]/u', '', $slug);
        $base = $slug;
        $i = 1;
        while (self::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /**
     * تعداد کاربران فعلی (معلم + دانش‌آموز)
     */
    public function getCurrentUsersCountAttribute(): int
    {
        return $this->users()->whereIn('role', ['teacher', 'student'])->count();
    }

    /**
     * آیا مدرسه می‌تونه کاربر جدید اضافه کنه؟
     */
    public function canAddUsers(int $count = 1): bool
    {
        $current = $this->current_users_count;
        return ($current + $count) <= $this->plan_max_total_users;
    }

    /**
     * تعداد جلسات هم‌زمان فعلی
     */
    public function getConcurrentSessionsCountAttribute(): int
    {
        $now = now();
        $carbonDay = $now->dayOfWeek;
        $currentDay = $carbonDay === 6 ? 0 : $carbonDay + 1;

        $regularCount = ScheduleSlot::where('school_id', $this->id)
            ->where('is_extra', false)
            ->where('day_of_week', $currentDay)
            ->whereHas('bell', function ($q) use ($now) {
                $currentTime = $now->format('H:i:s');
                $q->where('start_time', '<=', $currentTime)
                  ->where('end_time', '>=', $currentTime);
            })
            ->count();

        $extraCount = ScheduleSlot::where('school_id', $this->id)
            ->where('is_extra', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->count();

        return $regularCount + $extraCount;
    }

    /**
     * آیا مدرسه می‌تونه جلسه‌ی هم‌زمان جدید داشته باشه؟
     */
    public function canStartConcurrentSession(): bool
    {
        return $this->concurrent_sessions_count < $this->plan_max_concurrent;
    }
}
