@extends('layouts.app')

@section('title', 'ورود به کلاس')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border p-8">
        <div class="text-center mb-6">
            <div class="text-4xl mb-3">{{ $slot->is_extra ? '⚡' : '🎓' }}</div>

            <h2 class="text-xl font-bold mb-2">{{ $slot->subject }}</h2>

            @if ($slot->is_extra)
                {{-- فوق‌برنامه --}}
                <p class="text-sm text-gray-500">
                    @if ($slot->extra_scope === 'all')
                        همه‌ی دانش‌آموزان مدرسه
                    @else
                        {{ $slot->klass?->academicLevel?->name }} — {{ $slot->klass ? \App\Helpers\PersianHelper::digitsToPersian($slot->klass->name) : '—' }}
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-2">
                    📅 {{ $slot->time_label }}
                </p>
            @else
                {{-- کلاس عادی --}}
                <p class="text-sm text-gray-500">
                    {{ $slot->klass?->academicLevel?->name }} — {{ $slot->klass ? \App\Helpers\PersianHelper::digitsToPersian($slot->klass->name) : '—' }}
                </p>
                @if ($slot->bell)
                    <p class="text-xs text-gray-400 mt-1" dir="ltr">
                        {{ $slot->bell->start_time_short }} - {{ $slot->bell->end_time_short }}
                    </p>
                @endif
            @endif

            @if ($slot->teacher)
                <p class="text-xs text-gray-400 mt-1">
                    🎓 استاد: {{ $slot->teacher->name }}
                </p>
            @endif
        </div>

        @if ($errors->any())
            <div class="bg-red-50 text-red-600 rounded-lg p-3 mb-4 text-sm">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('guest.join.submit', $meetingId) }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-2">نام و نام خانوادگی</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       placeholder="نام خود را وارد کنید"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full bg-green-600 text-white rounded-lg py-3 font-semibold hover:bg-green-700 transition">
                ورود به کلاس
            </button>
        </form>

        <p class="text-xs text-gray-400 text-center mt-4">
            این جلسه توسط سامانه آموزش مجازی مدیریت می‌شود
        </p>
    </div>
</div>
@endsection
