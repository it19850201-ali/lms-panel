@extends('layouts.app')

@section('title', 'خانه‌ی من')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- هشدار رمز --}}
    @if ($user->shouldChangePassword())
        <div style="background: linear-gradient(to left, #fefce8, #fff7ed); border: 1px solid #fde047; border-radius: 16px; padding: 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: #fef08a; display: flex; align-items: center; justify-content: center; font-size: 20px;">⚠️</div>
                <div>
                    <p style="font-weight: bold; color: #854d0e; font-size: 14px; margin: 0;">رمز عبور شما همان کد ملی است</p>
                    <p style="color: #a16207; font-size: 12px; margin: 2px 0 0 0;">برای امنیت بیشتر، رمز خود را تغییر دهید</p>
                </div>
            </div>
            <a href="{{ route('profile.show') }}#change-password"
               style="background: #ca8a04; color: white; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: bold; text-decoration: none; white-space: nowrap;">
                تغییر رمز
            </a>
        </div>
    @endif

    {{-- خوش‌آمدگویی --}}
    <div style="background: linear-gradient(to left, #4f46e5 0%, #9333ea 50%, #4338ca 100%); border-radius: 24px; padding: 32px; margin-bottom: 24px; color: white; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 64px; height: 64px; border-radius: 16px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 30px;">
                🎓
            </div>
            <div>
                <h1 style="font-size: 24px; font-weight: bold; margin: 0 0 4px 0; color: white;">
                    سلام، {{ $user->name }}!
                </h1>
                @if ($klass)
                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 14px; margin: 0;">
                        کلاس {{ $klass->academicLevel->name }} — {{ $klass->name }}
                    </p>
                @else
                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 14px; margin: 0;">
                        هنوز به کلاسی اختصاص داده نشده‌اید
                    </p>
                @endif
            </div>
        </div>
    </div>
    {{-- اطلاعیه‌ها --}}
    @if (isset($announcements) && $announcements->count() > 0)
        <div class="mb-6">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-bold text-lg">📢 اطلاعیه‌ها</h2>
                <a href="{{ route('announcements.feed') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    همه ←
                </a>
            </div>
            <div class="space-y-2">
                @foreach ($announcements as $a)
                    @php $info = $a->priority_info; @endphp
                    <a href="{{ route('announcements.feed') }}"
                       class="block bg-white rounded-2xl border p-4 border-r-4
                        @if ($a->priority === 'urgent') border-r-red-500
                        @elseif ($a->priority === 'important') border-r-orange-500
                        @else border-r-blue-500
                        @endif hover:shadow-sm transition">
                        <div class="flex items-center gap-2 mb-1">
                            <span>{{ $info['icon'] }}</span>
                            <h4 class="font-bold text-sm">{{ $a->title }}</h4>
                            @if ($a->priority !== 'normal')
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    @if ($a->priority === 'urgent') bg-red-100 text-red-700
                                    @else bg-orange-100 text-orange-700
                                    @endif">
                                    {{ $info['label'] }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-600 line-clamp-1">{{ Str::limit($a->body, 80) }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ \App\Helpers\JalaliHelper::toJalali($a->created_at, 'Y/m/d') }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- فوق‌برنامه‌ها --}}
    @if ($extras->count() > 0)
        <div class="mb-6">
            <h2 class="font-bold text-lg mb-3">⚡ جلسات فوق‌برنامه</h2>
            <div class="space-y-2">
                @foreach ($extras as $extra)
                    @php
                        $isLive = $extra->isLive();
                        $isPast = $extra->ends_at && $extra->ends_at->lt(now());
                    @endphp
                    <div class="bg-gradient-to-l from-yellow-50 to-orange-50 border border-yellow-200 rounded-2xl p-4 {{ $isLive ? 'ring-2 ring-green-400' : '' }}">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="font-bold text-sm">{{ $extra->subject }}</h4>
                                    @if ($isLive)
                                        <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full animate-pulse">زنده</span>
                                    @elseif ($isPast)
                                        <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded-full">به پایان رسیده</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-700 mt-1">
                                    📅 {{ $extra->time_label }}
                                </p>
                                @if ($extra->teacher)
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        👨‍🏫 {{ $extra->teacher->name }}
                                    </p>
                                @endif
                            </div>
                            @if (!$isPast)
                                <a href="{{ route('my-schedule.join', $extra) }}"
                                   class="flex-shrink-0 {{ $isLive ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white px-4 py-2 rounded-lg text-xs font-bold whitespace-nowrap">
                                    ورود
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- برنامه امروز --}}
    <div class="mb-6">
        <div class="flex justify-between items-center mb-3">
            <h2 class="font-bold text-lg">
                📅 برنامه امروز
                <span class="text-sm font-normal text-gray-500 mr-2">
                    ({{ \App\Models\ScheduleSlot::$days[$today] ?? '—' }})
                </span>
            </h2>
            <a href="{{ route('my-schedule') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                برنامه هفتگی ←
            </a>
        </div>

        @if (!$klass)
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 text-sm text-yellow-800">
                هنوز به کلاسی اختصاص داده نشده‌اید. لطفاً با مدیر مدرسه تماس بگیرید.
            </div>
        @elseif ($todaySlots->isEmpty())
            <div class="bg-white rounded-2xl border p-10 text-center">
                <div class="text-4xl mb-3">🎉</div>
                <p class="font-bold text-gray-700">امروز کلاسی نداری!</p>
                <p class="text-sm text-gray-500 mt-1">روز خوبی داشته باشی</p>
            </div>
        @else
            <div class="space-y-3">
                @php $currentTime = now()->format('H:i:s'); @endphp

                @foreach ($todaySlots as $slot)
                    @php
                        $bell = $slot->bell;
                        $isLive = $currentTime >= $bell->start_time && $currentTime <= $bell->end_time;
                        $isPast = $currentTime > $bell->end_time;
                    @endphp

                    <div class="bg-white rounded-2xl border p-5 flex items-center gap-4 {{ $isLive ? 'border-green-400 bg-green-50/30 shadow-sm' : '' }}">
                        <div class="flex-shrink-0 text-center">
                            <div class="w-16 h-16 rounded-xl {{ $isLive ? 'bg-green-100' : ($isPast ? 'bg-gray-100' : 'bg-indigo-50') }} flex flex-col items-center justify-center">
                                @if ($isLive)
                                    <span class="text-xs font-bold text-green-700">الان</span>
                                    <span class="text-xs text-green-600 mt-0.5">{{ $bell->start_time_short }}</span>
                                @else
                                    <span class="text-sm font-bold {{ $isPast ? 'text-gray-400' : 'text-indigo-700' }}">{{ $bell->start_time_short }}</span>
                                    <span class="text-xs {{ $isPast ? 'text-gray-300' : 'text-indigo-400' }} mt-0.5">{{ $bell->end_time_short }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-lg {{ $isPast ? 'text-gray-400' : '' }}">{{ $slot->subject }}</h3>
                                @if ($isLive)
                                    <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full animate-pulse">زنده</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                👨‍🏫 {{ $slot->teacher->name }}
                                @if ($slot->room)
                                    · اتاق {{ $slot->room }}
                                @endif
                            </p>
                        </div>

                        <div class="flex-shrink-0">
                            @if ($isPast)
                                <span class="text-xs text-gray-400">به پایان رسید</span>
                            @else
                                <a href="{{ route('my-schedule.join', $slot) }}"
                                   class="inline-block {{ $isLive ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white px-4 py-2.5 rounded-lg text-sm font-bold whitespace-nowrap">
                                    ورود به کلاس
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- آمار --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-2xl border p-5">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-base">📊</div>
                <p class="text-xs text-gray-500">حضور این هفته</p>
            </div>
            <p class="text-2xl font-bold text-indigo-600">{{ $stats['this_week'] }}</p>
        </div>

        <div class="bg-white rounded-2xl border p-5">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-base">📈</div>
                <p class="text-xs text-gray-500">حضور این ماه</p>
            </div>
            <p class="text-2xl font-bold text-green-600">{{ $stats['this_month'] }}</p>
        </div>

        <a href="{{ route('materials') }}" class="bg-white rounded-2xl border p-5 hover:border-indigo-400 hover:shadow-sm transition">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-base">📚</div>
                <p class="text-xs text-gray-500">منابع کلاس</p>
            </div>
            <p class="text-sm font-bold text-purple-600">مشاهده</p>
        </a>

        <a href="{{ route('my-attendance') }}" class="bg-white rounded-2xl border p-5 hover:border-indigo-400 hover:shadow-sm transition">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center text-base">🗓️</div>
                <p class="text-xs text-gray-500">گزارش حضور</p>
            </div>
            <p class="text-sm font-bold text-teal-600">مشاهده</p>
        </a>
    </div>

    {{-- دسترسی سریع --}}
    <h3 class="text-sm font-bold text-gray-700 mb-3">دسترسی سریع</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="{{ route('my-schedule') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-lg">📅</div>
            <div>
                <p class="font-bold text-sm">برنامه هفتگی</p>
                <p class="text-xs text-gray-500 mt-0.5">همه‌ی روزها</p>
            </div>
        </a>

        <a href="{{ route('materials') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-lg">📚</div>
            <div>
                <p class="font-bold text-sm">منابع کلاس</p>
                <p class="text-xs text-gray-500 mt-0.5">جزوه و تکلیف</p>
            </div>
        </a>

        <a href="{{ route('my-attendance') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-lg">📊</div>
            <div>
                <p class="font-bold text-sm">حضور من</p>
                <p class="text-xs text-gray-500 mt-0.5">گزارش جلسات</p>
            </div>
        </a>

        <a href="{{ route('profile.show') }}" class="bg-white rounded-xl border p-4 hover:border-indigo-400 hover:shadow-sm transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-lg">👤</div>
            <div>
                <p class="font-bold text-sm">پروفایل</p>
                <p class="text-xs text-gray-500 mt-0.5">اطلاعات من</p>
            </div>
        </a>
    </div>
</div>
@endsection
