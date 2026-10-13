<?php

namespace App\Services;

use App\Models\AttendanceEvent;
use App\Models\ScheduleSlot;
use App\Models\User;
use Carbon\Carbon;

class AttendanceLogger
{
    public static function logJoin(
        ScheduleSlot $slot,
        ?User $user,
        string $fullName,
        string $role,
        ?string $bbbUserId = null
    ): AttendanceEvent {
        $today = Carbon::today();

        if (!$bbbUserId) {
            if ($user) {
                $bbbUserId = 'user-' . $user->id;
            } else {
                $bbbUserId = 'guest-' . md5($fullName . '|' . $slot->id . '|' . $today->toDateString());
            }
        }

        return AttendanceEvent::create([
            'meeting_id'      => $slot->bbb_meeting_id,
            'bbb_attendee_id' => $bbbUserId,
            'full_name'       => $fullName,
            'role'            => $role,
            'event_type'      => 'join',
            'occurred_at'     => now(),
            'source'          => 'bbb',
        ]);
    }

    public static function logLeave(
        ScheduleSlot $slot,
        ?User $user,
        string $fullName,
        string $role,
        ?string $bbbUserId = null
    ): AttendanceEvent {
        $today = Carbon::today();

        if (!$bbbUserId) {
            if ($user) {
                $bbbUserId = 'user-' . $user->id;
            } else {
                $bbbUserId = 'guest-' . md5($fullName . '|' . $slot->id . '|' . $today->toDateString());
            }
        }

        return AttendanceEvent::create([
            'meeting_id'      => $slot->bbb_meeting_id,
            'bbb_attendee_id' => $bbbUserId,
            'full_name'       => $fullName,
            'role'            => $role,
            'event_type'      => 'leave',
            'occurred_at'     => now(),
            'source'          => 'bbb',
        ]);
    }

    public static function getDurationMinutes(string $meetingId, string $bbbAttendeeId): float
    {
        $events = AttendanceEvent::where('meeting_id', $meetingId)
            ->where('bbb_attendee_id', $bbbAttendeeId)
            ->orderBy('occurred_at')
            ->get();

        $total = 0;
        $joinTime = null;

        foreach ($events as $event) {
            if ($event->event_type === 'join') {
                $joinTime = Carbon::parse($event->occurred_at);
            } elseif ($event->event_type === 'leave' && $joinTime !== null) {
                $total += $joinTime->diffInMinutes(Carbon::parse($event->occurred_at));
                $joinTime = null;
            }
        }

        return $total;
    }
}
