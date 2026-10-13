@extends('layouts.app')

@section('title', 'پروفایل من')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">پروفایل من</h2>
        <p class="text-sm text-gray-500">مدیریت اطلاعات شخصی</p>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif

    @if (auth()->user()->shouldChangePassword())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-2xl p-4 mb-6 text-sm">
            <p class="font-bold mb-1">⚠️ رمز عبور شما ناامن است</p>
            <p>رمز فعلی شما همان کد ملی است. لطفاً از بخش «تغییر رمز عبور» در پایین، رمز جدیدی انتخاب کنید.</p>
        </div>
    @endif

    {{-- نمایش اطلاعات --}}
    <div class="bg-white rounded-2xl border p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-500 mb-1">نقش</p>
                <p class="font-bold">
                    @if (auth()->user()->isAdmin()) مدیر کل
                    @elseif (auth()->user()->isManager()) مدیر مدرسه
                    @elseif (auth()->user()->isTeacher()) معلم
                    @else دانش‌آموز
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">مدرسه</p>
                <p class="font-bold">{{ auth()->user()->school?->name ?? '—' }}</p>
            </div>
            @if (auth()->user()->klass)
                <div>
                    <p class="text-xs text-gray-500 mb-1">کلاس</p>
                    <p class="font-bold">
                        {{ auth()->user()->klass->academicLevel->name }} — {{ auth()->user()->klass->name }}
                    </p>
                </div>
            @endif
            <div>
                <p class="text-xs text-gray-500 mb-1">وضعیت</p>
                <p class="font-bold">
                    @if (auth()->user()->status === 'approved') <span class="text-green-600">فعال</span>
                    @elseif (auth()->user()->status === 'pending') <span class="text-yellow-600">در انتظار</span>
                    @else <span class="text-red-600">رد‌شده</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- فرم ویرایش اطلاعات --}}
    <div class="bg-white rounded-2xl border p-6 mb-6">
        <h3 class="font-bold text-lg mb-4">اطلاعات شخصی</h3>

        @if ($errors->has('name') || $errors->has('mobile'))
            <div class="bg-red-50 text-red-600 rounded-lg p-3 mb-4 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-2">نام و نام خانوادگی</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">
                        کد ملی (نام کاربری)
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full mr-2">غیرقابل تغییر</span>
                    </label>
                    <input type="text" value="{{ $user->code_melli }}" disabled readonly dir="ltr"
                           class="w-full border rounded-lg px-4 py-3 bg-gray-100 text-gray-500 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">شماره موبایل</label>
                    <input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}"
                           maxlength="11" dir="ltr" required
                           class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">برای اطلاع‌رسانی و پیامک</p>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                        class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700">
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>

    {{-- فرم تغییر رمز --}}
    <div class="bg-white rounded-2xl border p-6" id="change-password">
        <h3 class="font-bold text-lg mb-4">تغییر رمز عبور</h3>

        @if ($errors->has('current_password') || $errors->has('password'))
            <div class="bg-red-50 text-red-600 rounded-lg p-3 mb-4 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-2">رمز فعلی</label>
                <input type="password" name="current_password" required dir="ltr"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">رمز جدید</label>
                    <input type="password" name="password" required dir="ltr" minlength="6"
                           class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">حداقل ۶ کاراکتر</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">تکرار رمز جدید</label>
                    <input type="password" name="password_confirmation" required dir="ltr" minlength="6"
                           class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                        class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700">
                    تغییر رمز
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
