<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bell extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'start_time',
        'end_time',
        'order',
        'is_break',
    ];

    protected $casts = [
        'is_break' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * تبدیل "07:30" به "07:30:00" برای ذخیره در دیتابیس
     */
    public function setStartTimeAttribute($value)
    {
        $this->attributes['start_time'] = $this->normalizeTime($value);
    }

    public function setEndTimeAttribute($value)
    {
        $this->attributes['end_time'] = $this->normalizeTime($value);
    }

    /**
     * نمایش کوتاه‌شده: 07:30
     */
    public function getStartTimeShortAttribute(): string
    {
        return substr($this->start_time, 0, 5);
    }

    public function getEndTimeShortAttribute(): string
    {
        return substr($this->end_time, 0, 5);
    }

    protected function normalizeTime(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // اعداد فارسی → انگلیسی
        $value = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],
            ['0','1','2','3','4','5','6','7','8','9'],
            $value
        );

        // اگه "7:30" بود → "07:30:00"
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $m)) {
            return sprintf('%02d:%02d:00', (int)$m[1], (int)$m[2]);
        }

        // اگه "07:30:00" بود
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $value, $m)) {
            return sprintf('%02d:%02d:%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
        }

        return $value;
    }
}
