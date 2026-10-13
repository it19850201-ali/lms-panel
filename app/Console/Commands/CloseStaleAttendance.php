<?php

namespace App\Console\Commands;

use App\Models\AttendanceEvent;
use Illuminate\Console\Command;

class CloseStaleAttendance extends Command
{
    protected $signature = 'attendance:close-stale {--buffer=15 : دقیقه بافر بعد از پایان جلسه}';
    protected $description = 'ثبت خروج برای ورودهای بازی که زمان جلسه‌شان گذشته';

    public function handle(): int
    {
        $buffer = (int) $this->option('buffer');
        $closed = 0;

        AttendanceEvent::with('scheduleSlot.bell')
            ->openJoins()
            ->get()
            ->each(function (AttendanceEvent $join) use ($buffer, &$closed) {
                $end = $join->sessionEnd();

                if (!$end) {
                    return;
                }

                // ورود بعد از پایان زنگ: همان لحظه بسته شود
                if ($end->lte($join->occurred_at)) {
                    $end = $join->occurred_at->copy();
                }

                if (now()->lt($end->copy()->addMinutes($buffer))) {
                    return;
                }

                $join->recordLeave($end, 'auto-close');
                $closed++;
            });

        $this->info("✅ {$closed} خروج ثبت شد");
        return self::SUCCESS;
    }
}
