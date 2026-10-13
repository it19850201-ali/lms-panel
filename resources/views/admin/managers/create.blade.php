@extends('layouts.app')

@section('title', 'مدیر جدید')

@section('content')
<div class="max-w-md mx-auto">
    <h2 class="text-2xl font-bold mb-8">ساخت مدیر مدرسه</h2>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    @if ($schools->isEmpty())
        <div class="bg-yellow-50 text-yellow-700 rounded-lg p-4 mb-6 text-sm">
            اول باید یه مدرسه بسازی.
        </div>
    @else
        <form method="POST" action="{{ route('admin.managers.store') }}"
              class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-2">نام و نام‌خانوادگی</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">شماره موبایل (نام کاربری)</label>
                <input type="text" name="mobile" value="{{ old('mobile') }}" maxlength="11" dir="ltr"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">کد ملی (رمز عبور)</label>
                <input type="text" name="code_melli" maxlength="10" dir="ltr"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">مدرسه</label>
                <select name="school_id"
                        class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— انتخاب کنید —</option>
                    @foreach ($schools as $s)
                        <option value="{{ $s->id }}" {{ old('school_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
                ساخت مدیر
            </button>
        </form>
    @endif
</div>
@endsection
