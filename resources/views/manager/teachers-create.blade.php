@extends('layouts.app')

@section('title', $teacherLabel . ' جدید')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">افزودن {{ $teacherLabel }} جدید</h2>
        <a href="{{ route('manager.teachers') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به لیست {{ $teacherLabel }}ان</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('manager.teachers.store') }}"
          class="bg-white rounded-2xl shadow-sm border p-6 md:p-8 space-y-5">
        @csrf

        <div class="bg-blue-50 text-blue-800 rounded-xl p-3 text-xs leading-6">
            ℹ️ {{ $teacherLabel }}ی که اینجا می‌سازی، <strong>بلافاصله فعال</strong> می‌شه و می‌تونه وارد سامانه بشه. رمز اولیه = کد ملی (اگه رمز نذاری).
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">نام و نام‌خانوادگی</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">کد ملی (نام کاربری)</label>
            <input type="text" name="code_melli" value="{{ old('code_melli') }}" maxlength="10" dir="ltr" required
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">شماره موبایل</label>
            <input type="text" name="mobile" value="{{ old('mobile') }}" maxlength="11" dir="ltr" required
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="border-t pt-5">
            <label class="block text-sm font-medium mb-2">
                رمز عبور <span class="text-xs text-gray-400">(اختیاری)</span>
            </label>
            <input type="password" name="password" dir="ltr" minlength="6"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <p class="text-xs text-gray-400 mt-1">اگه خالی بذاری، کد ملی به‌عنوان رمز استفاده می‌شه</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">تکرار رمز عبور</label>
            <input type="password" name="password_confirmation" dir="ltr" minlength="6"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit"
                    class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
                ساخت {{ $teacherLabel }}
            </button>
            <a href="{{ route('manager.teachers') }}"
               class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200 text-center">
                انصراف
            </a>
        </div>
    </form>
</div>
@endsection
