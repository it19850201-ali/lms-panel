@extends('layouts.app')

@section('title', 'مرحله ۱ - اطلاعات کلاس')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
            <span class="text-indigo-600 font-semibold">۱. اطلاعات کلاس</span>
            <span>←</span>
            <span>۲. افزودن دانش‌آموز</span>
            <span>←</span>
            <span>۳. زمان‌بندی</span>
        </div>
        <h2 class="text-2xl font-bold">اطلاعات پایه‌ی کلاس</h2>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('courses.create.regular.step1.store') }}" class="bg-white rounded-2xl shadow-sm border p-8 space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">نام کلاس / درس</label>
            <input type="text" name="course_name" value="{{ old('course_name', $data['course_name'] ?? '') }}"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="مثلاً: ریاضی پایه هفتم">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">پایه / مقطع</label>
            <input type="text" name="grade_level" value="{{ old('grade_level', $data['grade_level'] ?? '') }}"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="مثلاً: پایه هفتم">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
            مرحله‌ی بعد →
        </button>
    </form>
</div>
@endsection
