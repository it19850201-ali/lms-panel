@extends('layouts.app')

@section('title', 'کلاس‌های من')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold">کلاس‌های من</h2>

        {{-- دکمه‌ی ساخت کلاس: فقط برای ادمین و مدیر --}}
        @if (auth()->user()->isAdmin() || auth()->user()->isManager())
            <a href="{{ route('courses.create.select') }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
                + کلاس جدید
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    @if ($courses->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز کلاسی برای شما ثبت نشده است.
        </div>
    @else
        <div class="grid gap-4">
            @foreach ($courses as $course)
                @php
                    $now = now();
                    $notStarted = $course->starts_at && $now->lt($course->starts_at);
                    $ended = $course->ends_at && $now->gt($course->ends_at);
                    $isActive = !$notStarted && !$ended;
                @endphp

                <div class="bg-white rounded-2xl border p-6 flex justify-between items-center">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h3 class="font-bold text-lg">{{ $course->title }}</h3>

                            @if ($isActive)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">فعال</span>
                            @elseif ($notStarted)
                                <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full">در انتظار شروع</span>
                            @elseif ($ended)
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">پایان‌یافته</span>
                            @endif
                        </div>

                        <p class="text-sm text-gray-500 mt-2">
                            نوع: {{ $course->type === 'regular' ? 'منظم' : 'فوق‌برنامه' }}
                            @if (!empty($course->meta['student_count']))
                                · {{ $course->meta['student_count'] }} دانش‌آموز
                            @endif
                        </p>

                        @if ($course->starts_at || $course->ends_at)
                            <p class="text-xs text-gray-400 mt-1" dir="rtl">
                                @if ($course->starts_at && $course->ends_at)
                                    از {{ \App\Helpers\JalaliHelper::toJalali($course->starts_at) }}
                                    تا {{ \App\Helpers\JalaliHelper::toJalali($course->ends_at) }}
                                @elseif ($course->starts_at)
                                    از {{ \App\Helpers\JalaliHelper::toJalali($course->starts_at) }}
                                @else
                                    تا {{ \App\Helpers\JalaliHelper::toJalali($course->ends_at) }}
                                @endif
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- دکمه‌ی ورود به کلاس: برای همه --}}
                        @if ($isActive)
                            <a href="{{ route('courses.join', $course) }}"
                               class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 whitespace-nowrap">
                                ورود به کلاس
                            </a>
                        @else
                            <button disabled
                                    class="bg-gray-200 text-gray-400 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed whitespace-nowrap">
                                {{ $notStarted ? 'هنوز شروع نشده' : 'پایان‌یافته' }}
                            </button>
                        @endif

                        {{-- دکمه‌های ویرایش و حذف: فقط برای ادمین و مدیر --}}
                        @if (auth()->user()->isAdmin() || auth()->user()->isManager())
                            <a href="{{ route('courses.edit', $course) }}"
                               class="border border-gray-300 text-gray-600 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 whitespace-nowrap">
                                ویرایش
                            </a>

                            <form method="POST" action="{{ route('courses.destroy', $course) }}"
                                  onsubmit="return confirm('آیا مطمئنید که می‌خواهید کلاس «{{ $course->title }}» را حذف کنید؟ این کار کلاس را از Moodle هم پاک می‌کند و برگشت‌پذیر نیست.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="border border-red-200 text-red-600 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-50 whitespace-nowrap">
                                    حذف
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
