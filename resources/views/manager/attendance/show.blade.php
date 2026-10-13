@extends('layouts.app')

@section('title', 'جزئیات حضور')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">جزئیات حضور</h2>
        <a href="{{ route('manager.attendance') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت</a>
    </div>

    <div class="bg-white rounded-2xl border p-8">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <p class="text-xs text-gray-500 mb-1">نام</p>
                <p class="font-bold">{{ $log->full_name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">نقش</p>
                <p class="font-bold">
                    {{ $log->isModerator() ? 'معلم' : 'دانش‌آموز' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">تاریخ</p>
                <p dir="ltr">{{ \App\Helpers\JalaliHelper::toJalali($log->session_date, 'Y/m/d') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">کلاس</p>
                <p>{{ $log->scheduleSlot?->klass?->full_name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">زنگ</p>
                <p>
                    @if ($log->scheduleSlot?->bell)
                        {{ $log->scheduleSlot->bell->name }}
                        ({{ $log->scheduleSlot->bell->start_time_short }} - {{ $log->scheduleSlot->bell->end_time_short }})
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">وضعیت</p>
                <p>
                    @if ($log->left_at)
                        <span class="text-gray-600">خارج‌شده</span>
                    @else
                        <span class="text-green-600 font-bold">حاضر</span>
                    @endif
                </p>
            </div>

            <div class="col-span-2 border-t pt-6 grid grid-cols-3 gap-6">
                <div>
                    <p class="text-xs text-gray-500 mb-1">ورود</p>
                    <p class="font-bold" dir="ltr">{{ $log->first_joined_at?->format('H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">خروج</p>
                    <p class="font-bold" dir="ltr">{{ $log->left_at?->format('H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">مدت حضور</p>
                    <p class="font-bold text-indigo-600">{{ $log->calculateDuration() }} دقیقه</p>
                </div>
            </div>

            @if ($log->last_seen_at)
                <div class="col-span-2 text-xs text-gray-400 border-t pt-4">
                    آخرین بار دیده‌شده: <span dir="ltr">{{ \App\Helpers\JalaliHelper::toJalali($log->last_seen_at) }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
