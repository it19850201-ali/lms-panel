@extends('layouts.app')

@section('title', 'در انتظار تایید')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border p-8 text-center">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-yellow-100 flex items-center justify-center">
            <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h2 class="text-2xl font-bold mb-3">درخواست شما ثبت شد</h2>
        <p class="text-gray-600 leading-7 mb-6">
            حساب کاربری شما با موفقیت ایجاد شد.<br>
            لطفاً منتظر تایید مدیر مدرسه‌ی انتخابی باشید.<br>
            پس از تایید می‌توانید با موبایل و کد ملی وارد شوید.
        </p>

        <a href="{{ route('login') }}"
           class="inline-block bg-indigo-600 text-white rounded-lg px-6 py-3 font-semibold hover:bg-indigo-700 transition">
            بازگشت به صفحه‌ی ورود
        </a>
    </div>
</div>
@endsection
