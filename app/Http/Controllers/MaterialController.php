<?php

namespace App\Http\Controllers;

use App\Models\CourseMaterial;
use App\Models\Klass;
use App\Models\ScheduleSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /**
     * لیست کلاس‌هایی که کاربر می‌تونه منابعش رو ببینه
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isStudent()) {
            // دانش‌آموز مستقیم می‌ره به کلاس خودش
            if (!$user->klass_id) {
                return view('materials.no-class');
            }
            return redirect()->route('materials.class', $user->klass_id);
        }

        if ($user->isTeacher()) {
            // معلم فقط کلاس‌هایی که درس می‌ده
            $klassIds = ScheduleSlot::where('teacher_id', $user->id)
                ->pluck('klass_id')
                ->unique();

            $klasses = Klass::with('academicLevel')
                ->withCount(['students', 'materials'])
                ->whereIn('id', $klassIds)
                ->orderBy('academic_level_id')
                ->orderBy('name')
                ->get();

            return view('materials.teacher-index', compact('klasses'));
        }

        // مدیر/ادمین همه‌ی کلاس‌های مدرسه
        $schoolId = $user->isAdmin() ? null : $user->school_id;
        $klasses = Klass::with('academicLevel')
            ->withCount(['students', 'materials'])
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('academic_level_id')
            ->orderBy('name')
            ->get();

        return view('materials.teacher-index', compact('klasses'));
    }

    /**
     * نمایش منابع یک کلاس
     */
    public function show(Klass $klass)
    {
        $user = auth()->user();
        $this->authorizeAccess($user, $klass);

        // دسترسی آپلود فقط برای معلم کلاس یا مدیر/ادمین
        $canUpload = false;
        $subjects = [];

        if ($user->isTeacher()) {
            $subjects = ScheduleSlot::where('klass_id', $klass->id)
                ->where('teacher_id', $user->id)
                ->pluck('subject')
                ->unique()
                ->values()
                ->toArray();
            $canUpload = !empty($subjects);
        } elseif ($user->isManager() || $user->isAdmin()) {
            $subjects = ScheduleSlot::where('klass_id', $klass->id)
                ->pluck('subject')
                ->unique()
                ->values()
                ->toArray();
            $canUpload = true;
        }

        $materials = CourseMaterial::with('teacher')
            ->where('klass_id', $klass->id)
            ->orderBy('subject')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('subject');

        return view('materials.show', compact('klass', 'materials', 'canUpload', 'subjects'));
    }

    /**
     * آپلود فایل جدید
     */
    public function store(Request $request, Klass $klass)
    {
        $user = auth()->user();
        $this->authorizeUpload($user, $klass);

        $validated = $request->validate([
            'subject'     => 'required|string|max:255',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category'    => 'required|in:جزوه,تکلیف,ویدیو,سایر',
            'file'        => 'required|file|max:20480', // 20MB
        ], [
            'file.required' => 'فایل را انتخاب کنید',
            'file.max'      => 'حجم فایل نباید بیشتر از ۲۰ مگابایت باشد',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, CourseMaterial::$allowedExtensions)) {
            return back()->withErrors(['file' => 'فرمت فایل مجاز نیست. فرمت‌های مجاز: ' . implode(', ', CourseMaterial::$allowedExtensions)])->withInput();
        }

        // ذخیره فایل
        $folder = 'materials/' . $klass->id;
        $storedName = time() . '_' . uniqid() . '.' . $ext;
        $path = $file->storeAs($folder, $storedName, 'local');

        CourseMaterial::create([
            'klass_id'    => $klass->id,
            'teacher_id'  => $user->id,
            'subject'     => $validated['subject'],
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category'    => $validated['category'],
            'file_path'   => $path,
            'file_name'   => $file->getClientOriginalName(),
            'file_size'   => $file->getSize(),
            'file_type'   => $file->getMimeType() ?? 'application/octet-stream',
        ]);

        return redirect()->route('materials.class', $klass)->with('success', 'فایل با موفقیت آپلود شد');
    }

    /**
     * دانلود فایل
     */
    public function download(CourseMaterial $material)
    {
        $user = auth()->user();
        $this->authorizeAccess($user, $material->klass);

        $path = storage_path('app/' . $material->file_path);

        if (!file_exists($path)) {
            abort(404, 'فایل یافت نشد');
        }

        return response()->download($path, $material->file_name);
    }

    /**
     * حذف فایل
     */
    public function destroy(CourseMaterial $material)
    {
        $user = auth()->user();

        // فقط آپلودکننده یا مدیر/ادمین
        if (!$user->isAdmin() && !$user->isManager() && $material->teacher_id !== $user->id) {
            abort(403, 'فقط سازنده فایل یا مدیر می‌تواند آن را حذف کند');
        }

        // اگه معلمه، مطمئن شو کلاس مال خودشه
        if ($user->isTeacher()) {
            $this->authorizeUpload($user, $material->klass);
        }

        $klassId = $material->klass_id;

        // حذف فایل از storage
        if (Storage::disk('local')->exists($material->file_path)) {
            Storage::disk('local')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('materials.class', $klassId)->with('success', 'فایل حذف شد');
    }

    /**
     * بررسی دسترسی به کلاس برای دیدن
     */
    protected function authorizeAccess($user, Klass $klass): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isManager() && $user->school_id === $klass->school_id) {
            return;
        }

        if ($user->isStudent() && $user->klass_id === $klass->id) {
            return;
        }

        if ($user->isTeacher()) {
            $teaches = ScheduleSlot::where('klass_id', $klass->id)
                ->where('teacher_id', $user->id)
                ->exists();
            if ($teaches) return;
        }

        abort(403, 'شما به این کلاس دسترسی ندارید');
    }

    /**
     * بررسی دسترسی آپلود
     */
    protected function authorizeUpload($user, Klass $klass): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isManager() && $user->school_id === $klass->school_id) {
            return;
        }

        if ($user->isTeacher()) {
            $teaches = ScheduleSlot::where('klass_id', $klass->id)
                ->where('teacher_id', $user->id)
                ->exists();
            if ($teaches) return;
        }

        abort(403, 'شما اجازه‌ی آپلود در این کلاس را ندارید');
    }
}
