<?php

namespace App\Console\Commands;

use App\Models\AttendanceEvent;
use App\Services\BBBClient;
use Illuminate\Console\Command;

class SyncBBBAttendance extends Command
{
    protected $signature = 'attendance:sync-bbb';
    protected $description = 'ثبت خروج کاربرانی که از جلسه‌ی BBB رفته‌اند';

    public function handle(): int
    {
        $bbb = new BBBClient();
        $marked = 0;

        $openJoins = AttendanceEvent::with('scheduleSlot')
            ->openJoins()
            ->whereDate('occurred_at', today())
            ->get();

        if ($openJoins->isEmpty()) {
            $this->line('هیچ ورود بازی نیست');
            return self::SUCCESS;
        }

        foreach ($openJoins->groupBy('schedule_slot_id') as $logs) {
            $slot = $logs->first()->scheduleSlot;

            if (!$slot || !$slot->bbb_meeting_id) {
                continue;
            }

            $end = $logs->first()->sessionEnd();
            if ($end && now()->gt($end)) {
                continue; // جلسه تمام شده؛ CloseStale هندلش می‌کند
            }

            $info = $bbb->getMeetingInfo($slot->bbb_meeting_id);

            if (!$info) {
                $this->warn("BBB در دسترس نیست: slot {$slot->id}");
                continue; // اگر BBB جواب نداد، کسی را خارج فرض نکن
            }

            $running   = ($info['running'] ?? 'false') === 'true';
            $attendees = $running ? $this->extractAttendees($info) : [];

            foreach ($logs as $join) {
                // ۹۰ ثانیه مهلت بعد از ورود، چون BBB ممکن است دیرتر نشان دهد
                if ($join->occurred_at->gt(now()->subSeconds(90))) {
                    continue;
                }

                if ($running && $this->matchAttendee((string) $join->bbb_attendee_id, $attendees)) {
                    continue;
                }

                $join->recordLeave(now(), 'bbb-sync');
                $marked++;
            }
        }

        $this->info("✅ خروج ثبت شد: {$marked}");
        return self::SUCCESS;
    }

    protected function extractAttendees(array $info): array
    {
        $raw = $info['attendees']['attendee'] ?? [];

        if (empty($raw) || !is_array($raw)) {
            return [];
        }

        // اگر فقط یک نفر باشد، ساختار تک‌سطحی است
        if (isset($raw['userID'])) {
            return [$raw];
        }

        return $raw;
    }

    protected function matchAttendee(string $ourId, array $attendees): bool
    {
        if ($ourId === '') {
            return false;
        }

        foreach ($attendees as $attendee) {
            $bid = (string) ($attendee['userID'] ?? '');

            if ($bid === $ourId) {
                return true;
            }

            // BBB گاهی پیشوند مثل w_ یا v_ اضافه می‌کند
            if (preg_match('/^[a-z]_' . preg_quote($ourId, '/') . '$/', $bid)) {
                return true;
            }
        }

        return false;
    }
}
