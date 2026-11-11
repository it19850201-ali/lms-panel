<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\BBBClient;
use App\Services\StudentExcelParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourseWizardController extends Controller
{
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

    protected function finalize()
    {
        $wizardData = session('wizard.regular');

        if (empty($wizardData['course_name']) || empty($wizardData['students'])) {
            return redirect()->route('courses.create.regular')
                ->withErrors(['error' => 'اطلاعات ناقص است، لطفاً از ابتدا شروع کنید.']);
        }

        $studentCount = count($wizardData['students']);

        try {
            $bbb = new BBBClient();
            $meetingId = 'course-' . auth()->id() . '-' . time();
            $bbb->createMeeting($meetingId, $wizardData['course_name']);
            $joinUrl = $bbb->getJoinUrl($meetingId, 'مدیر کلاس', 'MODERATOR');

            $course = Course::create([
                'user_id' => auth()->id(),
                'bbb_meeting_id' => $meetingId,
                'title' => $wizardData['course_name'],
                'type' => 'regular',
                'meta' => [
                    'grade_level' => $wizardData['grade_level'] ?? null,
                    'day_of_week' => $wizardData['day_of_week'] ?? null,
                    'time' => $wizardData['time'] ?? null,
                    'students' => $wizardData['students'],
                    'student_count' => $studentCount,
                ],
            ]);

            session()->forget('wizard.regular');

            return view('courses.regular.success', [
                'courseId' => $course->id,
                'courseName' => $wizardData['course_name'],
                'studentCount' => $studentCount,
                'joinUrl' => $joinUrl,
            ]);

        } catch (\Throwable $e) {
            Log::error('Course wizard finalize failed: ' . $e->getMessage());

            return back()->withErrors(['error' => 'خطا در ساخت کلاس: ' . $e->getMessage()]);
        }
    }
}
