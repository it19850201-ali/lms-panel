<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleSlot extends Model
{
    protected $fillable = [
        'school_id',
        'klass_id',
        'teacher_id',
        'bell_id',
        'subject',
        'day_of_week',
        'room',
        'course_id',
        'bbb_meeting_id',
        'bbb_created_at',
        'is_extra',
        'starts_at',
        'ends_at',
        'extra_scope',
        'is_recurring',
        'recurrence_group_id',
        'recurrence_index',
        'recurrence_total',
    ];

    protected $casts = [
        'bbb_created_at' => 'datetime',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'is_extra'       => 'boolean',
        'is_recurring' => 'boolean',
    ];

    public static array $days = [
        0 => 'شنبه',
        1 => 'یکشنبه',
        2 => 'دوشنبه',
        3 => 'سه‌شنبه',
        4 => 'چهارشنبه',
        5 => 'پنجشنبه',
        6 => 'جمعه',
    ];

    public static array $activeDays = [0, 1, 2, 3, 4, 5];
    // 0=شنبه، 1=یکشنبه، 2=دوشنبه، 3=سه‌شنبه، 4=چهارشنبه، 5=پنجشنبه
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function klass()
    {
        return $this->belongsTo(Klass::class, 'klass_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function bell()
    {
        return $this->belongsTo(Bell::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceEvent::class);
    }

    public function getDayNameAttribute(): string
    {
        return self::$days[$this->day_of_week] ?? '—';
    }

    public function isExtra(): bool
    {
        return (bool) $this->is_extra;
    }

    /**
     * برچسب زمان نمایش (برای فوق‌برنامه، تاریخ شمسی)
     */
    public function getTimeLabelAttribute(): string
    {
        if ($this->is_extra) {
            if (!$this->starts_at) return '—';
            return \App\Helpers\JalaliHelper::toJalali($this->starts_at, 'Y/m/d')
                . ' ' . $this->starts_at->format('H:i')
                . ' - ' . $this->ends_at?->format('H:i');
        }

        if ($this->bell) {
            return $this->bell->name . ' (' . $this->bell->start_time_short . ' - ' . $this->bell->end_time_short . ')';
        }

        return '—';
    }

    /**
     * آیا این جلسه فعال است (در حال برگزاری)؟
     */
    public function isLive(): bool
    {
        $now = now();

        if ($this->is_extra) {
            if (!$this->starts_at || !$this->ends_at) return false;
            return $now->gte($this->starts_at) && $now->lte($this->ends_at);
        }

        if (!$this->bell || $this->day_of_week === null) return false;

        $carbonDay = $now->dayOfWeek;
        $currentDay = $carbonDay === 6 ? 0 : $carbonDay + 1;

        if ($currentDay !== (int) $this->day_of_week) return false;

        $start = \Carbon\Carbon::parse($now->toDateString() . ' ' . $this->bell->start_time);
        $end = \Carbon\Carbon::parse($now->toDateString() . ' ' . $this->bell->end_time);

        return $now->gte($start) && $now->lte($end);
    }
}
