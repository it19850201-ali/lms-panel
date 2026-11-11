<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function scheduleSlots()
    {
        return $this->hasMany(ScheduleSlot::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function isLive(): bool
    {
        if (!$this->is_active) return false;

        $today = Carbon::today();
        return $today->gte($this->starts_at) && $today->lte($this->ends_at);
    }

    public function getFullLabelAttribute(): string
    {
        $label = $this->name;

        if ($this->isLive()) {
            $label .= ' — فعال';
        } elseif (!$this->is_active) {
            $label .= ' — بسته';
        } elseif (Carbon::today()->lt($this->starts_at)) {
            $label .= ' — پیش‌رو';
        } elseif (Carbon::today()->gt($this->ends_at)) {
            $label .= ' — پایان‌یافته';
        }

        return $label;
    }
}
