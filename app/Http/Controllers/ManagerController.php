<?php

namespace App\Http\Controllers;

use App\Models\AcademicLevel;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceEvent;
use Carbon\Carbon;
use App\Services\KlassMoodleSync;
use App\Models\Klass;
use App\Models\User;
use App\Services\MoodleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ManagerController extends Controller
{
    /**
     * school_id کاربر جاری:
     *  - ادمین: همه مدارس (null → فیلتر اعمال نمی‌شه)
     *  - مدیر: فقط مدرسه‌ی خودش
     */
    protected function getSchoolId(): ?int
    {
        $user = auth()->user();
        return $user->isAdmin() ? null : $user->school_id;
    }

    /**
     * اطمینان از وجود school_id (ادمین اجازه نداره)
     */
    protected function ensureSchool(): int
    {
        $schoolId = $this->getSchoolId();

        if (!$schoolId) {
            abort(403, 'ادمین باید از پنل مدرسه‌ی مورد نظر این کار را انجام دهد.');
        }

        return $schoolId;
    }

    // ========== داشبورد ==========
    public function dashboard()
    {
        $schoolId = $this->getSchoolId();

        $pendingCount = User::where('role', 'teacher')
            ->where('status', 'pending')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->count();

        $approvedCount = User::where('role', 'teacher')
            ->where('status', 'approved')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->count();

        $levelsCount = AcademicLevel::when($schoolId, fn($q) => $q->where('school_id', $schoolId))->count();
        $klassesCount = Klass::when($schoolId, fn($q) => $q->where('school_id', $schoolId))->count();
        $studentsCount = User::where('role', 'student')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->count();

        return view('manager.dashboard', compact(
            'pendingCount',
            'approvedCount',
            'levelsCount',
            'klassesCount',
            'studentsCount'
        ));
    }

    // ========== معلمان ==========
    public function teachers()
    {
        $schoolId = $this->getSchoolId();

        $pending = User::where('role', 'teacher')
            ->where('status', 'pending')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->with('school')
            ->latest()
            ->get();

        $approved = User::where('role', 'teacher')
            ->where('status', 'approved')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->with('school')
            ->latest()
            ->get();

        $rejected = User::where('role', 'teacher')
            ->where('status', 'rejected')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->with('school')
            ->latest()
            ->get();

        return view('manager.teachers', compact('pending', 'approved', 'rejected'));
    }
    public function approveTeacher(User $user)
    {
        $manager = auth()->user();
        $this->authorizeTeacher($user, $manager);

        if ($user->status !== 'pending') {
            return back()->withErrors(['error' => 'این کاربر قبلاً بررسی شده است.']);
        }

        try {
            $moodle = new MoodleClient();
            $nameParts = explode(' ', trim($user->name), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '.';

            $moodleUser = $moodle->createOrGetUser($user->email, $firstName, $lastName);

            $user->update([
                'status' => 'approved',
                'moodle_user_id' => $moodleUser['id'] ?? null,
            ]);

            return back()->with('success', "معلم {$user->name} تایید شد" . ($user->moodle_user_id ? ' و در Moodle ساخته شد' : ''));

        } catch (\Throwable $e) {
            Log::error('Moodle user creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'خطا در ساخت کاربر در Moodle: ' . $e->getMessage()]);
        }
    }

    public function rejectTeacher(User $user)
    {
        $manager = auth()->user();
        $this->authorizeTeacher($user, $manager);

        $user->update(['status' => 'rejected']);

        return back()->with('success', "درخواست {$user->name} رد شد");
    }
    public function toggleTeacherStatus(User $user)
    {
        $manager = auth()->user();
        $this->authorizeTeacher($user, $manager);

        if ($user->status === 'approved') {
            // غیرفعال کن
            $user->update(['status' => 'rejected']);
            $message = "معلم {$user->name} غیرفعال شد";
        } elseif ($user->status === 'rejected') {
            // فعال کن (اگه Moodle نداشت، بسازش)
            if (!$user->moodle_user_id) {
                try {
                    $moodle = new MoodleClient();
                    $nameParts = explode(' ', trim($user->name), 2);
                    $firstName = $nameParts[0];
                    $lastName = $nameParts[1] ?? '.';

                    $moodleUser = $moodle->createOrGetUser($user->email, $firstName, $lastName);
                    $user->moodle_user_id = $moodleUser['id'] ?? null;
                } catch (\Throwable $e) {
                    \Log::warning('Moodle user creation on reactivate failed: ' . $e->getMessage());
                }
            }

            $user->status = 'approved';
            $user->save();
            $message = "معلم {$user->name} فعال شد";
        } else {
            return back()->withErrors(['error' => 'این کاربر در وضعیت قابل تغییر نیست.']);
        }

        return back()->with('success', $message);
    }
    public function createTeacher()
    {
        $schoolId = $this->getSchoolId();

        if (!$schoolId) {
            abort(403, 'ادمین باید از پنل مدرسه این کار را انجام دهد');
        }

        return view('manager.teachers-create');
    }

    public function storeTeacher(Request $request)
    {
        $schoolId = $this->getSchoolId();

        if (!$schoolId) {
            abort(403);
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code_melli' => 'required|digits:10|unique:users,code_melli',
            'mobile'     => 'required|digits:11|unique:users,mobile',
            'password'   => 'nullable|string|min:6|confirmed',
        ], [
            'name.required'       => 'نام و نام‌خانوادگی را وارد کنید',
            'code_melli.required' => 'کد ملی را وارد کنید',
            'code_melli.digits'   => 'کد ملی باید ۱۰ رقم باشد',
            'code_melli.unique'   => 'این کد ملی قبلاً ثبت شده است',
            'mobile.required'     => 'شماره موبایل را وارد کنید',
            'mobile.digits'       => 'موبایل باید ۱۱ رقم باشد',
            'mobile.unique'       => 'این شماره موبایل قبلاً ثبت شده است',
            'password.min'        => 'رمز عبور باید حداقل ۶ کاراکتر باشد',
            'password.confirmed'  => 'تکرار رمز مطابقت ندارد',
        ]);

        // اگه رمز ندادی، کد ملی رمز می‌شه
        $password = !empty($validated['password']) ? $validated['password'] : $validated['code_melli'];

        $teacher = User::create([
            'name'                => $validated['name'],
            'code_melli'          => $validated['code_melli'],
            'mobile'              => $validated['mobile'],
            'email'               => $validated['code_melli'] . '@placeholder.local',
            'password'            => \Hash::make($password),
            'role'                => 'teacher',
            'school_id'           => $schoolId,
            'status'              => 'approved',
            'password_changed_at' => !empty($validated['password']) ? now() : null,
        ]);

        // ساخت خودکار توی Moodle
        try {
            $moodle = new MoodleClient();
            $nameParts = explode(' ', trim($teacher->name), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '.';

            $moodleUser = $moodle->createOrGetUser($teacher->email, $firstName, $lastName);

            if (!empty($moodleUser['id'])) {
                $teacher->update(['moodle_user_id' => $moodleUser['id']]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Moodle user creation for new teacher failed: ' . $e->getMessage());
        }

        return redirect()->route('manager.teachers')
            ->with('success', "معلم {$teacher->name} با موفقیت ساخته شد" . ($teacher->fresh()->moodle_user_id ? ' و در Moodle ثبت شد' : ''));
    }

    protected function authorizeTeacher(User $user, User $manager): void
    {
        if ($user->role !== 'teacher') {
            abort(403);
        }

        if (!$manager->isAdmin() && $user->school_id !== $manager->school_id) {
            abort(403, 'این معلم به مدرسه‌ی شما تعلق ندارد');
        }
    }

    // ========== پایه‌ها ==========
    public function levels()
    {
        $schoolId = $this->getSchoolId();

        $levels = AcademicLevel::withCount(['klasses'])
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('manager.levels.index', compact('levels'));
    }

    public function createLevel()
    {
        return view('manager.levels.create');
    }

    public function storeLevel(Request $request)
    {
        $schoolId = $this->getSchoolId();

        if (!$schoolId) {
            return back()->withErrors(['error' => 'ادمین باید از پنل مدرسه‌ی مورد نظر این کار را انجام دهد.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $exists = AcademicLevel::where('school_id', $schoolId)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'این پایه/گروه قبلاً ثبت شده است.'])->withInput();
        }

        AcademicLevel::create([
            'school_id' => $schoolId,
            'name' => $validated['name'],
            'order' => $validated['order'] ?? 0,
        ]);

        return redirect()->route('manager.levels')->with('success', 'پایه با موفقیت اضافه شد');
    }

    public function destroyLevel(AcademicLevel $level)
    {
        $this->authorizeLevel($level);

        $level->delete();

        return back()->with('success', 'پایه حذف شد');
    }

    protected function authorizeLevel(AcademicLevel $level): void
    {
        $manager = auth()->user();

        if (!$manager->isAdmin() && $level->school_id !== $manager->school_id) {
            abort(403);
        }
    }

    // ========== کلاس‌ها ==========
    public function klasses()
    {
        $schoolId = $this->getSchoolId();

        $klasses = Klass::with(['academicLevel'])
            ->withCount('students')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('academic_level_id')
            ->orderBy('name')
            ->get();

        return view('manager.klasses.index', compact('klasses'));
    }

    public function createKlass()
    {
        $schoolId = $this->getSchoolId();

        $levels = AcademicLevel::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('manager.klasses.create', compact('levels'));
    }

    public function storeKlass(Request $request)
    {
        $schoolId = $this->ensureSchool();

        $validated = $request->validate([
            'academic_level_id' => 'required|exists:academic_levels,id',
            'name' => 'required|string|max:255',
        ]);

        $level = AcademicLevel::where('id', $validated['academic_level_id'])
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $exists = Klass::where('academic_level_id', $level->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'این کلاس در این پایه قبلاً ثبت شده است.'])->withInput();
        }

        $klass = Klass::create([
            'school_id' => $schoolId,
            'academic_level_id' => $level->id,
            'name' => $validated['name'],
        ]);

        // ساخت خودکار Moodle course
        try {
            $sync = new KlassMoodleSync();
            $sync->ensureCourse($klass);
        } catch (\Throwable $e) {
            // خطای Moodle مانع ساخت Klass نمی‌شه
            \Log::warning('Auto-sync Moodle on klass create failed: ' . $e->getMessage());
        }

        return redirect()->route('manager.klasses')->with('success', 'کلاس با موفقیت ساخته شد' . ($klass->fresh()->moodle_course_id ? ' و در Moodle ثبت شد' : ''));
    }

    public function editKlass(Klass $klass)
    {
        $this->authorizeKlass($klass);

        $schoolId = $this->getSchoolId();

        $levels = AcademicLevel::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('manager.klasses.edit', compact('klass', 'levels'));
    }

    public function updateKlass(Request $request, Klass $klass)
    {
        $this->authorizeKlass($klass);

        $validated = $request->validate([
            'academic_level_id' => 'required|exists:academic_levels,id',
            'name' => 'required|string|max:255',
        ]);

        // اطمینان از اینکه level به همین مدرسه تعلق داره
        $level = AcademicLevel::where('id', $validated['academic_level_id'])
            ->where('school_id', $klass->school_id)
            ->firstOrFail();

        // چک تکراری بودن (به جز خودش)
        $exists = Klass::where('academic_level_id', $level->id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $klass->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'این کلاس در این پایه قبلاً ثبت شده است.'])->withInput();
        }

        $klass->update([
            'academic_level_id' => $level->id,
            'name' => $validated['name'],
        ]);

        return redirect()->route('manager.klasses')->with('success', 'کلاس با موفقیت ویرایش شد');
    }


    public function destroyKlass(Klass $klass)
    {
        $this->authorizeKlass($klass);

        $klass->delete();

        return back()->with('success', 'کلاس حذف شد');
    }

    public function syncKlasses()
    {
        $schoolId = $this->ensureSchool();

        try {
            $sync = new KlassMoodleSync();
            $stats = $sync->syncSchool($schoolId);

            $message = "همگام‌سازی کامل شد. ";
            $message .= "کلاس‌های موفق: {$stats['klasses_ok']} از {$stats['klasses_total']}. ";
            $message .= "دانش‌آموزان: {$stats['students_total']}. ";
            $message .= "معلمان: {$stats['teachers_total']}.";

            if (!empty($stats['errors'])) {
                return redirect()->route('manager.klasses')
                    ->with('success', $message)
                    ->with('sync_errors', $stats['errors']);
            }

            return redirect()->route('manager.klasses')->with('success', $message);

        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'خطا در همگام‌سازی: ' . $e->getMessage()]);
        }
    }
    public function attendanceDashboard(Request $request)
    {
        $schoolId = $this->getSchoolId();

        // بازه‌ی تاریخ
        $range = $request->input('range', 'today');
        $from = $request->input('from');
        $to = $request->input('to');

        [$startDate, $endDate] = $this->resolveDateRange($range, $from, $to);

        // === آمار کلی ===
        $baseQuery = AttendanceEvent::query()
            ->where("event_type", "join")
            ->whereDate('occurred_at', '>=', $startDate->toDateString())
            ->whereDate('occurred_at', '<=', $endDate->toDateString())
            ->when($schoolId, fn($q) => $q->whereHas('scheduleSlot', fn($sq) => $sq->where('school_id', $schoolId)));

        $totalSessions = (clone $baseQuery)->distinct('schedule_slot_id')->count('schedule_slot_id');
        $totalAttendances = (clone $baseQuery)->count();
        $totalTeachers = (clone $baseQuery)->where('role', 'MODERATOR')->distinct('user_id')->count('user_id');
        $totalStudents = (clone $baseQuery)->where('role', 'VIEWER')->distinct('user_id')->count('user_id');

        // === حضور به تفکیک روز ===
        $dailyStats = (clone $baseQuery)
            ->selectRaw('DATE(occurred_at) as session_date, count(*) as total, count(distinct schedule_slot_id) as sessions')
            ->groupBy(DB::raw('DATE(occurred_at)'))
            ->orderBy('session_date', 'desc')
            ->limit(30)
            ->get();

        // === معلم‌های برتر (بیشترین حضور) ===
        $topTeachers = (clone $baseQuery)
            ->where('role', 'MODERATOR')
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function ($logs) {
                return [
                    'user'   => $logs->first()->user,
                    'count'  => $logs->count(),
                    'total_duration' => $logs->sum(fn($l) => $l->calculateDuration()),
                ];
            })
            ->sortByDesc('count')
            ->take(10);

        // === کلاس‌های پرحضور ===
        $topKlasses = (clone $baseQuery)
            ->with('scheduleSlot.klass.academicLevel')
            ->get()
            ->groupBy(fn($l) => $l->scheduleSlot?->klass_id)
            ->filter(fn($group, $klassId) => $klassId)
            ->map(function ($logs) {
                $klass = $logs->first()->scheduleSlot?->klass;
                return [
                    'klass' => $klass,
                    'count' => $logs->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(10);

        return view('manager.attendance.dashboard', compact(
            'range', 'from', 'to', 'startDate', 'endDate',
            'totalSessions', 'totalAttendances', 'totalTeachers', 'totalStudents',
            'dailyStats', 'topTeachers', 'topKlasses'
        ));
    }

    protected function resolveDateRange(string $range, ?string $from, ?string $to): array
    {
        return match ($range) {
            'week'  => [Carbon::today()->subDays(6), Carbon::today()],
            'month' => [Carbon::today()->subDays(29), Carbon::today()],
            'custom' => [
                $from ? Carbon::parse($from) : Carbon::today()->subDays(6),
                $to ? Carbon::parse($to) : Carbon::today(),
            ],
            default => [Carbon::today(), Carbon::today()], // today
        };
    }

    protected function authorizeKlass(Klass $klass): void
    {
        $manager = auth()->user();

        if (!$manager->isAdmin() && $klass->school_id !== $manager->school_id) {
            abort(403);
        }
    }
}
