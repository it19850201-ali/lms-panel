@extends('layouts.app')

@section('title', 'کلاس‌های فوق‌برنامه')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-1">⚡ کلاس‌های فوق‌برنامه</h2>
            <a href="{{ route('manager.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به پنل</a>
        </div>
        <a href="{{ route('manager.extra-class.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + فوق‌برنامه جدید
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    @if ($extras->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز فوق‌برنامه‌ای ساخته نشده.
        </div>
    @else
        <div class="space-y-3">
            @foreach ($extras as $extra)
                @php
                    $isLive = $extra->isLive();
                    $isPast = $extra->ends_at && $extra->ends_at->lt(now());
                @endphp
                <div class="bg-white rounded-2xl border p-5 {{ $isLive ? 'ring-2 ring-green-400' : '' }} {{ $isPast ? 'opacity-60' : '' }}">
                    <div class="flex justify-between items-start gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <h3 class="font-bold text-base">{{ $extra->subject }}</h3>
                                @if ($isLive)
                                    <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full animate-pulse">زنده</span>
                                @elseif ($isPast)
                                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">پایان‌یافته</span>
                                @else
                                    <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-0.5 rounded-full">پیش‌رو</span>
                                @endif
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                                    {{ $extra->extra_scope === 'all' ? 'همه‌ی مدرسه' : ('کلاس ' . $extra->klass?->name) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-600">
                                <div>📅 {{ $extra->time_label }}</div>
				<div>🎓 استاد: {{ $extra->teacher?->name ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- دکمه‌ها --}}
                    <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t">
                        @if ($extra->bbb_meeting_id)
                            <button type="button"
                                    onclick="copyLink('{{ route('guest.join.show', $extra->bbb_meeting_id) }}', this)"
                                    class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-indigo-700">
                                📋 کپی لینک مهمان
                            </button>
                        @endif

                        @if (!$isPast)
                            <a href="{{ route('my-schedule.join', $extra) }}"
                               class="border border-indigo-300 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-indigo-50">
                                🎓 ورود به کلاس
                            </a>
                        @endif

                        <form method="POST" action="{{ route('manager.extra-class.destroy', $extra) }}"
                              onsubmit="return confirm('این فوق‌برنامه حذف شود؟');" class="mr-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="border border-red-200 text-red-600 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-red-50">
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

@push('scripts')
<script>
function copyLink(url, btn) {
    const original = btn.textContent;
    const showSuccess = () => {
        btn.textContent = '✓ کپی شد!';
        btn.classList.add('bg-green-600');
        btn.classList.remove('bg-indigo-600');
        setTimeout(() => {
            btn.textContent = original;
            btn.classList.remove('bg-green-600');
            btn.classList.add('bg-indigo-600');
        }, 2000);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(showSuccess).catch(() => fallbackCopy(url, showSuccess));
    } else {
        fallbackCopy(url, showSuccess);
    }
}

function fallbackCopy(text, callback) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    try { document.execCommand('copy'); callback(); }
    catch (e) { prompt('لینک را کپی کنید:', text); }
    document.body.removeChild(textarea);
}
</script>
@endpush
