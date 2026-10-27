@extends('layouts.app')

@section('title', 'برنامه ' . $klass->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-1">
برنامه هفتگی: {{ $klass->academicLevel->name }} — کلاس {{ \App\Helpers\PersianHelper::digitsToPersian($klass->name) }}
            </h2>
            <a href="{{ route('manager.schedule') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت</a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    @if ($bells->isEmpty())
        <div class="bg-yellow-50 text-yellow-700 rounded-2xl p-6 text-sm">
            اول باید زنگ‌ها رو تعریف کنی. <a href="{{ route('manager.bells') }}" class="underline">تنظیمات زنگ‌ها</a>
        </div>
    @elseif ($teachers->isEmpty())
        <div class="bg-yellow-50 text-yellow-700 rounded-2xl p-6 text-sm">
            هیچ معلم تاییدشده‌ای در این مدرسه وجود ندارد.
        </div>
    @else
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
                                    $slot = $slots->get($day . '-' . $bell->id);
                                @endphp

                                <td class="px-2 py-2 border-l align-top {{ $bell->is_break ? 'text-center text-xs text-gray-400' : '' }}">
                                    @if ($bell->is_break)
                                        <span>—</span>
                                    @elseif ($slot)
                                        <div class="rounded-lg bg-indigo-50 border border-indigo-200 p-2 cursor-pointer hover:bg-indigo-100 slot-clickable"
                                             data-slot-id="{{ $slot->id }}"
                                             ...>
                                            <div class="font-bold text-sm">{{ $slot->subject }}</div>
                                            <div class="text-xs text-gray-600 mt-1">{{ $slot->teacher->name }}</div>
                                            @if ($slot->room)
                                                <div class="text-xs text-gray-400 mt-0.5">اتاق: {{ $slot->room }}</div>
                                            @endif
                                            <a href="{{ route('manager.schedule.join', $slot) }}"
                                               onclick="event.stopPropagation();"
                                               class="mt-2 block text-center bg-green-600 text-white text-xs px-2 py-1.5 rounded-lg hover:bg-green-700">
                                                ورود به کلاس
                                            </a>
                                        </div>
                                    @else
                                        <button type="button"
                                                class="w-full h-full min-h-[50px] rounded-lg border-2 border-dashed border-gray-200 hover:border-indigo-400 hover:bg-indigo-50/30 transition text-gray-400 hover:text-indigo-600 text-xs add-slot-btn"
                                                data-day="{{ $day }}"
                                                data-bell="{{ $bell->id }}"
                                                data-bell-name="{{ $bell->name }}"
                                                data-day-name="{{ $days[$day] }}">
                                            + افزودن
                                        </button>
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

<div id="slotModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <form id="slotForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="slotMethod" value="POST">
            <input type="hidden" name="klass_id" value="{{ $klass->id }}">
            <input type="hidden" name="day_of_week" id="modalDay">
            <input type="hidden" name="bell_id" id="modalBell">

            <div class="p-6 border-b">
                <h3 class="font-bold text-lg" id="modalTitle">افزودن درس</h3>
                <p class="text-sm text-gray-500 mt-1" id="modalSubtitle"></p>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">درس</label>
                    <input type="text" name="subject" id="modalSubject" required
                           placeholder="مثلاً ریاضی"
                           class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">معلم</label>
                    <select name="teacher_id" id="modalTeacher" required
                            class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— انتخاب کنید —</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">اتاق (اختیاری)</label>
                    <input type="text" name="room" id="modalRoom"
                           class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="p-6 border-t flex gap-3">
                <button type="submit"
                        class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700">
                    ذخیره
                </button>
                <button type="button" onclick="closeModal()"
                        class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200">
                    انصراف
                </button>
                <button type="button" id="deleteBtn"
                        class="hidden px-6 bg-red-50 text-red-600 rounded-lg py-3 font-medium hover:bg-red-100">
                    حذف
                </button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
const modal = document.getElementById('slotModal');
const form = document.getElementById('slotForm');
const modalDay = document.getElementById('modalDay');
const modalBell = document.getElementById('modalBell');
const modalSubject = document.getElementById('modalSubject');
const modalTeacher = document.getElementById('modalTeacher');
const modalRoom = document.getElementById('modalRoom');
const modalMethod = document.getElementById('slotMethod');
const modalTitle = document.getElementById('modalTitle');
const modalSubtitle = document.getElementById('modalSubtitle');
const deleteBtn = document.getElementById('deleteBtn');
const deleteForm = document.getElementById('deleteForm');

const storeUrl = '{{ route('manager.schedule.store') }}';
const updateBase = '{{ url('manager/schedule') }}';

document.querySelectorAll('.add-slot-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        form.action = storeUrl;
        modalMethod.value = 'POST';
        modalTitle.textContent = 'افزودن درس';
        modalSubtitle.textContent = this.dataset.dayName + ' — ' + this.dataset.bellName;
        modalDay.value = this.dataset.day;
        modalBell.value = this.dataset.bell;
        modalSubject.value = '';
        modalTeacher.value = '';
        modalRoom.value = '';
        deleteBtn.classList.add('hidden');
        modal.classList.remove('hidden');
        modalSubject.focus();
    });
});

document.querySelectorAll('.slot-clickable').forEach(el => {
    el.addEventListener('click', function() {
        const id = this.dataset.slotId;
        form.action = updateBase + '/' + id;
        modalMethod.value = 'PUT';
        modalTitle.textContent = 'ویرایش درس';
        modalSubtitle.textContent = this.dataset.slotTeacherName;
        modalSubject.value = this.dataset.slotSubject;
        modalTeacher.value = this.dataset.slotTeacherId;
        modalRoom.value = this.dataset.slotRoom || '';
        modalDay.value = this.dataset.slotDay;
        modalBell.value = this.dataset.slotBell;
        deleteBtn.classList.remove('hidden');
        deleteBtn.dataset.slotId = id;
        modal.classList.remove('hidden');
    });
});

deleteBtn.addEventListener('click', function() {
    if (!confirm('آیا مطمئنید که این درس حذف شود؟')) return;
    deleteForm.action = updateBase + '/' + this.dataset.slotId;
    deleteForm.submit();
});

function closeModal() {
    modal.classList.add('hidden');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
});

modal.addEventListener('click', e => {
    if (e.target === modal) closeModal();
});
</script>
@endpush
