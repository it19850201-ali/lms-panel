@extends('layouts.app')

@section('title', 'ساخت کلاس فوق‌برنامه')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-2">کلاس / دوره‌ی فوق‌برنامه</h2>
        <p class="text-gray-500 text-sm">بدون محدودیت زمان‌بندی — عنوان دلخواه بذارید و در صورت نیاز دانش‌آموز اضافه کنید</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('courses.create.extra.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border p-8 space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">عنوان دوره / کارگاه</label>
            <input type="text" name="title" value="{{ old('title') }}"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="مثلاً: کارگاه نقاشی، دوره‌ی تابستانی رباتیک">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">توضیحات (اختیاری)</label>
            <textarea name="description" rows="3"
                      class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      placeholder="توضیح کوتاهی درباره‌ی این دوره">{{ old('description') }}</textarea>
        </div>
	<div class="grid grid-cols-2 gap-4">
	    <div>
	        <label class="block text-sm font-medium mb-2">تاریخ شروع (اختیاری)</label>
	        <input type="text" name="starts_at" id="starts_at" value="{{ old('starts_at') }}"
	               placeholder="۱۴۰۵/۰۷/۰۴ ۱۰:۳۰" dir="ltr" autocomplete="off"
	               class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
	        <p class="text-xs text-gray-400 mt-1">اگه خالی بذاری، از همین حالا فعاله</p>
	    </div>

	    <div>
	        <label class="block text-sm font-medium mb-2">تاریخ پایان (اختیاری)</label>
	        <input type="text" name="ends_at" id="ends_at" value="{{ old('ends_at') }}"
	               placeholder="۱۴۰۵/۰۷/۱۵ ۱۲:۰۰" dir="ltr" autocomplete="off"
	               class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
	        <p class="text-xs text-gray-400 mt-1">اگه خالی بذاری، هیچ‌وقت بسته نمی‌شه</p>
	    </div>
	</div>


        <div class="flex items-center gap-2">
            <input type="checkbox" name="open_enrollment" id="open_enrollment" value="1"
                   class="w-4 h-4 rounded border-gray-300 text-indigo-600">
            <label for="open_enrollment" class="text-sm">ثبت‌نام آزاد (هرکسی با لینک عضو بشه)</label>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">افزودن دانش‌آموز/دانشجو (اختیاری)</label>

            <div class="mb-4 bg-gray-50 border border-dashed rounded-lg p-4">
                <label class="block text-sm font-medium mb-2">یا آپلود فایل اکسل</label>
                <input type="file" name="excel_file" accept=".xlsx,.xls,.csv"
                       class="w-full text-sm">
                <p class="text-xs text-gray-400 mt-2">
                    ستون اول: نام و نام‌خانوادگی — ستون دوم: ایمیل — ردیف اول (هدر) نادیده گرفته می‌شه
                </p>
            </div>

            <div id="students-container" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="students[0][name]" placeholder="نام و نام‌خانوادگی"
                           class="border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <input type="email" name="students[0][email]" placeholder="ایمیل"
                           class="border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <button type="button" id="add-student" class="text-indigo-600 text-sm font-medium mt-3">
                + افزودن دانش‌آموز دیگر
            </button>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
            ساخت کلاس ✓
        </button>
    </form>
</div>

<script>
let studentIndex = 1;
document.getElementById('add-student').addEventListener('click', function () {
    const container = document.getElementById('students-container');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-2 gap-3';
    row.innerHTML = `
        <input type="text" name="students[${studentIndex}][name]" placeholder="نام و نام‌خانوادگی"
               class="border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <input type="email" name="students[${studentIndex}][email]" placeholder="ایمیل"
               class="border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
    `;
    container.appendChild(row);
    studentIndex++;
});
</script>
@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
<script>
    (function() {
        var tryInit = function() {
            if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.pDatepicker === 'function') {
                window.jQuery('#starts_at, #ends_at').pDatepicker({
                    format: 'YYYY/MM/DD HH:mm',
                    initialValue: false,
                    autoClose: true,
                    timePicker: {
                        enabled: true,
                        meridiem: { enabled: false }
                    },
                    persianDigit: true,
                    observer: true
                });
            }
        };

        // اگه DOM هنوز آماده نشده، منتظر بمون
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', tryInit);
        } else {
            tryInit();
        }
    })();
</script>
@endpush
@endsection
