<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\Klass;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * لیست اطلاعیه‌های قابل مدیریت (مدیر/معلم)
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isManager()) {
            $schoolId = $user->isAdmin() ? null : $user->school_id;
            $announcements = Announcement::with(['author', 'klass'])
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->orderBy('is_active', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // معلم فقط اطلاعیه‌های خودش
            $announcements = Announcement::with(['author', 'klass'])
                ->where('author_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('announcements.index', compact('announcements'));
    }
    public function create()
    {
        $user = auth()->user();
        $schoolId = $user->isAdmin() ? null : $user->school_id;

        if (!$schoolId) {
            abort(403, 'ادمین باید از پنل مدرسه این کار را انجام دهد');
        }

        $klasses = Klass::with('academicLevel')
            ->where('school_id', $schoolId)
            ->orderBy('academic_level_id')
            ->orderBy('name')
            ->get();

        // معلم فقط می‌تونه برای کلاس‌هایی که توش درس می‌ده اطلاعیه بذاره
        if ($user->isTeacher()) {
            $teacherKlassIds = \App\Models\ScheduleSlot::where('teacher_id', $user->id)
                ->pluck('klass_id')
                ->unique()
                ->filter();

            $klasses = $klasses->whereIn('id', $teacherKlassIds);
        }

        return view('announcements.create', compact('klasses'));
    }
    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->isAdmin() ? null : $user->school_id;

        if (!$schoolId) {
            abort(403);
        }

        // === معلم فقط اجازه‌ی 'class' داره ===
        if ($user->isTeacher()) {
            $allowedAudiences = ['class'];
        } else {
            $allowedAudiences = ['all', 'class', 'teachers'];
        }

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'body'          => 'required|string|max:5000',
            'audience_type' => 'required|in:' . implode(',', $allowedAudiences),
            'klass_id'      => 'required_if:audience_type,class|nullable|exists:klasses,id',
            'priority'      => 'required|in:normal,important,urgent',
            'expires_at'    => 'nullable|string',
        ], [
            'title.required'       => 'عنوان را وارد کنید',
            'body.required'        => 'متن اطلاعیه را وارد کنید',
            'klass_id.required_if' => 'کلاس را انتخاب کنید',
            'audience_type.in'     => 'شما فقط می‌توانید برای یک کلاس خاص اطلاعیه منتشر کنید',
        ]);

        // چک اضافی: اگه معلمه، کلاس باید از کلاس‌های خودش باشه
        if ($user->isTeacher() && $validated['audience_type'] === 'class') {
            $teaches = \App\Models\ScheduleSlot::where('teacher_id', $user->id)
                ->where('klass_id', $validated['klass_id'])
                ->exists();

            if (!$teaches) {
                return back()->withErrors(['klass_id' => 'شما فقط می‌توانید برای کلاس‌هایی که در آن‌ها درس می‌دهید اطلاعیه منتشر کنید'])->withInput();
            }
        }

        $expiresAt = null;
        if (!empty($validated['expires_at'])) {
            $expiresAt = \App\Helpers\JalaliHelper::toGregorian($validated['expires_at']);
            if (!$expiresAt) {
                return back()->withErrors(['expires_at' => 'فرمت تاریخ نامعتبر است'])->withInput();
            }
        }

        Announcement::create([
            'school_id'     => $schoolId,
            'author_id'     => $user->id,
            'title'         => $validated['title'],
            'body'          => $validated['body'],
            'audience_type' => $validated['audience_type'],
            'klass_id'      => $validated['audience_type'] === 'class' ? $validated['klass_id'] : null,
            'priority'      => $validated['priority'],
            'expires_at'    => $expiresAt,
            'is_active'     => true,
        ]);

        return redirect()->route('announcements.index')->with('success', 'اطلاعیه با موفقیت منتشر شد');
    }
    public function edit(Announcement $announcement)
    {
        $this->authorizeEdit($announcement);

        $user = auth()->user();
        $schoolId = $user->isAdmin() ? null : $user->school_id;

        $klasses = Klass::with('academicLevel')
            ->where('school_id', $schoolId ?? $announcement->school_id)
            ->orderBy('academic_level_id')
            ->orderBy('name')
            ->get();

        // معلم فقط کلاس‌های خودش
        if ($user->isTeacher()) {
            $teacherKlassIds = \App\Models\ScheduleSlot::where('teacher_id', $user->id)
                ->pluck('klass_id')
                ->unique()
                ->filter();

            $klasses = $klasses->whereIn('id', $teacherKlassIds);
        }

        return view('announcements.edit', compact('announcement', 'klasses'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorizeEdit($announcement);

        $user = auth()->user();

        $allowedAudiences = $user->isTeacher() ? ['class'] : ['all', 'class', 'teachers'];

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'body'          => 'required|string|max:5000',
            'audience_type' => 'required|in:' . implode(',', $allowedAudiences),
            'klass_id'      => 'required_if:audience_type,class|nullable|exists:klasses,id',
            'priority'      => 'required|in:normal,important,urgent',
            'expires_at'    => 'nullable|string',
            'is_active'     => 'nullable|boolean',
        ]);

        if ($user->isTeacher() && $validated['audience_type'] === 'class') {
            $teaches = \App\Models\ScheduleSlot::where('teacher_id', $user->id)
                ->where('klass_id', $validated['klass_id'])
                ->exists();

            if (!$teaches) {
                return back()->withErrors(['klass_id' => 'شما فقط می‌توانید برای کلاس‌های خودتان اطلاعیه منتشر کنید'])->withInput();
            }
        }

        $expiresAt = null;
        if (!empty($validated['expires_at'])) {
            $expiresAt = \App\Helpers\JalaliHelper::toGregorian($validated['expires_at']);
            if (!$expiresAt) {
                return back()->withErrors(['expires_at' => 'فرمت تاریخ نامعتبر است'])->withInput();
            }
        }

        $announcement->update([
            'title'         => $validated['title'],
            'body'          => $validated['body'],
            'audience_type' => $validated['audience_type'],
            'klass_id'      => $validated['audience_type'] === 'class' ? $validated['klass_id'] : null,
            'priority'      => $validated['priority'],
            'expires_at'    => $expiresAt,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return redirect()->route('announcements.index')->with('success', 'اطلاعیه ویرایش شد');
    }
    public function destroy(Announcement $announcement)
    {
        $this->authorizeEdit($announcement);

        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'اطلاعیه حذف شد');
    }

    protected function authorizeEdit(Announcement $announcement): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) return;

        if ($user->isManager() && $announcement->school_id === $user->school_id) return;

        if ($user->isTeacher() && $announcement->author_id === $user->id) return;

        abort(403);
    }

    // ============ نمای کاربران ============

    /**
     * لیست اطلاعیه‌های قابل مشاهده برای کاربر
     */
    public function feed()
    {
        $user = auth()->user();

        $announcements = Announcement::with(['author', 'klass'])
            ->visibleFor($user)
            ->get();

        // علامت‌گذاری به‌عنوان خوانده‌شده
        $ids = $announcements->pluck('id');
        if ($ids->isNotEmpty()) {
            $existing = AnnouncementRead::where('user_id', $user->id)
                ->whereIn('announcement_id', $ids)
                ->pluck('announcement_id')
                ->toArray();

            $new = $ids->diff($existing);
            foreach ($new as $id) {
                AnnouncementRead::create([
                    'announcement_id' => $id,
                    'user_id'         => $user->id,
                    'read_at'         => now(),
                ]);
            }
        }

        return view('announcements.feed', compact('announcements'));
    }
}
