<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Models\AttendanceEvent;
use App\Models\Bell;
use App\Models\ScheduleSlot;
use App\Services\AttendanceLogger;
use App\Services\BBBClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MyScheduleController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isTeacher()) {
            $slots = ScheduleSlot::with(['klass.academicLevel', 'bell', 'teacher'])
                ->where('teacher_id', $user->id)
                ->where('is_extra', false)
                ->get();

            $extras = ScheduleSlot::with(['klass.academicLevel', 'teacher'])
                ->where('teacher_id', $user->id)
                ->where('is_extra', true)
                ->where('ends_at', '>=', now()->subDays(7))
                ->orderBy('starts_at')
                ->get();

            $klasses = $slots->pluck('klass')->unique('id')->filter();

            $bells = Bell::whereIn('id', $slots->pluck('bell_id')->unique()->filter())
                ->orderBy('order')
                ->orderBy('start_time')
                ->get();

            if ($bells->isEmpty() && $user->school_id) {
                $bells = Bell::where('school_id', $user->school_id)
                    ->orderBy('order')
                    ->orderBy('start_time')
                    ->get();
            }

            $slotsByCell = $slots->keyBy(fn($s) => $s->day_of_week . '-' . $s->bell_id);
            $klassColors = $this->buildKlassColors($klasses);
            $activeDays = ScheduleSlot::$activeDays;
            $days = ScheduleSlot::$days;

            return view('my-schedule.teacher', compact(
                'slots', 'slotsByCell', 'bells', 'klasses',
                'klassColors', 'activeDays', 'days', 'extras'
            ));
        }

        if ($user->isStudent()) {
            return redirect()->route('student.home');
        }

        return redirect()->route('manager.dashboard');
    }

    protected function buildKlassColors($klasses): array
    {
        $palette = [
            'indigo', 'green', 'blue', 'purple', 'pink',
            'orange', 'teal', 'red', 'cyan', 'amber',
        ];

        $result = [];
        foreach ($klasses as $i => $klass) {
            $result[$klass->id] = $palette[$i % count($palette)];
        }

        return $result;
    }

    public function join(ScheduleSlot $slot)
    {
        $user = auth()->user();

        if ($user->isTeacher() && $slot->teacher_id !== $user->id) {
            abort(403, 'این کلاس مال شما نیست');
        }

        if (!$slot->bbb_meeting_id) {
            return back()->withErrors(['error' => 'جلسه BBB این کلاس ساخته نشده']);
        }

        // چک زمان کلاس
        if (!$this->isWithinClassTime($slot, $user)) {
            return back()->withErrors(['error' => $this->getTimeError($slot)]);
        }

        // چک حداکثر جلسات هم‌زمان (فقط برای اولین ورودی)
        $school = $slot->school;
        if ($school && $slot->isLive() && !$slot->attendanceLogs()->whereDate('session_date', today())->exists()) {
            if (!$school->canStartConcurrentSession()) {
                return back()->withErrors([
                    'error' => "به حداکثر تعداد کلاس هم‌زمان پلن ({$school->plan_max_concurrent}) رسیده‌اید.",
                ]);
            }
        }

        $role = $user->isStudent() ? 'VIEWER' : 'MODERATOR';
        $bbbUserId = 'user-' . $user->id;

        try {
            AttendanceLogger::logJoin($slot, $user, $user->name, $role, $bbbUserId);
        } catch (\Throwable $e) {
            Log::warning('Attendance log failed: ' . $e->getMessage());
        }

        try {
            $bbb = new BBBClient();
            $joinUrl = $bbb->getJoinUrl($slot->bbb_meeting_id, $user->name, $role, $bbbUserId);
            return redirect($joinUrl);
        } catch (\Throwable $e) {
            Log::error('BBB join error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'خطا در ورود به جلسه: ' . $e->getMessage()]);
        }
    }

    public function student()
    {
        $user = auth()->user();

        if (!$user->klass_id) {
            return view('my-schedule.no-class');
        }

        $klass = $user->klass;

        $slots = ScheduleSlot::with(['bell', 'teacher'])
            ->where('klass_id', $klass->id)
            ->where('is_extra', false)
            ->get();

        $extras = ScheduleSlot::with('teacher')
            ->where('is_extra', true)
            ->where('ends_at', '>=', now()->subDays(7))
            ->where(function ($q) use ($klass) {
                $q->where('klass_id', $klass->id)
                  ->orWhere(function ($q2) use ($klass) {
                      $q2->where('extra_scope', 'all')
                         ->where('school_id', $klass->school_id);
                  });
            })
            ->orderBy('starts_at')
            ->get();

        $bells = Bell::where('school_id', $klass->school_id)
            ->orderBy('order')
            ->orderBy('start_time')
            ->get();

        $slotsByCell = $slots->keyBy(fn($s) => $s->day_of_week . '-' . $s->bell_id);
        $activeDays = ScheduleSlot::$activeDays;
        $days = ScheduleSlot::$days;

        return view('my-schedule.student', compact(
            'klass', 'slotsByCell', 'bells', 'activeDays', 'days', 'extras'
        ));
    }

    public function studentHome()
    {
        $user = auth()->user();

        if (!$user->isStudent()) {
            return redirect()->route('home');
        }

        $carbonDay = Carbon::today()->dayOfWeek;
        $today = $carbonDay === 6 ? 0 : $carbonDay + 1;

        $klass = $user->klass;

        $todaySlots = collect();
        $extras = collect();
        $stats = ['this_month' => 0, 'this_week' => 0];

        if ($klass) {
            $todaySlots = ScheduleSlot::with(['teacher', 'bell', 'klass.academicLevel'])
                ->where('klass_id', $klass->id)
                ->where('is_extra', false)
                ->where('day_of_week', $today)
                ->get()
                ->sortBy(fn($s) => $s->bell->order ?? 0)
                ->values();

            $extras = ScheduleSlot::with('teacher')
                ->where('is_extra', true)
                ->where('ends_at', '>=', now())
                ->where(function ($q) use ($klass) {
                    $q->where('klass_id', $klass->id)
                      ->orWhere(function ($q2) use ($klass) {
                          $q2->where('extra_scope', 'all')
                             ->where('school_id', $klass->school_id);
                      });
                })
                ->orderBy('starts_at')
                ->take(5)
                ->get();
            $logs = AttendanceEvent::where('user_id', $user->id)
                ->where('event_type', 'join');

            $stats['this_month'] = (clone $logs)
                ->whereMonth('occurred_at', now()->month)
                ->whereYear('occurred_at', now()->year)
                ->count();

            $stats['this_week'] = (clone $logs)
                ->whereDate('occurred_at', '>=', now()->subDays(6)->toDateString())
                ->count();

        }

        return view('student.home', compact('user', 'klass', 'todaySlots', 'stats', 'today', 'extras'));
    }

    public function myAttendance()
    {
        $user = auth()->user();

        $from = Carbon::today()->subDays(29);
        $to = Carbon::today();

        $logs = AttendanceEvent::with(['scheduleSlot.klass.academicLevel', 'scheduleSlot.bell'])
            ->where('user_id', $user->id)
            ->where('event_type', 'join')
            ->whereDate('occurred_at', '>=', $from->toDateString())
            ->whereDate('occurred_at', '<=', $to->toDateString())
            ->orderBy('occurred_at', 'desc')
            ->get();

        $byDate = $logs->groupBy(fn($l) => $l->occurred_at->format('Y-m-d'));

        $stats = [
            'total_sessions' => $logs->count(),
            'total_minutes'  => $logs->sum(fn($l) => $l->calculateDuration()),
            'this_week'      => $logs->filter(fn($l) => $l->occurred_at->gte(Carbon::today()->subDays(6)))->count(),
        ];

        return view('my-schedule.my-attendance', compact('logs', 'byDate', 'stats', 'from', 'to'));
    }

    public function exportAttendance()
    {
        $user = auth()->user();
        $logs = AttendanceEvent::with(['scheduleSlot.klass.academicLevel', 'scheduleSlot.bell'])
            ->where('user_id', $user->id)
            ->where('event_type', 'join')
            ->orderBy('occurred_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();


        $filename = 'my-attendance-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new AttendanceExport($logs), $filename);
    }

    protected function isWithinClassTime(ScheduleSlot $slot, $user): bool
    {
        $now = now();

        // معلم صاحب کلاس → همیشه مجاز
        if ($user->isTeacher() && $slot->teacher_id === $user->id) {
            return true;
        }

        // مدیر و ادمین → همیشه مجاز
        if ($user->isManager() || $user->isAdmin()) {
            return true;
        }

        // از این به بعد فقط دانش‌آموز
        if ($slot->is_extra) {
            if (!$slot->starts_at || !$slot->ends_at) return false;

            $start = $slot->starts_at->copy()->subMinutes(5);
            $end = $slot->ends_at->copy();

            return $now->gte($start) && $now->lte($end);
        }

        if (!$slot->bell) return false;

        $carbonDay = $now->dayOfWeek;
        $currentDay = $carbonDay === 6 ? 0 : $carbonDay + 1;

        if ($currentDay !== (int) $slot->day_of_week) {
            return false;
        }

        $start = Carbon::parse($now->toDateString() . ' ' . $slot->bell->start_time)->subMinutes(5);
        $end = Carbon::parse($now->toDateString() . ' ' . $slot->bell->end_time);

        return $now->gte($start) && $now->lte($end);
    }

    protected function getTimeError(ScheduleSlot $slot): string
    {
        if ($slot->is_extra) {
            if (!$slot->starts_at) return 'زمان این فوق‌برنامه مشخص نیست';
            $startJ = \App\Helpers\JalaliHelper::toJalali($slot->starts_at, 'Y/m/d H:i');
            $endTime = $slot->ends_at?->format('H:i');
            return "این فوق‌برنامه در تاریخ {$startJ} تا {$endTime} برگزار می‌شود.";
        }

        if (!$slot->bell) {
            return 'این کلاس زمان‌بندی نشده است';
        }

        $carbonDay = now()->dayOfWeek;
        $currentDay = $carbonDay === 6 ? 0 : $carbonDay + 1;

        if ($currentDay !== (int) $slot->day_of_week) {
            $dayName = ScheduleSlot::$days[$slot->day_of_week] ?? '—';
            return "این کلاس در روز {$dayName} برگزار می‌شود";
        }

        $start = $slot->bell->start_time_short;
        $end = $slot->bell->end_time_short;

        return "این کلاس از ساعت {$start} تا {$end} برگزار می‌شود. در حال حاضر خارج از زمان کلاس هستید.";
    }
}
