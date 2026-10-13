@extends('layouts.app')

@section('title', 'برنامه کلاس من')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">برنامه کلاس من</h2>
        <p class="text-sm text-gray-500">
کلاس: {{ $klass->academicLevel->name }} — {{ \App\Helpers\PersianHelper::digitsToPersian($klass->name) }}
        </p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    @if ($slotsByCell->isEmpty())
        <div class="bg-yellow-50 text-yellow-700 rounded-2xl p-6 text-sm">
            هنوز برنامه‌ای برای کلاس شما تنظیم نشده.
        </div>
    @else
    @if (isset($extras) && $extras->count() > 0)
        <div class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 mb-3">⚡ جلسات فوق‌برنامه</h3>
            <div class="space-y-2">
                @foreach ($extras as $extra)
                    @php
                        $isLive = $extra->isLive();
                        $isPast = $extra->ends_at && $extra->ends_at->lt(now());
                    @endphp
                    <div class="bg-gradient-to-l from-yellow-50 to-orange-50 border border-yellow-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 {{ $isLive ? 'ring-2 ring-green-400' : '' }}">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-bold">{{ $extra->subject }}</h4>
                                @if ($isLive)
                                    <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full animate-pulse">زنده</span>
                                @elseif ($isPast)
                                    <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">به پایان رسیده</span>
                                @endif
                                <span class="text-xs bg-white px-2 py-0.5 rounded-full text-gray-600">
                                    {{ $extra->extra_scope === 'all' ? 'همه‌ی مدرسه' : $extra->klass?->name }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">
                                📅 {{ $extra->time_label }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            @if (!$isPast)
                                <a href="{{ route('my-schedule.join', $extra) }}"
                                   class="inline-block {{ $isLive ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap">
                                    ورود به کلاس
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
        <div class="bg-white rounded-2xl border overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-3 py-3 text-right w-32 border-l">زنگ</th>
                        @foreach ($activeDays as $day)
                            <th class="px-3 py-3 text-center border-l">{{ $days[$day] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bells as $bell)
                        <tr class="border-b last:border-b-0 {{ $bell->is_break ? 'bg-yellow-50' : '' }}">
                            <td class="px-3 py-3 border-l align-top">
                                <div class="font-bold text-xs">{{ $bell->name }}</div>
                                <div class="text-xs text-gray-500 mt-1" dir="ltr">{{ $bell->start_time_short }}-{{ $bell->end_time_short }}</div>
                            </td>

                            @foreach ($activeDays as $day)
                                @php
                                    $slot = $slotsByCell->get($day . '-' . $bell->id);
                                @endphp

                                <td class="px-2 py-2 border-l align-top {{ $bell->is_break ? 'text-center text-xs text-gray-400' : '' }}">
                                    @if ($bell->is_break)
                                        <span>—</span>
                                    @elseif ($slot)
                                        <div class="rounded-lg bg-indigo-50 border border-indigo-200 p-2">
                                            <div class="font-bold text-sm text-indigo-800">{{ $slot->subject }}</div>
                                            <div class="text-xs text-gray-600 mt-1">{{ $slot->teacher->name }}</div>
                                            @if ($slot->bbb_meeting_id)
                                                <a href="{{ route('my-schedule.join', $slot) }}"
                                                   class="mt-2 block text-center bg-green-600 text-white text-xs px-2 py-1.5 rounded-lg hover:bg-green-700">
                                                    ورود به کلاس
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <div class="w-full h-full min-h-[50px] rounded-lg border-2 border-dashed border-gray-100"></div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
