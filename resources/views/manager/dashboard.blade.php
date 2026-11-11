@extends('layouts.app')

@section('title', 'پنل مدیریت')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">{{ $isUni ? 'پنل دانشگاه' : 'پنل مدرسه' }}</h2>
        <p class="text-sm text-gray-500">
            {{ $orgLabel }}: <strong>{{ auth()->user()->school?->name ?? 'همه مراکز' }}</strong>
        </p>
    </div>

    {{-- آمار --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-8">
        <a href="{{ route('manager.teachers') }}" class="bg-white rounded-xl border p-4 hover:border-yellow-300 transition">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-yellow-50 flex items-center justify-center text-sm">⏳</div>
                <p class="text-xs text-gray-500">در انتظار</p>
            </div>
            <p class="text-2xl font-bold text-yellow-600">{{ $pendingCount }}</p>
        </a>

        <a href="{{ route('manager.teachers') }}" class="bg-white rounded-xl border p-4 hover:border-green-300 transition">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-green-50 flex items-center justify-center text-sm">✓</div>
                <p class="text-xs text-gray-500">{{ $teacherLabel }}ان</p>
            </div>
            <p class="text-2xl font-bold text-green-600">{{ $approvedCount }}</p>
        </a>

        <div class="bg-white rounded-xl border p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center text-sm">👥</div>
                <p class="text-xs text-gray-500">{{ $studentLabel }}ان</p>
            </div>
            <p class="text-2xl font-bold text-indigo-600">{{ $studentsCount }}</p>
        </div>

        <a href="{{ route('manager.levels') }}" class="bg-white rounded-xl border p-4 hover:border-purple-300 transition">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center text-sm">📚</div>
                <p class="text-xs text-gray-500">{{ $levelLabel }}‌ها</p>
            </div>
            <p class="text-2xl font-bold text-purple-600">{{ $levelsCount }}</p>
        </a>

        <a href="{{ route('manager.klasses') }}" class="bg-white rounded-xl border p-4 hover:border-pink-300 transition">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-pink-50 flex items-center justify-center text-sm">🏫</div>
                <p class="text-xs text-gray-500">{{ $classLabel }}‌ها</p>
            </div>
            <p class="text-2xl font-bold text-pink-600">{{ $klassesCount }}</p>
        </a>
    </div>

    {{-- دسترسی سریع --}}
    <h3 class="text-sm font-bold text-gray-700 mb-3">دسترسی سریع</h3>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <a href="{{ route('manager.terms') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-lg">📆</div>
            <div>
                <p class="font-bold text-sm">ترم‌های تحصیلی</p>
                <p class="text-xs text-gray-500 mt-0.5">مدیریت ترم‌ها</p>
            </div>
        </a>

        <a href="{{ route('manager.teachers') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-lg">👨‍🏫</div>
            <div>
                <p class="font-bold text-sm">مدیریت {{ $teacherLabel }}ان</p>
                <p class="text-xs text-gray-500 mt-0.5">تایید و رد درخواست‌ها</p>
            </div>
        </a>

        <a href="{{ route('manager.levels') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-lg">📚</div>
            <div>
                <p class="font-bold text-sm">{{ $levelLabel }}‌ها / گروه‌ها</p>
                <p class="text-xs text-gray-500 mt-0.5">مدیریت {{ $levelLabel }}‌های تحصیلی</p>
            </div>
        </a>

        <a href="{{ route('manager.klasses') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-pink-50 flex items-center justify-center text-lg">🏫</div>
            <div>
                <p class="font-bold text-sm">{{ $classLabel }}‌ها</p>
                <p class="text-xs text-gray-500 mt-0.5">مدیریت {{ $classLabel }}‌ها</p>
            </div>
        </a>

        <a href="{{ route('manager.students.import') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-lg">📥</div>
            <div>
                <p class="font-bold text-sm">ایمپورت {{ $studentLabel }}ان</p>
                <p class="text-xs text-gray-500 mt-0.5">ثبت گروهی از اکسل</p>
            </div>
        </a>

        {{-- زنگ‌های مدرسه (فقط برای مدارس) --}}
        @if (!$isUni)
            <a href="{{ route('manager.bells') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-lg">🔔</div>
                <div>
                    <p class="font-bold text-sm">زنگ‌های مدرسه</p>
                    <p class="text-xs text-gray-500 mt-0.5">تعریف ساعت زنگ‌ها</p>
                </div>
            </a>
        @endif

        <a href="{{ route('manager.schedule') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-lg">📅</div>
            <div>
                <p class="font-bold text-sm">برنامه هفتگی</p>
                <p class="text-xs text-gray-500 mt-0.5">مشاهده و ویرایش برنامه</p>
            </div>
        </a>

        <a href="{{ route('manager.attendance') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-lg">📊</div>
            <div>
                <p class="font-bold text-sm">گزارش حضور و غیاب</p>
                <p class="text-xs text-gray-500 mt-0.5">مشاهده حضور در جلسات</p>
            </div>
        </a>

        <a href="{{ route('manager.attendance.dashboard') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center text-lg">📈</div>
            <div>
                <p class="font-bold text-sm">داشبورد تحلیلی</p>
                <p class="text-xs text-gray-500 mt-0.5">آمار و نمودارها</p>
            </div>
        </a>

        <a href="{{ route('announcements.index') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-lg">📢</div>
            <div>
                <p class="font-bold text-sm">اطلاعیه‌ها</p>
                <p class="text-xs text-gray-500 mt-0.5">اطلاع‌رسانی به کاربران</p>
            </div>
        </a>

        <a href="{{ route('manager.extra-class.index') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center text-lg">⚡</div>
            <div>
                <p class="font-bold text-sm">{{ $isUni ? 'کلاس‌های درسی / کارگاه‌ها' : 'کلاس فوق‌برنامه' }}</p>
                <p class="text-xs text-gray-500 mt-0.5">مشاهده، ساخت و مدیریت</p>
            </div>
        </a>
    </div>
</div>
@endsection
