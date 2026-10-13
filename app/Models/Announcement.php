<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'school_id',
        'author_id',
        'title',
        'body',
        'audience_type',
        'klass_id',
        'priority',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    public static array $priorities = [
        'normal'    => ['label' => 'عادی',  'color' => 'blue',   'icon' => '📢'],
        'important' => ['label' => 'مهم',   'color' => 'orange', 'icon' => '⚠️'],
        'urgent'    => ['label' => 'فوری',  'color' => 'red',    'icon' => '🚨'],
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function klass()
    {
        return $this->belongsTo(Klass::class, 'klass_id');
    }

    public function reads()
    {
        return $this->hasMany(\App\Models\AnnouncementRead::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->lt(now());
    }

    public function isVisible(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    public function getPriorityInfoAttribute(): array
    {
        return self::$priorities[$this->priority] ?? self::$priorities['normal'];
    }

    public function isReadBy(?int $userId): bool
    {
        if (!$userId) return false;
        return $this->reads()->where('user_id', $userId)->exists();
    }

    public function getAudienceLabelAttribute(): string
    {
        return match ($this->audience_type) {
            'class'    => 'کلاس ' . ($this->klass?->name ?? '?'),
            'teachers' => 'همه‌ی معلمان',
            default    => 'همه‌ی مدرسه',
        };
    }

    /**
     * اسکوپ: اطلاعیه‌های قابل مشاهده برای یه کاربر
     */
    public function scopeVisibleFor($query, User $user)
    {
        $query->where('school_id', $user->school_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });

        if ($user->isStudent()) {
            $query->where(function ($q) use ($user) {
                $q->where('audience_type', 'all')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('audience_type', 'class')
                         ->where('klass_id', $user->klass_id);
                  });
            });
        } elseif ($user->isTeacher()) {
            $query->where(function ($q) {
                $q->whereIn('audience_type', ['all', 'teachers']);
            });
        }

        return $query->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'important' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc');
    }
}
