<?php

namespace App\Http\Controllers;

use App\Models\Bell;
use App\Models\Klass;
use App\Models\School;
use App\Models\ScheduleSlot;
use App\Models\User;
use App\Services\BBBClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    protected function getSchoolId(): ?int
    {
        $user = auth()->user();
        return $user->isAdmin() ? null : $user->school_id;
    }

    protected function ensureSchool(): int
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) {
            abort(403, 'ادمین باید از پنل مدرسه‌ی مورد نظر این کار را انجام دهد.');
        }
        return $schoolId;
    }

    public function index()
    {
        $schoolId = $this->getSchoolId();

        $klasses = Klass::with('academicLevel')
            ->withCount('students')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('academic_level_id')
            ->orderBy('name')
            ->get();

        return view('manager.schedule.index', compact('klasses'));
    }

    public function show(Klass $klass)
    {
        $this->authorizeKlass($klass);

        $bells = Bell::where('school_id', $klass->school_id)
            ->orderBy('order')
            ->orderBy('start_time')
            ->get();

        $slots = ScheduleSlot::where('klass_id', $klass->id)
            ->get()
            ->keyBy(fn($s) => $s->day_of_week . '-' . $s->bell_id);

        $teachers = User::where('role', 'teacher')
            ->where('school_id', $klass->school_id)
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();

        $activeDays = ScheduleSlot::$activeDays;
        $days = ScheduleSlot::$days;

        return view('manager.schedule.show', compact(
            'klass', 'bells', 'slots', 'teachers', 'activeDays', 'days'
        ));
    }

    public function join(ScheduleSlot $slot)
    {
        $user = auth()->user();

        if (!$user->isAdmin() && $user->school_id !== $slot->school_id) {
            abort(403);
        }

        $role = $user->isStudent() ? 'VIEWER' : 'MODERATOR';

        if (!$slot->bbb_meeting_id) {
            return back()->withErrors(['error' => 'این جلسه هنوز ساخته نشده است']);
        }

        $bbb = new BBBClient();
        $joinUrl = $bbb->getJoinUrl($slot->bbb_meeting_id, $user->name, $role);

        return redirect($joinUrl);
    }

    public function store(Request $request)
    {
        $schoolId = $this->ensureSchool();

        $validated = $request->validate([
            'klass_id'    => 'required|exists:klasses,id',
            'teacher_id'  => 'required|exists:users,id',
            'bell_id'     => 'required|exists:bells,id',
            'subject'     => 'required|string|max:255',
            'day_of_week' => 'required|integer|min:0|max:6',
            'room'        => 'nullable|string|max:100',
        ]);

        $klass = Klass::where('id', $validated['klass_id'])
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $teacher = User::where('id', $validated['teacher_id'])
            ->where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->firstOrFail();

        $bell = Bell::where('id', $validated['bell_id'])
            ->where('school_id', $schoolId)
            ->firstOrFail();

        // ============ چک‌های پلن ============
        $school = School::find($schoolId);
        // ⭐ گرفتن ترم فعال
        $school = School::find($schoolId);
        $activeTerm = $school->activeTerm();

        if (!$activeTerm) {
            return back()->withErrors([
                'error' => 'هیچ ترم فعالی وجود ندارد. لطفاً ابتدا یک ترم فعال بسازید.',
            ])->withInput();
        }

        // ۱. حداکثر کاربر هر کلاس
        $studentCount = User::where('klass_id', $klass->id)
            ->where('role', 'student')
            ->count();

        if ($studentCount > $school->plan_max_users_per_class) {
            return back()->withErrors([
                'error' => "تعداد دانش‌آموزان این کلاس ({$studentCount}) از حد مجاز پلن ({$school->plan_max_users_per_class}) بیشتر است. لطفاً با مدیر سیستم تماس بگیرید.",
            ])->withInput();
        }

        // ۲. حداکثر جلسات هم‌زمان
        $conflictCount = ScheduleSlot::where('school_id', $schoolId)
            ->where('is_extra', false)
            ->where('day_of_week', $validated['day_of_week'])
            ->where('bell_id', $bell->id)
            ->count();

        if ($conflictCount >= $school->plan_max_concurrent) {
            return back()->withErrors([
                'error' => "به حداکثر تعداد کلاس هم‌زمان پلن ({$school->plan_max_concurrent}) رسیده‌اید. لطفاً بعداً تلاش کنید یا با مدیر سیستم تماس بگیرید.",
            ])->withInput();
        }
        // ⭐ گرفتن ترم فعال
        $activeTerm = $school->activeTerm();
        if (!$activeTerm) {
            return back()->withErrors([
                'error' => 'هیچ ترم فعالی وجود ندارد. لطفاً ابتدا از بخش «ترم‌های تحصیلی» یک ترم فعال بسازید.',
            ])->withInput();
        }

        // ============ چک تداخل کلاس ============
        $klassConflict = ScheduleSlot::where('klass_id', $klass->id)
            ->where('day_of_week', $validated['day_of_week'])
            ->where('bell_id', $bell->id)
            ->exists();

        if ($klassConflict) {
            return back()->withErrors(['error' => 'این کلاس در این زنگ و روز قبلاً درس دارد.'])->withInput();
        }

        // ============ چک تداخل معلم ============
        $teacherConflict = ScheduleSlot::where('teacher_id', $teacher->id)
            ->where('day_of_week', $validated['day_of_week'])
            ->where('bell_id', $bell->id)
            ->exists();

        if ($teacherConflict) {
            return back()->withErrors(['error' => "معلم {$teacher->name} در این زنگ و روز در کلاس دیگری درس دارد."])->withInput();
        }

        // ============ ساخت BBB meeting ============
        $bbb = new BBBClient();
        $meetingId = $bbb->generateMeetingId('slot', $klass->id, $validated['day_of_week'], $bell->id);
        $meetingName = "{$klass->academicLevel->name} - {$klass->name} - {$validated['subject']}";

        // پاس دادن محدودیت‌های پلن
        $bbbOptions = [
            'maxParticipants' => $school->plan_max_users_per_class,
            'maxCameras'      => $school->plan_max_cameras,
        ];

        try {
            $result = $bbb->createMeeting($meetingId, $meetingName, $bbbOptions);

            if (($result['returncode'] ?? '') !== 'SUCCESS') {
                $msg = $result['message'] ?? 'خطای نامشخص';
                Log::error('BBB meeting creation failed', ['result' => $result]);
                return back()->withErrors(['error' => "خطا در ساخت جلسه BBB: {$msg}"])->withInput();
            }
        } catch (\Throwable $e) {
            Log::error('BBB create exception: ' . $e->getMessage());
            return back()->withErrors(['error' => 'خطا در ارتباط با BBB: ' . $e->getMessage()])->withInput();
        }

        ScheduleSlot::create([
            'school_id'      => $schoolId,
	    'term_id'        => $activeTerm->id,
            'klass_id'       => $klass->id,
            'teacher_id'     => $teacher->id,
            'bell_id'        => $bell->id,
            'subject'        => $validated['subject'],
            'day_of_week'    => $validated['day_of_week'],
            'room'           => $validated['room'] ?? null,
            'bbb_meeting_id' => $meetingId,
            'bbb_created_at' => now(),
        ]);

        return redirect()->route('manager.schedule.show', $klass)
            ->with('success', 'درس و جلسه BBB با موفقیت ساخته شد');
    }

    public function update(Request $request, ScheduleSlot $slot)
    {
        $this->authorizeSlot($slot);

        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject'    => 'required|string|max:255',
            'room'       => 'nullable|string|max:100',
        ]);

        $teacher = User::where('id', $validated['teacher_id'])
            ->where('school_id', $slot->school_id)
            ->where('role', 'teacher')
            ->firstOrFail();

        if ($teacher->id !== $slot->teacher_id) {
            $teacherConflict = ScheduleSlot::where('teacher_id', $teacher->id)
                ->where('day_of_week', $slot->day_of_week)
                ->where('bell_id', $slot->bell_id)
                ->where('id', '!=', $slot->id)
                ->exists();

            if ($teacherConflict) {
                return back()->withErrors(['error' => "معلم {$teacher->name} در این زنگ و روز در کلاس دیگری درس دارد."])->withInput();
            }
        }

        $slot->update([
            'teacher_id' => $teacher->id,
            'subject'    => $validated['subject'],
            'room'       => $validated['room'] ?? null,
        ]);

        return redirect()->route('manager.schedule.show', $slot->klass_id)
            ->with('success', 'درس با موفقیت ویرایش شد');
    }

    public function destroy(ScheduleSlot $slot)
    {
        $this->authorizeSlot($slot);

        $klassId = $slot->klass_id;

        if ($slot->bbb_meeting_id) {
            try {
                $bbb = new BBBClient();
                $bbb->endMeeting($slot->bbb_meeting_id);
            } catch (\Throwable $e) {
                Log::warning('Failed to end BBB meeting: ' . $e->getMessage());
            }
        }

        $slot->delete();

        return redirect()->route('manager.schedule.show', $klassId)
            ->with('success', 'درس حذف شد');
    }

    protected function authorizeKlass(Klass $klass): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $klass->school_id !== $user->school_id) {
            abort(403);
        }
    }

    protected function authorizeSlot(ScheduleSlot $slot): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $slot->school_id !== $user->school_id) {
            abort(403);
        }
    }
}
