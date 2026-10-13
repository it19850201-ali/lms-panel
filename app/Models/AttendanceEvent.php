<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceEvent extends Model
{
    protected $fillable = [
        'meeting_id',
        'session_id',
        'schedule_slot_id',
        'user_id',
        'bbb_attendee_id',
        'full_name',
        'role',
        'event_type',
        'occurred_at',
        'source',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    protected ?AttendanceEvent $cachedLeave = null;
    protected bool $leaveResolved = false;

    /** رویداد خروج متناظر با این ورود */
    public function matchingLeave(): ?AttendanceEvent
    {
        if ($this->event_type !== 'join') {
            return null;
        }

        if (!$this->leaveResolved) {
            $this->cachedLeave = static::where('meeting_id', $this->meeting_id)
                ->where('user_id', $this->user_id)
                ->where('event_type', 'leave')
                ->where('occurred_at', '>=', $this->occurred_at)
                ->orderBy('occurred_at')
                ->first();
            $this->leaveResolved = true;
        }

        return $this->cachedLeave;
    }

    public function calculateDuration(): int
    {
        if ($this->event_type !== 'join') {
            return 0;
        }

        $end = $this->matchingLeave()?->occurred_at ?? now();
        return (int) $this->occurred_at->diffInMinutes($end);
    }

    /** ورودهایی که خروج متناظر ندارند */
    public function scopeOpenJoins($query)
    {
        return $query->where('event_type', 'join')
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')
                  ->from('attendance_events as l')
                  ->whereColumn('l.meeting_id', 'attendance_events.meeting_id')
                  ->whereColumn('l.user_id', 'attendance_events.user_id')
                  ->where('l.event_type', 'leave')
                  ->whereColumn('l.occurred_at', '>=', 'attendance_events.occurred_at');
            });
    }

    /** پایان جلسه‌ی این ورود (از روی زنگ یا فوق‌برنامه) */
    public function sessionEnd(): ?\Carbon\Carbon
    {
        $slot = $this->scheduleSlot;
        if (!$slot) return null;

        if ($slot->is_extra) {
            return $slot->ends_at ? \Carbon\Carbon::parse($slot->ends_at) : null;
        }

        if (!$slot->bell) return null;

        return \Carbon\Carbon::parse(
            $this->occurred_at->toDateString() . ' ' . $slot->bell->end_time
        );
    }

    /** ثبت یک رویداد خروج برای همین کاربر و جلسه */
    public function recordLeave(\Carbon\Carbon $at, string $source = 'system'): AttendanceEvent
    {
        return static::create([
            'meeting_id'       => $this->meeting_id,
            'session_id'       => $this->session_id,
            'schedule_slot_id' => $this->schedule_slot_id,
            'user_id'          => $this->user_id,
            'bbb_attendee_id'  => $this->bbb_attendee_id,
            'full_name'        => $this->full_name,
            'role'             => $this->role,
            'event_type'       => 'leave',
            'occurred_at'      => $at,
            'source'           => $source,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scheduleSlot(): BelongsTo
    {
        return $this->belongsTo(ScheduleSlot::class);
    }

// ---- Helper methods for view compatibility ----

    public function isPresent(): bool
    {
        return $this->event_type === 'join' && !$this->matchingLeave();
    }

    public function isModerator(): bool
    {
        return $this->role === 'MODERATOR';
    }

// session_date -> occurred_at
    public function getSessionDateAttribute(): ?\Carbon\Carbon
    {
        return $this->occurred_at;
    }

// first_joined_at -> occurred_at (برای رویداد join)
    public function getFirstJoinedAtAttribute(): ?\Carbon\Carbon
    {
        return $this->event_type === 'join' ? $this->occurred_at : null;
    }

// left_at -> زمان رویداد leave متناظر
    public function getLeftAtAttribute(): ?\Carbon\Carbon
    {
        if ($this->event_type === 'leave') {
            return $this->occurred_at;
        }
        return $this->matchingLeave()?->occurred_at;
    }

// last_seen_at ستون ندارد
    public function getLastSeenAtAttribute(): ?\Carbon\Carbon
    {
        return null;
    }
}
