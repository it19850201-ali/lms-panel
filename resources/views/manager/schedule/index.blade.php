@extends('layouts.app')

@section('title', 'برنامه هفتگی')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">برنامه هفتگی</h2>
        <a href="{{ route('manager.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به پنل</a>
    </div>

    {{-- بنر آپلود اکسل --}}
    <div class="bg-gradient-to-l from-indigo-50 to-purple-50 border border-indigo-100 rounded-2xl p-5 mb-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-xl">
                📥
            </div>
            <div>
                <h3 class="font-bold text-indigo-900 text-sm">آپلود گروهی برنامه</h3>
                <p class="text-xs text-indigo-700 mt-0.5">به‌جای وارد کردن دستی، برنامه هفته رو از اکسل وارد کنید</p>
            </div>
        </div>
        <a href="{{ route('manager.schedule.import') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-700 whitespace-nowrap">
            آپلود اکسل
        </a>
    </div>

    @if ($klasses->isEmpty())
        <div class="bg-yellow-50 text-yellow-700 rounded-2xl p-6 text-sm">
            اول باید کلاس بسازی. <a href="{{ route('manager.klasses') }}" class="underline">مدیریت کلاس‌ها</a>
        </div>
    @else
        <p class="text-sm text-gray-500 mb-4">برای مشاهده و ویرایش برنامه‌ی هر کلاس، روی آن کلیک کنید.</p>
        <div class="grid gap-3">
            @foreach ($klasses as $klass)
                <a href="{{ route('manager.schedule.show', $klass) }}"
                   class="bg-white rounded-2xl border p-6 hover:border-indigo-400 transition flex justify-between items-center">
                    <div>
			<h3 class="font-bold text-lg">{{ $klass->academicLevel->name }} — کلاس {{ \App\Helpers\PersianHelper::digitsToPersian($klass->name) }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $klass->students_count }} دانش‌آموز</p>
                    </div>
                    <span class="text-indigo-600 text-sm font-medium">مشاهده برنامه ←</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
