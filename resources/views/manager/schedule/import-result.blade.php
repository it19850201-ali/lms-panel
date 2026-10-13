@extends('layouts.app')

@section('title', 'نتیجه ایمپورت برنامه')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-1">نتیجه ایمپورت</h2>
        <a href="{{ route('manager.schedule') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به برنامه هفتگی</a>
    </div>

    {{-- خلاصه --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border p-6">
            <p class="text-sm text-gray-500 mb-1">درس جدید</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['slots_created'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-6">
            <p class="text-sm text-gray-500 mb-1">درس آپدیت‌شده</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['slots_updated'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-6">
            <p class="text-sm text-gray-500 mb-1">معلم جدید</p>
            <p class="text-3xl font-bold text-indigo-600">{{ $stats['teachers_created'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-6">
            <p class="text-sm text-gray-500 mb-1">پایه جدید</p>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['levels_created'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-6">
            <p class="text-sm text-gray-500 mb-1">کلاس جدید</p>
            <p class="text-3xl font-bold text-pink-600">{{ $stats['klasses_created'] }}</p>
        </div>
    </div>

    {{-- خطاها --}}
    @if (!empty($stats['errors']))
        <div class="bg-white rounded-2xl border p-6 mb-6">
            <h3 class="font-bold text-red-600 mb-3">
                ⚠️ هشدارها ({{ count($stats['errors']) }} مورد)
            </h3>
            <ul class="space-y-2 text-sm text-gray-700 leading-6">
                @foreach ($stats['errors'] as $error)
                    <li class="flex gap-2">
                        <span class="text-red-400">●</span>
                        <span>{{ $error }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex gap-3">
        <a href="{{ route('manager.schedule.import') }}"
           class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold text-center hover:bg-indigo-700">
            آپلود فایل دیگر
        </a>
        <a href="{{ route('manager.schedule') }}"
           class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200">
            مشاهده برنامه
        </a>
    </div>
</div>
@endsection
