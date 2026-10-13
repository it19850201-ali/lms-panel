@extends('layouts.app')

@section('title', 'مدیریت معلمان')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-1">مدیریت معلمان</h2>
            <a href="{{ route('manager.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به پنل</a>
        </div>
        <a href="{{ route('manager.teachers.create') }}"
           class="bg-indigo-600 text-white px-6 py-3 rounded-lg text-base font-bold hover:bg-indigo-700 text-center whitespace-nowrap">
            + افزودن معلم
        </a>
    </div>
    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <div class="mb-10">
        <h3 class="text-lg font-bold mb-4">در انتظار تایید ({{ $pending->count() }})</h3>

        @if ($pending->isEmpty())
            <div class="bg-white rounded-2xl border p-8 text-center text-gray-500 text-sm">
                هیچ درخواست در انتظاری نیست.
            </div>
        @else
            <div class="grid gap-3">
                @foreach ($pending as $t)
                    <div class="bg-white rounded-2xl border p-6 flex justify-between items-center">
                        <div>
                            <h4 class="font-bold">{{ $t->name }}</h4>
                            <p class="text-sm text-gray-500 mt-1" dir="ltr">{{ $t->mobile }}</p>
                            @if (auth()->user()->isAdmin())
                                <p class="text-xs text-gray-400 mt-1">مدرسه: {{ $t->school?->name ?? '—' }}</p>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('manager.teachers.approve', $t) }}">
                                @csrf
                                <button type="submit"
                                        class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">
                                    تایید
                                </button>
                            </form>
                            <form method="POST" action="{{ route('manager.teachers.reject', $t) }}"
                                  onsubmit="return confirm('آیا مطمئنید؟');">
                                @csrf
                                <button type="submit"
                                        class="border border-red-200 text-red-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-50">
                                    رد
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div>
        <h3 class="text-lg font-bold mb-4">تایید‌شده ({{ $approved->count() }})</h3>
    {{-- غیرفعال‌شده‌ها --}}
    @if (isset($rejected) && $rejected->count() > 0)
        <div class="mt-10">
            <h3 class="text-lg font-bold mb-4">غیرفعال‌شده ({{ $rejected->count() }})</h3>
            <div class="grid gap-3">
                @foreach ($rejected as $t)
                    <div class="bg-white rounded-2xl border p-6 flex justify-between items-center gap-3 opacity-70">
                        <div class="flex-1">
                            <h4 class="font-bold">{{ $t->name }}</h4>
                            <p class="text-sm text-gray-500 mt-1" dir="ltr">{{ $t->mobile }}</p>
                        </div>
                        <form method="POST" action="{{ route('manager.teachers.toggle-status', $t) }}"
                              onsubmit="return confirm('این معلم فعال شود؟');">
                            @csrf
                            <button type="submit"
                                    class="border border-green-200 text-green-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-50 whitespace-nowrap">
                                ✓ فعال‌سازی
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($approved->isEmpty())
            <div class="bg-white rounded-2xl border p-8 text-center text-gray-500 text-sm">
                هنوز معلمی تایید نشده.
            </div>
        @else
            <div class="grid gap-3">
                @foreach ($approved as $t)
                    <div class="bg-white rounded-2xl border p-6 flex justify-between items-center gap-3">
                        <div class="flex-1">
                            <h4 class="font-bold">{{ $t->name }}</h4>
                            <p class="text-sm text-gray-500 mt-1" dir="ltr">{{ $t->mobile }}</p>
                            @if (auth()->user()->isAdmin())
                                <p class="text-xs text-gray-400 mt-1">مدرسه: {{ $t->school?->name ?? '—' }}</p>
                            @endif
                            @if ($t->moodle_user_id)
                                <p class="text-xs text-green-600 mt-1">Moodle ID: {{ $t->moodle_user_id }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('manager.teachers.toggle-status', $t) }}"
                              onsubmit="return confirm('این معلم غیرفعال شود؟ دیگه نمی‌تونه وارد سامانه بشه.');">
                            @csrf
                            <button type="submit"
                                    class="border border-orange-200 text-orange-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-50 whitespace-nowrap">
                                🚫 غیرفعال
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
