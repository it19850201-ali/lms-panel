<?php

namespace App\Http\Controllers;

use App\Exports\StudentTemplateExport;
use App\Models\AcademicLevel;
use App\Models\Klass;
use App\Services\StudentImporter;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $schoolId = $user->isAdmin() ? null : $user->school_id;

        $levels = AcademicLevel::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('manager.students.import', compact('levels'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'academic_level_id' => 'required|exists:academic_levels,id',
            'klass_id'          => 'required|exists:klasses,id',
            'file'              => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'academic_level_id.required' => 'پایه را انتخاب کنید',
            'klass_id.required'          => 'کلاس را انتخاب کنید',
            'file.required'              => 'فایل را انتخاب کنید',
            'file.mimes'                 => 'فرمت فایل باید xlsx، xls یا csv باشد',
            'file.max'                   => 'حجم فایل نباید بیشتر از ۵ مگابایت باشد',
        ]);

        $user = auth()->user();

        if (!$user->school_id) {
            return back()->withErrors(['file' => 'حساب شما به مدرسه‌ای متصل نیست']);
        }

        $klass = Klass::where('id', $request->klass_id)
            ->where('school_id', $user->school_id)
            ->first();

        if (!$klass) {
            return back()->withErrors(['klass_id' => 'کلاس انتخاب‌شده معتبر نیست']);
        }

        try {
            $importer = new StudentImporter($user->school_id, $klass->id);
            $stats = $importer->import($request->file('file'));

            return view('manager.students.import-result', [
                'stats' => $stats,
                'klass' => $klass,
            ]);

        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'خطا در پردازش فایل: ' . $e->getMessage()]);
        }
    }

    public function klassesByLevel(Request $request)
    {
        $request->validate([
            'academic_level_id' => 'required|exists:academic_levels,id',
        ]);

        $user = auth()->user();
        $schoolId = $user->isAdmin() ? null : $user->school_id;

        $klasses = Klass::where('academic_level_id', $request->academic_level_id)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($klasses);
    }

    public function template()
    {
        return Excel::download(new StudentTemplateExport(), 'student-import-template.xlsx');
    }
}
