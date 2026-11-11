@extends('layouts.app')

@section('title', $levelLabel . '‌ها')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold">{{ $levelLabel }}‌ها / گروه‌ها</h2>
            <a href="{{ route('manager.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به پنل</a>
        </div>
        <a href="{{ route('manager.levels.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + {{ $levelLabel }} جدید
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    @if ($levels->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز {{ $levelLabel }}‌ای تعریف نشده.
        </div>
    @else
        <div class="grid gap-3">
            @foreach ($levels as $level)
                <div class="bg-white rounded-2xl border p-6 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg">{{ $level->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $level->klasses_count }} {{ $classLabel }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('manager.levels.destroy', $level) }}"
                          onsubmit="return confirm('آیا مطمئنید؟ همه‌ی {{ $classLabel }}‌های این {{ $levelLabel }} هم حذف می‌شوند.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="border border-red-200 text-red-600 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-50">
                            حذف
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
