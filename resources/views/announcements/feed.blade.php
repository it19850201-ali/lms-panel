@extends('layouts.app')

@section('title', 'اطلاعیه‌ها')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">📢 اطلاعیه‌ها</h2>
        <p class="text-sm text-gray-500">آخرین اخبار و اطلاع‌رسانی‌ها</p>
    </div>

    @if ($announcements->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز اطلاعیه‌ای منتشر نشده است.
        </div>
    @else
        <div class="space-y-3">
            @foreach ($announcements as $a)
                @php $info = $a->priority_info; @endphp
                <div class="bg-white rounded-2xl border p-5 border-r-4
                    @if ($a->priority === 'urgent') border-r-red-500
                    @elseif ($a->priority === 'important') border-r-orange-500
                    @else border-r-blue-500
                    @endif">
                    <div class="flex items-center gap-2 flex-wrap mb-2">
                        <span class="text-lg">{{ $info['icon'] }}</span>
                        <h3 class="font-bold text-base">{{ $a->title }}</h3>
                        @if ($a->priority !== 'normal')
                            <span class="text-xs px-2 py-0.5 rounded-full
                                @if ($a->priority === 'urgent') bg-red-100 text-red-700
                                @else bg-orange-100 text-orange-700
                                @endif">
                                {{ $info['label'] }}
                            </span>
                        @endif
                    </div>

                    <p class="text-sm text-gray-700 leading-7 whitespace-pre-line">{{ $a->body }}</p>

                    <div class="flex items-center gap-3 mt-3 text-xs text-gray-500 flex-wrap">
                        <span>👤 {{ $a->author->name }}</span>
                        <span>🗓️ {{ \App\Helpers\JalaliHelper::toJalali($a->created_at, 'Y/m/d H:i') }}</span>
                        @if ($a->audience_type === 'class')
                            <span>🎯 فقط کلاس {{ $a->klass?->name }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
