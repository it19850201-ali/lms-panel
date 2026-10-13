@extends('layouts.app')

@section('title', 'مرحله ۲ - افزودن دانش‌آموز')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
            <span>۱. اطلاعات کلاس</span>
            <span>←</span>
            <span class="text-indigo-600 font-semibold">۲. افزودن دانش‌آموز</span>
            <span>←</span>
            <span>۳. زمان‌بندی</span>
        </div>
        <h2 class="text-2xl font-bold">افزودن دانش‌آموز / دانشجو</h2>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('courses.create.regular.step2.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border p-8" enctype="multipart/form-data">
        @csrf

	<div class="mb-4 bg-gray-50 border border-dashed rounded-lg p-4">
	    <label class="block text-sm font-medium mb-2">یا آپلود فایل اکسل</label>
	    <input type="file" name="excel_file" accept=".xlsx,.xls,.csv"
	           class="w-full text-sm">
	    <p class="text-xs text-gray-400 mt-2">
        ستون اول: نام و نام‌خانوادگی — ستون دوم: ایمیل — ردیف اول (هدر) نادیده گرفته می‌شه
	    </p>
	</div>
        <div id="students-container" class="space-y-4 mb-6">
            <div class="grid grid-cols-2 gap-3 student-row">
                <input type="text" name="students[0][name]" placeholder="نام و نام‌خانوادگی"
                       class="border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <input type="email" name="students[0][email]" placeholder="ایمیل"
                       class="border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <button type="button" id="add-student"
                class="text-indigo-600 text-sm font-medium mb-6">
            + افزودن دانش‌آموز دیگر
        </button>

        <button type="submit"
                class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
            مرحله‌ی بعد →
        </button>
    </form>
</div>

<script>
let studentIndex = 1;
document.getElementById('add-student').addEventListener('click', function () {
    const container = document.getElementById('students-container');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-2 gap-3 student-row mt-4';
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
@endsection
