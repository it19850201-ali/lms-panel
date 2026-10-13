@extends('layouts.app')

@section('title', 'منابع کلاس‌ها')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">منابع کلاس‌ها</h2>
        <p class="text-sm text-gray-500">مدیریت فایل‌های درسی و جزوات</p>
    </div>

    @if ($klasses->isEmpty())
        <div class="bg-yellow-50 text-yellow-700 rounded-2xl p-6 text-sm">
            هنوز هیچ کلاسی به شما اختصاص داده نشده است.
        </div>
    @else
        <div class="grid gap-3">
            @foreach ($klasses as $klass)
                <a href="{{ route('materials.class', $klass) }}"
                   class="bg-white rounded-2xl border p-5 hover:border-indigo-400 hover:shadow-sm transition flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg">{{ $klass->academicLevel->name }} — کلاس {{ $klass->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $klass->students_count }} دانش‌آموز
                            @if ($klass->materials_count > 0)
                                · {{ $klass->materials_count }} فایل
                            @endif
                        </p>
                    </div>
                    <span class="text-indigo-600 text-sm font-medium">مشاهده منابع ←</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
