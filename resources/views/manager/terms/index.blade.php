@extends('layouts.app')

@section('title', 'ترم‌های تحصیلی')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-1">ترم‌های تحصیلی</h2>
            <a href="{{ route('manager.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت</a>
        </div>
        <a href="{{ route('manager.terms.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 text-center">
            + ترم جدید
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <div class="bg-blue-50 text-blue-800 rounded-2xl p-4 mb-6 text-sm leading-6">
        💡 هر مدرسه/دانشگاه فقط <strong>یک ترم فعال</strong> داره. {{ $classLabel }}‌های جدید خودکار به ترم فعال اضافه می‌شن.
    </div>

    @if ($terms->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز ترمی تعریف نشده.
        </div>
    @else
        <div class="space-y-3">
            @foreach ($terms as $term)
                @php
                    $isLive = $term->isLive();
                    $isPast = now()->gt($term->ends_at);
                    $isFuture = now()->lt($term->starts_at);
                @endphp
                <div class="bg-white rounded-2xl border p-5 {{ $isLive ? 'ring-2 ring-green-400' : '' }} {{ $isPast ? 'opacity-60' : '' }}">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <h3 class="font-bold text-lg">{{ $term->name }}</h3>
                                @if ($isLive)
                                    <span class="bg-green-500 text-white text-xs px-2 py-0.5 rounded-full">فعال</span>
                                @elseif ($isPast)
                                    <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded-full">پایان‌یافته</span>
                                @elseif ($isFuture)
                                    <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">پیش‌رو</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded-full">غیرفعال</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600">
                                📅 {{ \App\Helpers\JalaliHelper::toJalali($term->starts_at, 'Y/m/d') }}
                                تا
                                {{ \App\Helpers\JalaliHelper::toJalali($term->ends_at, 'Y/m/d') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $term->scheduleSlots()->count() }} {{ $classLabel }}
                            </p>
                        <div class="flex flex-wrap items-center gap-2 mt-3 md:mt-0">
                            @if (!$term->is_active && !$isPast)
                                <form method="POST" action="{{ route('manager.terms.activate', $term) }}" class="m-0">
                                    @csrf
                                    <button type="submit"
                                            class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-green-700">
                                        فعال‌سازی
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('manager.terms.edit', $term) }}"
                               class="border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg text-xs hover:bg-gray-50">
                                ویرایش
                            </a>
                            
                            <form method="POST" action="{{ route('manager.terms.destroy', $term) }}"
                                  onsubmit="return confirm('این ترم حذف شود؟');" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="border border-red-200 text-red-600 px-3 py-1.5 rounded-lg text-xs hover:bg-red-50">
                                    حذف
                                </button>
                            </form>
                        </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
