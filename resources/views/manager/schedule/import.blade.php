@extends('layouts.app')

@section('title', 'آپلود اکسل برنامه هفتگی')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-1">آپلود اکسل برنامه هفتگی</h2>
        <a href="{{ route('manager.schedule') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- راهنما --}}
    <div class="bg-blue-50 text-blue-800 rounded-2xl p-6 mb-6 text-sm leading-7">
        <h3 class="font-bold mb-3">📋 راهنما</h3>
        <ol class="list-decimal list-inside space-y-1">
            <li>قالب اکسل رو دانلود کنید</li>
            <li>ستون‌ها: <strong>پایه، کلاس، روز، زنگ، نام معلم، موبایل معلم، درس، اتاق</strong></li>
            <li>روزها: شنبه، یکشنبه، دوشنبه، سه‌شنبه، چهارشنبه، پنجشنبه، جمعه</li>
            <li><strong>زنگ‌ها باید قبلاً تعریف شده باشن</strong> (<a href="{{ route('manager.bells') }}" class="underline">تنظیمات زنگ‌ها</a>)</li>
            <li>معلم‌ها اگه توی سیستم نباشن، خودکار با موبایل ساخته می‌شن</li>
            <li>پایه و کلاس اگه نباشن، خودکار ساخته می‌شن</li>
            <li>برای هر slot جدید، BBB meeting خودکار ساخته می‌شه</li>
            <li>اگه slot تکراری (همون کلاس/روز/زنگ) آپلود بشه، فقط آپدیت می‌شه</li>
        </ol>
    </div>

    {{-- دانلود قالب --}}
    <div class="bg-white rounded-2xl border p-6 mb-6 flex justify-between items-center">
        <div>
            <h3 class="font-bold">قالب اکسل برنامه هفتگی</h3>
            <p class="text-sm text-gray-500 mt-1">۳ نمونه + ۲۰۰ ردیف خالی</p>
        </div>
        <a href="{{ route('manager.schedule.import.template') }}"
           class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200">
            دانلود قالب
        </a>
    </div>

    {{-- فرم آپلود --}}
    <form method="POST" action="{{ route('manager.schedule.import.store') }}"
          enctype="multipart/form-data"
          class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">فایل اکسل</label>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <p class="text-xs text-gray-400 mt-1">حداکثر ۱۰ مگابایت</p>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
            شروع پردازش
        </button>
    </form>
</div>
@endsection
