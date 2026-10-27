<?php

use App\Exports\AttendanceExport;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BellController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseWizardController;
use App\Http\Controllers\ExtraClassController;
use App\Http\Controllers\ExtraCourseController;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MoodleSsoController;
use App\Http\Controllers\MyScheduleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleImportController;
use App\Http\Controllers\StudentImportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
// تازه‌سازی CSRF token
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.token');

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isManager()) {
            return redirect()->route('manager.dashboard');
        }
        if ($user->isStudent()) {
            return redirect()->route('student.home');
        }
        if ($user->isTeacher()) {
            return redirect()->route('my-schedule');
        }

        return redirect()->route('courses.index');
    }

    return view('landing');
})->name('home');

// === احراز هویت ===
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::get('/register/pending', [AuthController::class, 'showPending'])->name('register.pending');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// === ورود مهمان با لینک مستقیم ===
Route::get('/join/{meetingId}', [JoinController::class, 'show'])->name('guest.join.show');
Route::post('/join/{meetingId}', [JoinController::class, 'join'])->name('guest.join.submit');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // === بازگشت از Impersonate ===
    Route::post('/admin/stop-impersonate', [AdminController::class, 'stopImpersonate'])
        ->name('admin.stop-impersonate');

    // === پروفایل ===
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // === برنامه‌ی من ===
    Route::get('/my-schedule', [MyScheduleController::class, 'index'])->name('my-schedule');
    Route::get('/my-schedule/student', [MyScheduleController::class, 'student'])->name('my-schedule.student');
    Route::get('/my-schedule/slot/{slot}/join', [MyScheduleController::class, 'join'])->name('my-schedule.join');

    // === صفحه‌ی خانه‌ی دانش‌آموز ===
    Route::get('/student-home', [MyScheduleController::class, 'studentHome'])->name('student.home');

    // === حضور من ===
    Route::get('/my-attendance', [MyScheduleController::class, 'myAttendance'])->name('my-attendance');
    Route::get('/my-attendance/export', [MyScheduleController::class, 'exportAttendance'])->name('my-attendance.export');

    // === منابع کلاس ===
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials');
    Route::get('/materials/class/{klass}', [MaterialController::class, 'show'])->name('materials.class');
    Route::post('/materials/class/{klass}', [MaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');


    // === اطلاعیه‌ها (دید کاربر) ===
    Route::get('/announcements', [AnnouncementController::class, 'feed'])->name('announcements.feed');

    // === اطلاعیه‌ها (مدیریت) ===
    Route::prefix('manage/announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
        Route::post('/', [AnnouncementController::class, 'store'])->name('store');
        Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
        Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
    });

    // === کلاس‌های فوق‌برنامه‌ی قدیمی ===
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}/join', [CourseController::class, 'join'])->name('courses.join');
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

    // === فقط ادمین و مدیر: ساخت کلاس قدیمی ===
    Route::middleware('can.create.course')->group(function () {
        Route::get('/courses/create', function () {
            return view('courses.create-select');
        })->name('courses.create.select');

        Route::get('/courses/create/extra', [ExtraCourseController::class, 'create'])->name('courses.create.extra');
        Route::post('/courses/create/extra', [ExtraCourseController::class, 'store'])->name('courses.create.extra.store');

        Route::prefix('courses/create/regular')->name('courses.create.regular')->group(function () {
            Route::get('/', [CourseWizardController::class, 'step1'])->name('');
            Route::post('/step1', [CourseWizardController::class, 'step1Store'])->name('.step1.store');
            Route::get('/step2', [CourseWizardController::class, 'step2'])->name('.step2');
            Route::post('/step2', [CourseWizardController::class, 'step2Store'])->name('.step2.store');
            Route::get('/step3', [CourseWizardController::class, 'step3'])->name('.step3');
            Route::post('/step3', [CourseWizardController::class, 'step3Store'])->name('.step3.store');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // مدارس
    Route::get('/schools', [AdminController::class, 'schools'])->name('schools');
    Route::get('/schools/create', [AdminController::class, 'createSchool'])->name('schools.create');
    Route::post('/schools', [AdminController::class, 'storeSchool'])->name('schools.store');
    Route::get('/schools/{school}/edit', [AdminController::class, 'editSchool'])->name('schools.edit');
    Route::put('/schools/{school}', [AdminController::class, 'updateSchool'])->name('schools.update');
    Route::get('/schools/{school}/plan', [AdminController::class, 'editSchoolPlan'])->name('schools.plan.edit');
    Route::put('/schools/{school}/plan', [AdminController::class, 'updateSchoolPlan'])->name('schools.plan.update');

    // مدیران
    Route::get('/managers', [AdminController::class, 'managers'])->name('managers');
    Route::get('/managers/create', [AdminController::class, 'createManager'])->name('managers.create');
    Route::post('/managers', [AdminController::class, 'storeManager'])->name('managers.store');

    // کاربران
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/bulk-reset-password', [AdminController::class, 'bulkResetPassword'])->name('users.bulk-reset-password');
    Route::post('/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{user}/impersonate', [AdminController::class, 'impersonate'])->name('users.impersonate');
});

/*
|--------------------------------------------------------------------------
| Manager Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'manager'])->prefix('manager')->name('manager.')->group(function () {

    Route::get('/', [ManagerController::class, 'dashboard'])->name('dashboard');

    // معلمان
    Route::get('/teachers', [ManagerController::class, 'teachers'])->name('teachers');
    Route::post('/teachers/{user}/approve', [ManagerController::class, 'approveTeacher'])->name('teachers.approve');
    Route::post('/teachers/{user}/reject', [ManagerController::class, 'rejectTeacher'])->name('teachers.reject');
    Route::post('/teachers/{user}/toggle-status', [ManagerController::class, 'toggleTeacherStatus'])->name('teachers.toggle-status');
    Route::get('/teachers/create', [ManagerController::class, 'createTeacher'])->name('teachers.create');
    Route::post('/teachers', [ManagerController::class, 'storeTeacher'])->name('teachers.store');

    // پایه‌ها
    Route::get('/levels', [ManagerController::class, 'levels'])->name('levels');
    Route::get('/levels/create', [ManagerController::class, 'createLevel'])->name('levels.create');
    Route::post('/levels', [ManagerController::class, 'storeLevel'])->name('levels.store');
    Route::delete('/levels/{level}', [ManagerController::class, 'destroyLevel'])->name('levels.destroy');

    // کلاس‌ها
    Route::get('/klasses', [ManagerController::class, 'klasses'])->name('klasses');
    Route::get('/klasses/create', [ManagerController::class, 'createKlass'])->name('klasses.create');
    Route::post('/klasses', [ManagerController::class, 'storeKlass'])->name('klasses.store');
    Route::get('/klasses/{klass}/edit', [ManagerController::class, 'editKlass'])->name('klasses.edit');
    Route::put('/klasses/{klass}', [ManagerController::class, 'updateKlass'])->name('klasses.update');
    Route::delete('/klasses/{klass}', [ManagerController::class, 'destroyKlass'])->name('klasses.destroy');

    // زنگ‌ها
    Route::get('/bells', [BellController::class, 'index'])->name('bells');
    Route::get('/bells/create', [BellController::class, 'create'])->name('bells.create');
    Route::post('/bells', [BellController::class, 'store'])->name('bells.store');
    Route::get('/bells/{bell}/edit', [BellController::class, 'edit'])->name('bells.edit');
    Route::put('/bells/{bell}', [BellController::class, 'update'])->name('bells.update');
    Route::delete('/bells/{bell}', [BellController::class, 'destroy'])->name('bells.destroy');

    // برنامه هفتگی
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
    Route::get('/schedule/{klass}', [ScheduleController::class, 'show'])->name('schedule.show');
    Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
    Route::put('/schedule/{slot}', [ScheduleController::class, 'update'])->name('schedule.update');
    Route::delete('/schedule/{slot}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
    Route::get('/schedule/{slot}/join', [ScheduleController::class, 'join'])->name('schedule.join');

    // ایمپورت اکسل برنامه هفتگی
    Route::get('/schedule-import', [ScheduleImportController::class, 'show'])->name('schedule.import');
    Route::post('/schedule-import', [ScheduleImportController::class, 'import'])->name('schedule.import.store');
    Route::get('/schedule-import/template', [ScheduleImportController::class, 'template'])->name('schedule.import.template');

    // ایمپورت دانش‌آموزان
    Route::get('/students/import', [StudentImportController::class, 'show'])->name('students.import');
    Route::post('/students/import', [StudentImportController::class, 'import'])->name('students.import.store');
    Route::get('/students/template', [StudentImportController::class, 'template'])->name('students.template');
    Route::get('/api/klasses-by-level', [StudentImportController::class, 'klassesByLevel'])->name('api.klasses');

    // کلاس فوق‌برنامه (جدید)
    Route::get('/extra-class', [ExtraClassController::class, 'index'])->name('extra-class.index');
    Route::get('/extra-class/create', [ExtraClassController::class, 'create'])->name('extra-class.create');
    Route::post('/extra-class', [ExtraClassController::class, 'store'])->name('extra-class.store');
    Route::delete('/extra-class/{slot}', [ExtraClassController::class, 'destroy'])->name('extra-class.destroy');

    // گزارش حضور
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::get('/attendance/dashboard', [ManagerController::class, 'attendanceDashboard'])->name('attendance.dashboard');
    Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
    Route::post('/attendance/close-stale', [AttendanceController::class, 'closeStale'])->name('attendance.close-stale');
    Route::get('/attendance/{log}', [AttendanceController::class, 'show'])->name('attendance.show');
});
