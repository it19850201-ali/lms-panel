@extends('layouts.app')

@section('title', 'ورود')

@section('content')
<div class="max-w-md mx-auto">
    <h2 class="text-2xl font-bold mb-8 text-center">ورود به سامانه</h2>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">کد ملی</label>
            <input type="text" name="code_melli" value="{{ old('code_melli') }}" maxlength="10" dir="ltr" required autofocus
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">رمز عبور</label>
            <input type="password" name="password" dir="ltr" required
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
            ورود
        </button>

        <p class="text-center text-sm text-gray-500">
            حساب ندارید؟ <a href="{{ route('register') }}" class="text-indigo-600 font-medium">ثبت‌نام کنید</a>
        </p>
    </form>
</div>
@endsection
