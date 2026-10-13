<?php

namespace App\Http\Controllers;

use App\Services\MoodleClient;
use App\Services\StudentExcelParser;
use App\Services\BBBClient;
use Illuminate\Http\Request;

class CourseWizardController extends Controller
{
    // مرحله ۱: اطلاعات پایه‌ی کلاس
    public function step1()
    {
        return view('courses.regular.step1', [
            'data' => session('wizard.regular', []),
        ]);
    }

    public function step1Store(Request $request)
    {
        $validated = $request->validate([
            'course_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
        ]);

        session(['wizard.regular' => array_merge(
            session('wizard.regular', []),
            $validated
        )]);

        return redirect()->route('courses.create.regular.step2');
    }

    // مرحله ۲: افزودن دانش‌آموز/دانشجو
    public function step2()
    {
        if (!session('wizard.regular.course_name')) {
            return redirect()->route('courses.create.regular');
        }

        return view('courses.regular.step2', [
            'data' => session('wizard.regular', []),
        ]);
    }

    public function step2Store(Request $request)
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
            'students' => 'required|array|min:1',
            'students.*.name' => 'required|string|max:255',
            'students.*.email' => 'required|email',
        ]);

        session(['wizard.regular' => array_merge(
            session('wizard.regular', []),
            ['students' => $validated['students']]
        )]);

        return redirect()->route('courses.create.regular.step3');
    }

    // مرحله ۳: زمان‌بندی
    public function step3()
    {
        if (!session('wizard.regular.students')) {
            return redirect()->route('courses.create.regular.step2');
        }

        return view('courses.regular.step3', [
            'data' => session('wizard.regular', []),
        ]);
    }

    public function step3Store(Request $request)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|string',
            'time' => 'required|string',
        ]);

        session(['wizard.regular' => array_merge(
            session('wizard.regular', []),
            $validated
        )]);

        return $this->finalize();
    }

    // نهایی‌سازی: ساخت واقعی تو Moodle + BBB
    protected function finalize()
    {
        $wizardData = session('wizard.regular');
        $moodle = new MoodleClient();

        try {
            $courseResult = $moodle->createCourse(
                $wizardData['course_name'],
                'course-' . time()
            );
            $courseId = $courseResult[0]['id'];

	    foreach ($wizardData['students'] as $student) {
    $nameParts = explode(' ', trim($student['name']), 2);
    $firstName = $nameParts[0];
    $lastName = $nameParts[1] ?? '.';

    $user = $moodle->createOrGetUser($student['email'], $firstName, $lastName);
    $moodle->enrolUser($user['id'], $courseId);
}
            $bbb = new BBBClient();
            $meetingId = 'course-' . $courseId . '-' . time();;
            $bbb->createMeeting($meetingId, $wizardData['course_name']);
            $joinUrl = $bbb->getJoinUrl($meetingId, 'مدیر کلاس', 'MODERATOR');
	    \App\Models\Course::create([
                'user_id' => auth()->id(),
                'moodle_course_id' => $courseId,
                'bbb_meeting_id' => $meetingId,
                'title' => $wizardData['course_name'],
                'type' => 'regular',
                'meta' => [
                    'grade_level' => $wizardData['grade_level'] ?? null,
                    'day_of_week' => $wizardData['day_of_week'] ?? null,
                    'time' => $wizardData['time'] ?? null,
                    'student_count' => count($wizardData['students']),
                ],
            ]);
            session()->forget('wizard.regular');

            return view('courses.regular.success', [
                'courseId' => $courseId,
                'courseName' => $wizardData['course_name'],
                'studentCount' => count($wizardData['students']),
                'joinUrl' => $joinUrl,
            ]);

        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'خطا در ساخت کلاس: ' . $e->getMessage()]);
        }
    }
}
