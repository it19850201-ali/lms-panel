@extends('layouts.app')

@section('title', 'حضور من')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-1">گزارش حضور من</h2>
            <p class="text-sm text-gray-500">
                بازه: <span dir="ltr">{{ \App\Helpers\JalaliHelper::toJalali($from, 'Y/m/d') }}</span> تا <span dir="ltr">{{ \App\Helpers\JalaliHelper::toJalali($to, 'Y/m/d') }}</span>
            </p>
        </div>
        <a href="{{ route('my-attendance.export') }}"
           class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 whitespace-nowrap">
            📥 خروجی اکسل
        </a>
    </div>
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border p-5">
            <p class="text-xs text-gray-500 mb-1">تعداد جلسات</p>
            <p class="text-3xl font-bold text-indigo-600">{{ $stats['total_sessions'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-5">
            <p class="text-xs text-gray-500 mb-1">مجموع حضور</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['total_minutes'] }}<span class="text-sm text-gray-500 mr-1">دقیقه</span></p>
        </div>
        <div class="bg-white rounded-2xl border p-5">
            <p class="text-xs text-gray-500 mb-1">این هفته</p>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['this_week'] }}</p>
        </div>
    </div>

    @if ($logs->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز در هیچ جلسه‌ای حضور نداشته‌اید.
        </div>
    @else
        <div class="space-y-6">
            @foreach ($byDate as $date => $dayLogs)
                <div>
                    <h3 class="text-sm font-bold text-gray-700 mb-2" dir="ltr">{{ $date }}</h3>
                    <div class="bg-white rounded-2xl border overflow-hidden">
                        @foreach ($dayLogs as $log)
                            @php $slot = $log->scheduleSlot; @endphp
                            <div class="flex justify-between items-center px-5 py-4 border-b last:border-b-0">
                                <div>
                                    <p class="font-bold text-sm">{{ $slot?->subject ?? '—' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $slot?->klass?->academicLevel?->name }} — {{ $slot?->klass?->name }}
                                        @if ($slot?->bell)
                                            · {{ $slot->bell->name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500" dir="ltr">
                                        {{ $log->first_joined_at?->format('H:i') }}
                                        @if ($log->left_at)
                                            - {{ $log->left_at->format('H:i') }}
                                        @else
                                            <span class="text-green-600">(حاضر)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs font-bold text-indigo-600 mt-1">
                                        {{ $log->calculateDuration() }} دقیقه
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
