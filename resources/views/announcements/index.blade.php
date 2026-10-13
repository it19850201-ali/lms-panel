@extends('layouts.app')

@section('title', 'مدیریت اطلاعیه‌ها')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-1">مدیریت اطلاعیه‌ها</h2>
            <a href="{{ route('home') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت</a>
        </div>
        <a href="{{ route('announcements.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + اطلاعیه جدید
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif

    @if ($announcements->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز اطلاعیه‌ای منتشر نشده است.
        </div>
    @else
        <div class="space-y-3">
            @foreach ($announcements as $a)
                @php $info = $a->priority_info; @endphp
                <div class="bg-white rounded-2xl border p-5 {{ !$a->is_active ? 'opacity-60' : '' }}">
                    <div class="flex justify-between items-start gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <span class="text-lg">{{ $info['icon'] }}</span>
                                <h3 class="font-bold text-base">{{ $a->title }}</h3>
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    @if ($a->priority === 'urgent') bg-red-100 text-red-700
                                    @elseif ($a->priority === 'important') bg-orange-100 text-orange-700
                                    @else bg-blue-100 text-blue-700
                                    @endif">
                                    {{ $info['label'] }}
                                </span>
                                @if (!$a->is_active)
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">غیرفعال</span>
                                @endif
                                @if ($a->isExpired())
                                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">منقضی</span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600 line-clamp-2">{{ Str::limit($a->body, 150) }}</p>

                            <div class="flex items-center gap-3 mt-3 text-xs text-gray-500 flex-wrap">
                                <span>📢 {{ $a->audience_label }}</span>
                                <span>👤 {{ $a->author->name }}</span>
                                <span>🗓️ {{ \App\Helpers\JalaliHelper::toJalali($a->created_at, 'Y/m/d H:i') }}</span>
                                @if ($a->expires_at)
                                    <span>⏰ انقضا: {{ \App\Helpers\JalaliHelper::toJalali($a->expires_at, 'Y/m/d H:i') }}</span>
                                @endif
                                <span>👁️ {{ $a->reads()->count() }} بازدید</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1 flex-shrink-0">
                            <a href="{{ route('announcements.edit', $a) }}"
                               class="text-xs border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-50 text-center">
                                ویرایش
                            </a>
                            <form method="POST" action="{{ route('announcements.destroy', $a) }}"
                                  onsubmit="return confirm('این اطلاعیه حذف شود؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full text-xs border border-red-200 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50">
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
