<?php

namespace App\Http\Controllers;

use App\Services\MoodleClient;
use App\Services\StudentExcelParser;
use App\Services\BBBClient;
use Illuminate\Http\Request;

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

        // تبدیل تاریخ شمسی به میلادی
        $startsAtGregorian = \App\Helpers\JalaliHelper::toGregorian($validated['starts_at'] ?? null);
        $endsAtGregorian = \App\Helpers\JalaliHelper::toGregorian($validated['ends_at'] ?? null);

        // اعتبارسنجی دستی تاریخ‌ها
        if (!empty($validated['starts_at']) && $startsAtGregorian === null) {
            return back()->withErrors(['starts_at' => 'فرمت تاریخ شروع نامعتبر است. مثال: ۱۴۰۵/۰۷/۰۴ ۱۰:۳۰'])->withInput();
        }
        if (!empty($validated['ends_at']) && $endsAtGregorian === null) {
            return back()->withErrors(['ends_at' => 'فرمت تاریخ پایان نامعتبر است. مثال: ۱۴۰۵/۰۷/۱۵ ۱۲:۰۰'])->withInput();
        }
        if ($startsAtGregorian && $endsAtGregorian && $endsAtGregorian->lt($startsAtGregorian)) {
            return back()->withErrors(['ends_at' => 'تاریخ پایان باید بعد از تاریخ شروع باشد'])->withInput();
        }

        $moodle = new MoodleClient();

        try {
            $courseResult = $moodle->createCourse(
                $validated['title'],
                'extra-' . time()
            );
            $courseId = $courseResult[0]['id'];

            $studentCount = 0;
            if (!empty($validated['students'])) {
                foreach ($validated['students'] as $student) {
                    $nameParts = explode(' ', trim($student['name']), 2);
                    $firstName = $nameParts[0];
                    $lastName = $nameParts[1] ?? '.';

                    $user = $moodle->createOrGetUser($student['email'], $firstName, $lastName);
                    $moodle->enrolUser($user['id'], $courseId);
                    $studentCount++;
                }
            }

            $bbb = new BBBClient();
            $meetingId = 'extra-' . $courseId . '-' . time();
            $bbb->createMeeting($meetingId, $validated['title']);
            $joinUrl = $bbb->getJoinUrl($meetingId, 'مدیر کلاس', 'MODERATOR');

            \App\Models\Course::create([
                'user_id' => auth()->id(),
                'moodle_course_id' => $courseId,
                'bbb_meeting_id' => $meetingId,
                'title' => $validated['title'],
                'type' => 'extra',
                'starts_at' => $startsAtGregorian,
                'ends_at' => $endsAtGregorian,
                'meta' => [
                    'student_count' => $studentCount,
                    'description' => $validated['description'] ?? null,
                ],
            ]);

            return view('courses.extra.success', [
                'courseName' => $validated['title'],
                'studentCount' => $studentCount,
                'joinUrl' => $joinUrl,
            ]);

        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'خطا در ساخت کلاس: ' . $e->getMessage()])->withInput();
        }
    }
}
