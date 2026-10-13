<?php

namespace App\Http\Controllers;

use App\Exports\ScheduleTemplateExport;
use App\Services\ScheduleImporter;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleImportController extends Controller
{
    public function show()
    {
        return view('manager.schedule.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'فایل را انتخاب کنید',
            'file.mimes'    => 'فرمت فایل باید xlsx، xls یا csv باشد',
            'file.max'      => 'حجم فایل نباید بیشتر از ۱۰ مگابایت باشد',
        ]);

        $user = auth()->user();

        if (!$user->school_id) {
            return back()->withErrors(['file' => 'حساب شما به مدرسه‌ای متصل نیست']);
        }

        try {
            $importer = new ScheduleImporter($user->school_id);
            $stats = $importer->import($request->file('file'));

            return view('manager.schedule.import-result', compact('stats'));

        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'خطا در پردازش فایل: ' . $e->getMessage()]);
        }
    }

    public function template()
    {
        return Excel::download(new ScheduleTemplateExport(), 'schedule-import-template.xlsx');
    }
}
