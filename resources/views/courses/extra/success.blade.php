@extends('layouts.app')

@section('title', 'کلاس فوق‌برنامه ساخته شد')

@section('content')
<div class="max-w-lg mx-auto text-center bg-white rounded-2xl shadow-sm border p-10">
    <div class="text-5xl mb-4">✅</div>
    <h2 class="text-2xl font-bold mb-2">کلاس با موفقیت ساخته شد!</h2>
    <p class="text-gray-500 mb-6">
        «{{ $courseName }}»
        @if($studentCount > 0)
            با {{ $studentCount }} دانش‌آموز/دانشجو
        @endif
        ساخته شد.
    </p>

    <a href="{{ $joinUrl }}" target="_blank"
       class="inline-block bg-green-600 text-white rounded-lg px-6 py-3 font-semibold hover:bg-green-700 transition mb-4">
        🎥 ورود به کلاس
    </a>

    <div>
        <a href="{{ route('courses.create.select') }}"
           class="inline-block text-indigo-600 text-sm font-medium mt-2">
            ساخت کلاس جدید
        </a>
    </div>
</div>
@endsection
