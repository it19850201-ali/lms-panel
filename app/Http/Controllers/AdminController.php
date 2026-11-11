<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use App\Models\Klass;
use App\Models\School;
use App\Models\ScheduleSlot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // ============ داشبورد ============
    public function dashboard()
    {
        $stats = [
            'schools'         => School::count(),
            'active_schools'  => School::where('is_active', true)->count(),
            'managers'        => User::where('role', 'manager')->count(),
            'teachers'        => User::where('role', 'teacher')->count(),
            'students'        => User::where('role', 'student')->count(),
            'pending'         => User::where('status', 'pending')->count(),
            'klasses'         => Klass::count(),
            'slots'           => ScheduleSlot::count(),
            'attendance_today' => AttendanceEvent::where('event_type','join')->whereDate('occurred_at', today())->count(),
        ];

        // آمار هر مدرسه
        $schools = School::withCount([
            'users',
            'users as teachers_count' => fn($q) => $q->where('role', 'teacher'),
            'users as students_count' => fn($q) => $q->where('role', 'student'),
        ])->latest()->take(10)->get();

        // آخرین کاربران ثبت‌نام‌کرده
        $recentUsers = User::orderBy('id', 'desc')->take(10)->get();

        return view('admin.dashboard', compact('stats', 'schools', 'recentUsers'));
    }

    // ============ مدارس ============
    public function schools()
    {
        $schools = School::withCount('users')->latest()->get();
        return view('admin.schools.index', compact('schools'));
    }

    public function createSchool()
    {
        return view('admin.schools.create');
    }

    public function storeSchool(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:school,college,institute',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
        ]);

        $validated['slug'] = (new School)->generateSlug($validated['name']);
        $validated['is_active'] = true;

        School::create($validated);

        return redirect()->route('admin.schools')->with('success', 'مدرسه با موفقیت ساخته شد');
    }

    public function editSchool(School $school)
    {
        return view('admin.schools.edit', compact('school'));
    }

    public function updateSchool(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:school,college,institute',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $school->update($validated);

        return redirect()->route('admin.schools')->with('success', 'مدرسه با موفقیت ویرایش شد');
    }

    // ============ مدیران ============
    public function managers()
    {
        $managers = User::where('role', 'manager')->with('school')->latest()->get();
        return view('admin.managers.index', compact('managers'));
    }

    public function createManager()
    {
        $schools = School::where('is_active', true)->orderBy('name')->get();
        return view('admin.managers.create', compact('schools'));
    }

    public function storeManager(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:11|unique:users,mobile',
            'code_melli' => 'required|digits:10|unique:users,code_melli',
            'school_id' => 'required|exists:schools,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'code_melli' => $validated['code_melli'],
            'email' => $validated['mobile'] . '@placeholder.local',
            'password' => Hash::make($validated['code_melli']),
            'role' => 'manager',
            'school_id' => $validated['school_id'],
            'status' => 'approved',
        ]);

        return redirect()->route('admin.managers')->with('success', 'مدیر با موفقیت ساخته شد');
    }

    // ============ کاربران ============
    public function users(Request $request)
    {
        $query = User::with('school')->orderBy('id', 'desc');

        // فیلتر
        if ($schoolId = $request->input('school_id')) {
            $query->where('school_id', $schoolId);
        }
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('code_melli', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(30);
        $schools = School::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'schools'));
    }

    /**
     * ریست رمز یک کاربر → کد ملی
     */
    public function resetPassword(User $user)
    {
        if (empty($user->code_melli)) {
            return back()->withErrors(['error' => 'این کاربر کد ملی ندارد']);
        }

        $user->update([
            'password'            => Hash::make($user->code_melli),
            'password_reset_at'   => now(),
            'password_changed_at' => null, // یعنی رمز پیش‌فرضه
        ]);

        \Log::info("Password reset by admin for user {$user->id} ({$user->name})");

        return back()->with('success', "رمز {$user->name} به کد ملی ({$user->code_melli}) بازگشت");
    }
    /**
     * ریست گروهی
     */
    public function bulkResetPassword(Request $request)
    {
        $validated = $request->validate([
            'scope' => 'required|in:all,school,role',
            'school_id' => 'nullable|exists:schools,id',
            'role' => 'nullable|in:manager,teacher,student',
        ]);

        $query = User::query();

        if ($validated['scope'] === 'school' && !empty($validated['school_id'])) {
            $query->where('school_id', $validated['school_id']);
        } elseif ($validated['scope'] === 'role' && !empty($validated['role'])) {
            $query->where('role', $validated['role']);
        }

        $query->whereNotNull('code_melli')->where('code_melli', '!=', '');
        $query->where('role', '!=', 'admin');

        $users = $query->get();
        $count = 0;

        foreach ($users as $user) {
            $user->update([
                'password'            => Hash::make($user->code_melli),
                'password_reset_at'   => now(),
                'password_changed_at' => null,
            ]);
            $count++;
        }

        \Log::warning("Bulk password reset by admin: {$count} users");

        return back()->with('success', "رمز {$count} کاربر به کد ملی بازگشت");
    }

    // ============ Impersonate ============
    public function impersonate(User $user)
    {
        $admin = Auth::user();

        if (!$admin->isAdmin()) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return back()->withErrors(['error' => 'نمی‌توانید به ادمین دیگر وارد شوید']);
        }

        // ذخیره ادمین اصلی در session
        session(['impersonator_id' => $admin->id]);

        // آپدیت رکورد کاربر
        $user->update(['impersonated_by' => $admin->id]);

        Auth::login($user);

        Log::warning("Admin {$admin->id} ({$admin->name}) impersonated user {$user->id} ({$user->name})");

        return redirect()->route('home')->with('success', "ورود به اکانت {$user->name}");
    }

    public function stopImpersonate()
    {
        $impersonatorId = session('impersonator_id');

        if (!$impersonatorId) {
            return redirect()->route('home');
        }

        $admin = User::find($impersonatorId);

        if (!$admin) {
            session()->forget('impersonator_id');
            return redirect()->route('login');
        }

        // کاربر فعلی = جعلی، پاکش کن
        $current = Auth::user();
        if ($current && $current->impersonated_by) {
            $current->update(['impersonated_by' => null]);
        }

        session()->forget('impersonator_id');
        Auth::login($admin);

        return redirect()->route('admin.dashboard')->with('success', 'بازگشت به حساب ادمین');
    }
    public function editSchoolPlan(School $school)
    {
        return view('admin.schools.plan', compact('school'));
    }

    public function updateSchoolPlan(Request $request, School $school)
    {
        $validated = $request->validate([
            'plan_max_total_users' => 'required|integer|min:1|max:100000',
            'plan_max_users_per_class' => 'required|integer|min:1|max:1000',
            'plan_max_cameras' => 'required|integer|min:0|max:100',
            'plan_max_concurrent' => 'required|integer|min:1|max:200',
        ], [
            'plan_max_total_users.required' => 'حداکثر کاربر کل مدرسه را وارد کنید',
            'plan_max_users_per_class.required' => 'حداکثر کاربر هر کلاس را وارد کنید',
            'plan_max_cameras.required' => 'حداکثر دوربین را وارد کنید',
            'plan_max_concurrent.required' => 'حداکثر کلاس هم‌زمان را وارد کنید',
        ]);

        $school->update($validated);

        return redirect()->route('admin.schools')->with('success', 'پلن مدرسه با موفقیت به‌روز شد');
    }
}
