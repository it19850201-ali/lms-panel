<?php

namespace App\Http\Controllers;

use App\Helpers\JalaliHelper;
use App\Models\Course;
use App\Services\BBBClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('courses.index', compact('courses'));
    }

    public function join(Course $course)
    {
        abort_if($course->user_id !== auth()->id(), 403);

        $now = now();

        if ($course->starts_at && $now->lt($course->starts_at)) {
            $startsJalali = JalaliHelper::toJalali($course->starts_at);
            return back()->withErrors([
                'error' => "این کلاس از تاریخ {$startsJalali} شروع می‌شود.",
            ]);
        }

        if ($course->ends_at && $now->gt($course->ends_at)) {
            $endsJalali = JalaliHelper::toJalali($course->ends_at);
            return back()->withErrors([
                'error' => "این کلاس در تاریخ {$endsJalali} به پایان رسیده است.",
            ]);
        }

        $bbb = new BBBClient();
        $joinUrl = $bbb->getJoinUrl(
            $course->bbb_meeting_id,
            auth()->user()->name,
            'MODERATOR'
        );

        return redirect($joinUrl);
    }

    public function edit(Course $course)
    {
        abort_if($course->user_id !== auth()->id(), 403);

        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        abort_if($course->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'starts_at' => 'nullable|string',
            'ends_at' => 'nullable|string',
        ]);

        $startsAtGregorian = JalaliHelper::toGregorian($validated['starts_at'] ?? null);
        $endsAtGregorian = JalaliHelper::toGregorian($validated['ends_at'] ?? null);

        if (!empty($validated['starts_at']) && $startsAtGregorian === null) {
            return back()->withErrors(['starts_at' => 'فرمت تاریخ شروع نامعتبر است. مثال: ۱۴۰۵/۰۷/۰۴ ۱۰:۳۰'])->withInput();
        }
        if (!empty($validated['ends_at']) && $endsAtGregorian === null) {
            return back()->withErrors(['ends_at' => 'فرمت تاریخ پایان نامعتبر است. مثال: ۱۴۰۵/۰۷/۱۵ ۱۲:۰۰'])->withInput();
        }
        if ($startsAtGregorian && $endsAtGregorian && $endsAtGregorian->lt($startsAtGregorian)) {
            return back()->withErrors(['ends_at' => 'تاریخ پایان باید بعد از تاریخ شروع باشد'])->withInput();
        }

        $course->update([
            'title' => $validated['title'],
            'starts_at' => $startsAtGregorian,
            'ends_at' => $endsAtGregorian,
        ]);

        return redirect()->route('courses.index')->with('success', 'کلاس با موفقیت ویرایش شد');
    }

    public function destroy(Course $course)
    {
        abort_if($course->user_id !== auth()->id(), 403);

        $bbbMeetingId = $course->bbb_meeting_id;

        $course->delete();

        if ($bbbMeetingId) {
            try {
                $bbb = new BBBClient();
                $bbb->endMeeting($bbbMeetingId);
            } catch (\Throwable $e) {
                Log::warning('Failed to end BBB meeting ' . $bbbMeetingId . ': ' . $e->getMessage());
            }
        }

        return redirect()->route('courses.index')->with('success', 'کلاس با موفقیت حذف شد');
    }
}
