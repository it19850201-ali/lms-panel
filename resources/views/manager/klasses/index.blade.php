@extends('layouts.app')

@section('title', $classLabel . '‌ها')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold">{{ $classLabel }}‌ها</h2>
            <a href="{{ route('manager.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به پنل</a>
        </div>
        <a href="{{ route('manager.klasses.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + {{ $classLabel }} جدید
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    @if ($klasses->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز {{ $classLabel }}‌ای تعریف نشده.
        </div>
    @else
        <div class="grid gap-3">
            @foreach ($klasses as $klass)
                <div class="bg-white rounded-2xl border p-6 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg">
                            {{ $klass->academicLevel->name }} — {{ $classLabel }} {{ $klass->name }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $klass->students_count }} {{ $studentLabel }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('manager.klasses.edit', $klass) }}"
                           class="border border-gray-300 text-gray-600 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 whitespace-nowrap">
                            ویرایش
                        </a>
                        <form method="POST" action="{{ route('manager.klasses.destroy', $klass) }}"
                              onsubmit="return confirm('آیا مطمئنید؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="border border-red-200 text-red-600 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-50 whitespace-nowrap">
                                حذف
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
