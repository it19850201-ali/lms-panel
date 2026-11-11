@extends('layouts.app')

@section('title', 'داشبورد حضور')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-1">داشبورد حضور و غیاب</h2>
            <a href="{{ route('manager.attendance') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← لیست کامل</a>
        </div>
        <a href="{{ route('manager.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">بازگشت به پنل</a>
    </div>

    <div class="bg-white rounded-2xl border p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">بازه</label>
                <select name="range" onchange="this.form.submit()" class="border rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="today" {{ request('range', 'today') === 'today' ? 'selected' : '' }}>امروز</option>
                    <option value="week" {{ request('range') === 'week' ? 'selected' : '' }}>۷ روز اخیر</option>
                    <option value="month" {{ request('range') === 'month' ? 'selected' : '' }}>۳۰ روز اخیر</option>
                    <option value="custom" {{ request('range') === 'custom' ? 'selected' : '' }}>دلخواه</option>
                </select>
            </div>
            @if (request('range') === 'custom')
                <div>
                    <label class="block text-xs text-gray-500 mb-1">از</label>
                    <input type="date" name="from" value="{{ request('from', $startDate->toDateString()) }}" class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">تا</label>
                    <input type="date" name="to" value="{{ request('to', $endDate->toDateString()) }}" class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">اعمال</button>
            @endif
        </form>
        <p class="text-xs text-gray-400 mt-3">بازه‌ی فعلی: <span dir="ltr">{{ $startDate->format('Y-m-d') }}</span> تا <span dir="ltr">{{ $endDate->format('Y-m-d') }}</span></p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border p-5">
            <p class="text-xs text-gray-500 mb-1">جلسات برگزارشده</p>
            <p class="text-3xl font-bold text-indigo-600">{{ $totalSessions }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-5">
            <p class="text-xs text-gray-500 mb-1">کل ورودها</p>
            <p class="text-3xl font-bold text-green-600">{{ $totalAttendances }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-5">
            <p class="text-xs text-gray-500 mb-1">{{ $teacherLabel }}ان فعال</p>
            <p class="text-3xl font-bold text-purple-600">{{ $totalTeachers }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-5">
            <p class="text-xs text-gray-500 mb-1">{{ $studentLabel }}ان حاضر</p>
            <p class="text-3xl font-bold text-blue-600">{{ $totalStudents }}</p>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-2xl border p-6">
            <h3 class="font-bold text-lg mb-4">حضور روزانه</h3>
            @if ($dailyStats->isEmpty())
                <p class="text-sm text-gray-500 text-center py-8">داده‌ای برای نمایش نیست</p>
            @else
                <div class="space-y-2">
                    @foreach ($dailyStats as $stat)
                        <div class="flex justify-between items-center py-2 border-b last:border-b-0">
                            <span class="text-sm" dir="ltr">{{ $stat->session_date }}</span>
                            <div class="flex gap-3 text-xs">
                                <span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded">{{ $stat->sessions }} جلسه</span>
                                <span class="bg-green-50 text-green-700 px-2 py-1 rounded">{{ $stat->total }} ورود</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl border p-6">
            <h3 class="font-bold text-lg mb-4">فعال‌ترین {{ $teacherLabel }}ان</h3>
            @if ($topTeachers->isEmpty())
                <p class="text-sm text-gray-500 text-center py-8">داده‌ای برای نمایش نیست</p>
            @else
                <div class="space-y-3">
                    @foreach ($topTeachers as $item)
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-bold text-sm">{{ $item['user']?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">{{ $item['total_duration'] }} دقیقه حضور</p>
                            </div>
                            <span class="bg-purple-50 text-purple-700 text-xs px-3 py-1.5 rounded-full font-medium">{{ $item['count'] }} جلسه</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold text-lg mb-4">{{ $classLabel }}‌های با بیشترین ورود</h3>
        @if ($topKlasses->isEmpty())
            <p class="text-sm text-gray-500 text-center py-8">داده‌ای برای نمایش نیست</p>
        @else
            <div class="grid md:grid-cols-2 gap-3">
                @foreach ($topKlasses as $item)
                    <div class="flex justify-between items-center bg-gray-50 rounded-xl px-4 py-3">
                        <span class="text-sm font-medium">{{ $item['klass']?->academicLevel?->name }} — {{ $item['klass']?->name }}</span>
                        <span class="bg-indigo-100 text-indigo-700 text-xs px-3 py-1 rounded-full">{{ $item['count'] }} ورود</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
