@extends('layouts.app')

@section('title', 'مرحله ۳ - زمان‌بندی')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
            <span>۱. اطلاعات کلاس</span>
            <span>←</span>
            <span>۲. افزودن دانش‌آموز</span>
            <span>←</span>
            <span class="text-indigo-600 font-semibold">۳. زمان‌بندی</span>
        </div>
        <h2 class="text-2xl font-bold">زمان‌بندی کلاس</h2>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('courses.create.regular.step3.store') }}" class="bg-white rounded-2xl shadow-sm border p-8 space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">روز هفته</label>
            <select name="day_of_week" class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="شنبه">شنبه</option>
                <option value="یکشنبه">یکشنبه</option>
                <option value="دوشنبه">دوشنبه</option>
                <option value="سه‌شنبه">سه‌شنبه</option>
                <option value="چهارشنبه">چهارشنبه</option>
                <option value="پنجشنبه">پنجشنبه</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">ساعت</label>
            <input type="time" name="time" value="10:00"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <button type="submit"
                class="w-full bg-green-600 text-white rounded-lg py-3 font-semibold hover:bg-green-700 transition">
            ساخت کلاس ✓
        </button>
    </form>
</div>
@endsection
