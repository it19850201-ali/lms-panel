<?php

namespace App\Http\Controllers;

use App\Models\ScheduleSlot;
use App\Services\AttendanceLogger;
use App\Services\BBBClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JoinController extends Controller
{
    /**
     * صفحه‌ی ورود مهمان (دانش‌آموز با لینک مستقیم)
     */
    public function show(Request $request, string $meetingId)
    {
        $slot = ScheduleSlot::with(['klass.academicLevel', 'bell', 'teacher'])
            ->where('bbb_meeting_id', $meetingId)
            ->firstOrFail();

        return view('join.show', compact('slot', 'meetingId'));
    }

    /**
     * پردازش ورود
     */
    public function join(Request $request, string $meetingId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ], [
            'name.required' => 'نام خود را وارد کنید',
        ]);

        $slot = ScheduleSlot::where('bbb_meeting_id', $meetingId)->firstOrFail();

        if ($slot->is_extra) {
            $user = $request->user();

            // معلم صاحب جلسه از مسیر خودش و با نقش معلم وارد می‌شود.
            if ($user && $user->isTeacher() && (int) $slot->teacher_id === (int) $user->id) {
                return redirect()->route('my-schedule.join', $slot);
            }

            if (!$slot->starts_at || !$slot->ends_at || $slot->ends_at->lte($slot->starts_at)) {
                return back()->withErrors(['error' => 'زمان برگزاری این جلسه مشخص نیست. با برگزارکننده تماس بگیرید.'])
                    ->withInput($validated);
            }

            $now = now();
            $opensAt = $slot->starts_at->copy()->subMinutes(10);

            if ($now->lt($opensAt)) {
                $opensAtLabel = \App\Helpers\JalaliHelper::toJalali($opensAt, 'Y/m/d H:i');

                return back()->withErrors([
                    'error' => "ورود مهمان از ۱۰ دقیقه قبل از شروع جلسه، در {$opensAtLabel} امکان‌پذیر است.",
                ])->withInput($validated);
            }

            if ($now->gt($slot->ends_at)) {
                return back()->withErrors(['error' => 'زمان این جلسه به پایان رسیده است.'])
                    ->withInput($validated);
            }
        }

        // userID یکتا برای مهمان
        $bbbUserId = 'guest-' . md5($validated['name'] . '|' . $slot->id . '|' . now()->toDateString() . '|' . $request->ip());

        try {
            AttendanceLogger::logJoin($slot, null, $validated['name'], 'VIEWER', $bbbUserId);
        } catch (\Throwable $e) {
            Log::warning('Guest attendance log failed: ' . $e->getMessage());
        }

        try {
            $bbb = new BBBClient();
            $joinUrl = $bbb->getJoinUrl($meetingId, $validated['name'], 'VIEWER', $bbbUserId);
            return redirect($joinUrl);
        } catch (\Throwable $e) {
            Log::error('BBB guest join error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'خطا در ورود به جلسه: ' . $e->getMessage()]);
        }
    }
}
