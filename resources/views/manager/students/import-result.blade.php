@extends('layouts.app')

@section('title', 'نتیجه ایمپورت')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-1">نتیجه ایمپورت</h2>
        <p class="text-sm text-gray-500">
            کلاس مقصد: <strong>{{ $klass->academicLevel->name }} — {{ $klass->name }}</strong>
        </p>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-2xl border p-6">
            <p class="text-sm text-gray-500 mb-1">دانش‌آموز جدید</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['students_created'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-6">
            <p class="text-sm text-gray-500 mb-1">دانش‌آموز آپدیت‌شده</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['students_updated'] }}</p>
        </div>
    </div>

    @if (!empty($stats['errors']))
        <div class="bg-white rounded-2xl border p-6 mb-6">
            <h3 class="font-bold text-red-600 mb-3">
                هشدارها ({{ count($stats['errors']) }} مورد)
            </h3>
            <ul class="space-y-2 text-sm text-gray-700 leading-6">
                @foreach ($stats['errors'] as $error)
                    <li class="flex gap-2">
                        <span class="text-red-400">●</span>
                        <span>{{ $error }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex gap-3">
        <a href="{{ route('manager.students.import') }}"
           class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold text-center hover:bg-indigo-700">
            ایمپورت فایل دیگر
        </a>
        <a href="{{ route('manager.dashboard') }}"
           class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200">
            بازگشت
        </a>
    </div>
</div>
@endsection
