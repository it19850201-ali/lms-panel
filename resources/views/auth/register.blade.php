@extends('layouts.app')

@section('title', 'ثبت‌نام معلم')

@section('content')
<div class="max-w-md mx-auto">
    <h2 class="text-2xl font-bold mb-4 text-center">ثبت‌نام معلم</h2>
    <p class="text-sm text-gray-500 text-center mb-8">
        پس از ثبت‌نام، حساب شما توسط مدیر مدرسه بررسی و تایید می‌شود
    </p>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    @if ($schools->isEmpty())
        <div class="bg-yellow-50 text-yellow-700 rounded-lg p-4 mb-6 text-sm">
            در حال حاضر هیچ مدرسه‌ای در سیستم ثبت نشده است.
        </div>
    @else
        <form method="POST" action="{{ route('register') }}" class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-2">نام و نام‌خانوادگی</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">کد ملی (نام کاربری)</label>
                <input type="text" name="code_melli" value="{{ old('code_melli') }}" maxlength="10" dir="ltr" required
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">با این کد وارد سامانه می‌شوید</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">شماره موبایل</label>
                <input type="text" name="mobile" value="{{ old('mobile') }}" maxlength="11"
                       placeholder="09xxxxxxxxx" dir="ltr" required
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">برای اطلاع‌رسانی و پیامک</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">رمز عبور</label>
                <input type="password" name="password" dir="ltr" required minlength="6"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">حداقل ۶ کاراکتر</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">تکرار رمز عبور</label>
                <input type="password" name="password_confirmation" dir="ltr" required minlength="6"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">مدرسه / دانشکده</label>
                <select name="school_id" required
                        class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">— انتخاب کنید —</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                            @if ($school->city)
                                ({{ $school->city }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
                ثبت‌نام
            </button>

            <p class="text-center text-sm text-gray-500">
                حساب دارید؟ <a href="{{ route('login') }}" class="text-indigo-600 font-medium">وارد شوید</a>
            </p>
        </form>
    @endif
</div>
@endsection
