<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->isAdmin() ? null : $user->school_id;

        $query = AttendanceEvent::with(['scheduleSlot.klass.academicLevel', 'scheduleSlot.bell', 'user'])->where('event_type', 'join')
            ->where('event_type', 'join')
            ->orderBy('id', 'desc');

        // فیلتر بر اساس مدرسه
        if ($schoolId) {
            $query->whereHas('scheduleSlot', fn($q) => $q->where('school_id', $schoolId));
        }

        // فیلتر بر اساس slot
        if ($slotId = $request->input('slot_id')) {
            $query->where('schedule_slot_id', $slotId);
        }

        // فیلتر بر اساس تاریخ
        if ($date = $request->input('date')) {
            $query->whereDate('occurred_at', $date);
        }

        // فیلتر بر اساس وضعیت
        if ($status = $request->input('status')) {
            if ($status === 'open') {
                // join بدون leave متناظر = هنوز در جلسه
                $query->whereNotExists(function ($sub) {
                    $sub->from('attendance_events as ae2')
                        ->whereColumn('ae2.user_id', 'attendance_events.user_id')
                        ->whereColumn('ae2.meeting_id', 'attendance_events.meeting_id')
                        ->where('ae2.event_type', 'leave');
                });
            } elseif ($status === 'closed') {
                // join با leave متناظر = از جلسه خارج شده
                $query->whereExists(function ($sub) {
                    $sub->from('attendance_events as ae2')
                        ->whereColumn('ae2.user_id', 'attendance_events.user_id')
                        ->whereColumn('ae2.meeting_id', 'attendance_events.meeting_id')
                        ->where('ae2.event_type', 'leave');
                });
            }
        }

        $logs = $query->paginate(50);

        return view('manager.attendance.index', compact('logs'));
    }

    public function closeStale()
    {
        Artisan::call('attendance:close-stale');
        $output = Artisan::output();

        return back()->with('success', trim($output));
    }

    public function show(AttendanceEvent $log)
    {
        $this->authorizeLog($log);

        $log->load(['scheduleSlot.klass.academicLevel', 'scheduleSlot.teacher', 'scheduleSlot.bell', 'user']);

        return view('manager.attendance.show', compact('log'));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->isAdmin() ? null : $user->school_id;

        // بازه‌ی تاریخ
        $range = $request->input('range', 'all');
        $from  = $request->input('from');
        $to    = $request->input('to');

        [$startDate, $endDate] = match ($range) {
            'today'  => [Carbon::today(), Carbon::today()],
            'week'   => [Carbon::today()->subDays(6), Carbon::today()],
            'month'  => [Carbon::today()->subDays(29), Carbon::today()],
            'custom' => [
                $from ? Carbon::parse($from) : Carbon::today()->subDays(29),
                $to   ? Carbon::parse($to)   : Carbon::today(),
            ],
            default  => [null, null],
        };

        $query = AttendanceEvent::with(['scheduleSlot.klass.academicLevel', 'scheduleSlot.bell', 'user'])->where('event_type', 'join')
            ->where('event_type', 'join')
            ->when($schoolId, fn($q) => $q->whereHas('scheduleSlot', fn($sq) => $sq->where('school_id', $schoolId)));

        if ($startDate) {
            $query->whereDate('occurred_at', '>=', $startDate->toDateString());
        }
        if ($endDate) {
            $query->whereDate('occurred_at', '<=', $endDate->toDateString());
        }

        // فیلترهای اضافی
        if ($slotId = $request->input('slot_id')) {
            $query->where('schedule_slot_id', $slotId);
        }

        if ($status = $request->input('status')) {
            if ($status === 'open') {
                $query->whereNotExists(function ($sub) {
                    $sub->from('attendance_events as ae2')
                        ->whereColumn('ae2.user_id', 'attendance_events.user_id')
                        ->whereColumn('ae2.meeting_id', 'attendance_events.meeting_id')
                        ->where('ae2.event_type', 'leave');
                });
            } elseif ($status === 'closed') {
                $query->whereExists(function ($sub) {
                    $sub->from('attendance_events as ae2')
                        ->whereColumn('ae2.user_id', 'attendance_events.user_id')
                        ->whereColumn('ae2.meeting_id', 'attendance_events.meeting_id')
                        ->where('ae2.event_type', 'leave');
                });
            }
        }

        $logs = $query->orderBy('occurred_at', 'desc')
            ->orderBy('schedule_slot_id')
            ->get();

        $filename = 'attendance-' . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(new AttendanceExport($logs), $filename);
    }

    protected function authorizeLog(AttendanceEvent $log): void
    {
        $user = auth()->user();

        if (! $user->isAdmin() && $log->scheduleSlot->school_id !== $user->school_id) {
            abort(403);
        }
    }
}
