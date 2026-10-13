@extends('layouts.app')

@section('title', 'برنامه هفتگی من')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">برنامه هفتگی من</h2>
        <p class="text-sm text-gray-500">{{ auth()->user()->name }}</p>
    </div>

    @if (session('error'))
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- ============ فوق‌برنامه‌ها ============ --}}
    @if (isset($extras) && $extras->count() > 0)
        <div class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 mb-3">⚡ جلسات فوق‌برنامه</h3>
            <div class="space-y-2">
                @foreach ($extras as $extra)
                    @php
                        $isLive = $extra->isLive();
                        $isPast = $extra->ends_at && $extra->ends_at->lt(now());
                    @endphp
                    <div class="bg-gradient-to-l from-yellow-50 to-orange-50 border border-yellow-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 {{ $isLive ? 'ring-2 ring-green-400' : '' }} {{ $isPast ? 'opacity-60' : '' }}">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-bold">{{ $extra->subject }}</h4>
                                @if ($isLive)
                                    <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full animate-pulse">زنده</span>
                                @elseif ($isPast)
                                    <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded-full">به پایان رسیده</span>
                                @else
                                    <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-0.5 rounded-full">پیش‌رو</span>
                                @endif
                                <span class="text-xs bg-white px-2 py-0.5 rounded-full text-gray-600">
                                    {{ $extra->extra_scope === 'all' ? 'همه‌ی مدرسه' : ($extra->klass?->name ?? '—') }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-700 mt-2">📅 {{ $extra->time_label }}</p>
                        </div>
                        <div class="flex-shrink-0 flex flex-col gap-2">
                            @if (!$isPast)
                                <a href="{{ route('my-schedule.join', $extra) }}"
                                   class="inline-block {{ $isLive ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap text-center">
                                    🎓 ورود به کلاس
                                </a>
                            @endif

                            @if ($extra->bbb_meeting_id)
                                <button type="button"
                                        onclick="copyStudentLink('{{ route('guest.join.show', $extra->bbb_meeting_id) }}', this)"
                                        class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-xs font-medium hover:bg-gray-50 whitespace-nowrap">
                                    📋 کپی لینک مهمان
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ============ گرید هفتگی ============ --}}
    @if ($slots->isEmpty())
        {{-- معلم هیچ کلاس عادی نداره --}}
        @if (!isset($extras) || $extras->isEmpty())
            <div class="bg-yellow-50 text-yellow-700 rounded-2xl p-6 text-sm">
                هنوز هیچ کلاسی به شما اختصاص داده نشده. لطفاً با مدیر مدرسه تماس بگیرید.
            </div>
        @endif
    @else
        <h3 class="text-sm font-bold text-gray-700 mb-3">📅 برنامه هفتگی</h3>

        @if ($klasses->count() > 1)
            <div class="bg-white rounded-2xl border p-4 mb-4 flex flex-wrap gap-3 text-xs">
                @foreach ($klasses as $k)
                    <span class="inline-flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-{{ $klassColors[$k->id] }}-500"></span>
                        <span>{{ $k->academicLevel->name }} — {{ $k->name }}</span>
                    </span>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-2xl border overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-3 py-3 text-right w-32 border-l">زنگ</th>
                        @foreach ($activeDays as $day)
                            <th class="px-3 py-3 text-center border-l">{{ $days[$day] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bells as $bell)
                        <tr class="border-b last:border-b-0 {{ $bell->is_break ? 'bg-yellow-50' : '' }}">
                            <td class="px-3 py-3 border-l align-top">
                                <div class="font-bold text-xs">{{ $bell->name }}</div>
                                <div class="text-xs text-gray-500 mt-1" dir="ltr">{{ $bell->start_time_short }}-{{ $bell->end_time_short }}</div>
                            </td>

                            @foreach ($activeDays as $day)
                                @php
                                    $slot = $slotsByCell->get($day . '-' . $bell->id);
                                @endphp

                                <td class="px-2 py-2 border-l align-top {{ $bell->is_break ? 'text-center text-xs text-gray-400' : '' }}">
                                    @if ($bell->is_break)
                                        <span>—</span>
                                    @elseif ($slot)
                                        @php $color = $klassColors[$slot->klass_id] ?? 'indigo'; @endphp
                                        <div class="rounded-lg bg-{{ $color }}-50 border border-{{ $color }}-200 p-2">
                                            <div class="font-bold text-sm text-{{ $color }}-800">{{ $slot->subject }}</div>
                                            <div class="text-xs text-gray-600 mt-1">
                                                {{ $slot->klass->academicLevel->name }} — {{ $slot->klass->name }}
                                            </div>
                                            @if ($slot->room)
                                                <div class="text-xs text-gray-400 mt-0.5">اتاق: {{ $slot->room }}</div>
                                            @endif

                                            @if ($slot->bbb_meeting_id)
                                                <a href="{{ route('my-schedule.join', $slot) }}"
                                                   class="mt-2 block text-center bg-green-600 text-white text-xs px-2 py-1.5 rounded-lg hover:bg-green-700">
                                                    🎓 ورود به کلاس
                                                </a>
                                                <button type="button"
                                                        onclick="copyStudentLink('{{ route('guest.join.show', $slot->bbb_meeting_id) }}', this)"
                                                        class="mt-1 w-full text-center bg-gray-100 text-gray-700 text-xs px-2 py-1.5 rounded-lg hover:bg-gray-200">
                                                    📋 کپی لینک دانش‌آموزان
                                                </button>
                                            @endif

                                            @if ($slot->klass?->moodle_course_id)
                                                <a href="{{ route('moodle.sso', $slot) }}"
                                                   target="_blank"
                                                   class="mt-1 block text-center bg-orange-600 text-white text-xs px-2 py-1.5 rounded-lg hover:bg-orange-700">
                                                    📚 محتوای درس
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <div class="w-full h-full min-h-[50px] rounded-lg border-2 border-dashed border-gray-100"></div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function copyStudentLink(url, btn) {
    const original = btn.textContent;
    const showSuccess = () => {
        btn.textContent = '✓ کپی شد!';
        btn.classList.add('bg-green-100', 'text-green-700');
        setTimeout(() => {
            btn.textContent = original;
            btn.classList.remove('bg-green-100', 'text-green-700');
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
