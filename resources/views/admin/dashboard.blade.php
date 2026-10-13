@extends('layouts.app')

@section('title', 'پنل مدیر کل')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">پنل مدیر کل</h2>
        <p class="text-sm text-gray-500">مدیریت کل سیستم</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 mb-1">مدارس</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $stats['schools'] }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['active_schools'] }} فعال</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 mb-1">مدیران</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['managers'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 mb-1">معلمان</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['teachers'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 mb-1">دانش‌آموزان</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['students'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 mb-1">کلاس‌ها</p>
            <p class="text-2xl font-bold text-pink-600">{{ $stats['klasses'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 mb-1">جلسات هفتگی</p>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['slots'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 mb-1">حضور امروز</p>
            <p class="text-2xl font-bold text-teal-600">{{ $stats['attendance_today'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 mb-1">در انتظار</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
    </div>

    <h3 class="text-sm font-bold text-gray-700 mb-3">دسترسی سریع</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
        <a href="{{ route('admin.schools') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-lg">🏫</div>
            <div>
                <p class="font-bold text-sm">مدارس</p>
                <p class="text-xs text-gray-500">{{ $stats['schools'] }} مدرسه</p>
            </div>
        </a>
        <a href="{{ route('admin.managers') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-lg">👔</div>
            <div>
                <p class="font-bold text-sm">مدیران</p>
                <p class="text-xs text-gray-500">{{ $stats['managers'] }} مدیر</p>
            </div>
        </a>
        <a href="{{ route('admin.users') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-lg">👥</div>
            <div>
                <p class="font-bold text-sm">کاربران</p>
                <p class="text-xs text-gray-500">مدیریت و ریست رمز</p>
            </div>
        </a>
        <a href="{{ route('manager.dashboard') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-lg">⚙️</div>
            <div>
                <p class="font-bold text-sm">پنل مدرسه</p>
                <p class="text-xs text-gray-500">مدیریت مدرسه‌ها</p>
            </div>
        </a>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">آخرین مدارس</h3>
                <a href="{{ route('admin.schools') }}" class="text-xs text-indigo-600">همه →</a>
            </div>
            @if ($schools->isEmpty())
                <p class="text-sm text-gray-500 text-center py-6">مدرسه‌ای ثبت نشده</p>
            @else
                <div class="space-y-3">
                    @foreach ($schools as $school)
                        <div class="flex justify-between items-center py-2 border-b last:border-b-0">
                            <div>
                                <p class="font-bold text-sm">{{ $school->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $school->teachers_count }} معلم · {{ $school->students_count }} دانش‌آموز
                                </p>
                            </div>
                            <span class="text-xs {{ $school->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} px-2 py-1 rounded-full">
                                {{ $school->is_active ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl border p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">آخرین کاربران</h3>
                <a href="{{ route('admin.users') }}" class="text-xs text-indigo-600">همه →</a>
            </div>
            <div class="space-y-3">
                @foreach ($recentUsers as $u)
                    <div class="flex justify-between items-center py-2 border-b last:border-b-0">
                        <div>
                            <p class="font-bold text-sm">{{ $u->name }}</p>
                            <p class="text-xs text-gray-500" dir="ltr">{{ $u->mobile }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full
                            @if ($u->role === 'admin') bg-red-100 text-red-700
                            @elseif ($u->role === 'manager') bg-purple-100 text-purple-700
                            @elseif ($u->role === 'teacher') bg-green-100 text-green-700
                            @else bg-blue-100 text-blue-700
                            @endif">
                            {{ $u->role }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
