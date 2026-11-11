<?php

namespace App\Http\Controllers;

use App\Helpers\JalaliHelper;
use App\Models\Course;
use App\Services\BBBClient;
use App\Services\StudentExcelParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExtraCourseController extends Controller
{
    public function create()
    {
        return view('courses.extra.create');
    }

    public function store(Request $request)
    {
        if ($request->hasFile('excel_file')) {
            $students = StudentExcelParser::parse($request->file('excel_file'));
        } else {
            $students = collect($request->input('students', []))
                ->filter(fn ($s) => !empty($s['name']) || !empty($s['email']))
                ->values()
                ->all();
        }

        $request->merge(['students' => $students]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'students' => 'nullable|array',
            'students.*.name' => 'required|string|max:255',
            'students.*.email' => 'required|email',
            'open_enrollment' => 'nullable|boolean',
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

        $students = $validated['students'] ?? [];
        $studentCount = count($students);

        try {
            $bbb = new BBBClient();
            $meetingId = 'extra-' . auth()->id() . '-' . time();
            $bbb->createMeeting($meetingId, $validated['title']);
            $joinUrl = $bbb->getJoinUrl($meetingId, 'مدیر کلاس', 'MODERATOR');

            Course::create([
                'user_id' => auth()->id(),
                'bbb_meeting_id' => $meetingId,
                'title' => $validated['title'],
                'type' => 'extra',
                'starts_at' => $startsAtGregorian,
                'ends_at' => $endsAtGregorian,
                'meta' => [
                    'students' => $students,
                    'student_count' => $studentCount,
                    'description' => $validated['description'] ?? null,
                    'open_enrollment' => (bool) ($validated['open_enrollment'] ?? false),
                ],
            ]);

            return view('courses.extra.success', [
                'courseName' => $validated['title'],
                'studentCount' => $studentCount,
                'joinUrl' => $joinUrl,
            ]);

        } catch (\Throwable $e) {
            Log::error('Extra course creation failed: ' . $e->getMessage());

            return back()->withErrors(['error' => 'خطا در ساخت کلاس: ' . $e->getMessage()])->withInput();
        }
    }
}
