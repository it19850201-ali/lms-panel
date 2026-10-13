@extends('layouts.app')

@section('title', 'کلاس فوق‌برنامه جدید')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">⚡ کلاس فوق‌برنامه جدید</h2>
        <a href="{{ route('manager.extra-class.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('manager.extra-class.store') }}"
          class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">عنوان جلسه</label>
            <input type="text" name="subject" value="{{ old('subject') }}" required
                   placeholder="مثلاً: حل تمرین ریاضی فصل ۳"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">استاد</label>
            <select name="teacher_id" required
                    class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">— انتخاب کنید —</option>
                @foreach ($teachers as $t)
                    <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">فقط استادهای تاییدشده‌ی مدرسه</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">مخاطب</label>
            <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="scope" value="single" {{ old('scope', 'single') === 'single' ? 'checked' : '' }}
                           onchange="toggleKlass(this.value)">
                    <span class="text-sm">فقط یک کلاس</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="scope" value="all" {{ old('scope') === 'all' ? 'checked' : '' }}
                           onchange="toggleKlass(this.value)">
                    <span class="text-sm">همه‌ی دانش‌آموزان مدرسه</span>
                </label>
            </div>
        </div>

        <div id="klass-wrapper">
            <label class="block text-sm font-medium mb-2">کلاس</label>
            <select name="klass_id"
                    class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">— انتخاب کنید —</option>
                @foreach ($klasses as $k)
                    <option value="{{ $k->id }}" {{ old('klass_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->academicLevel->name }} — {{ $k->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">تاریخ</label>
                <input type="text" name="date" id="date" value="{{ old('date') }}" required
                       placeholder="۱۴۰۵/۰۷/۱۵" dir="ltr" autocomplete="off"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">ساعت شروع</label>
                <input type="text" name="start_time" value="{{ old('start_time', '16:00') }}" required
                       placeholder="16:00" dir="ltr"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">ساعت پایان</label>
                <input type="text" name="end_time" value="{{ old('end_time', '17:30') }}" required
                       placeholder="17:30" dir="ltr"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">توضیحات (اختیاری)</label>
            <textarea name="description" rows="2"
                      class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
                ساخت فوق‌برنامه
            </button>
            <a href="{{ route('manager.extra-class.index') }}"
               class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200">
                انصراف
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
<script>
function toggleKlass(scope) {
    document.getElementById('klass-wrapper').style.display = scope === 'single' ? 'block' : 'none';
}
toggleKlass(document.querySelector('input[name="scope"]:checked')?.value || 'single');

(function() {
    var tryInit = function() {
        if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.pDatepicker === 'function') {
            window.jQuery('#date').pDatepicker({
                format: 'YYYY/MM/DD',
                initialValue: false,
                autoClose: true,
                persianDigit: true,
                observer: true
            });
        }
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryInit);
    } else {
        tryInit();
    }
})();
</script>
@endpush
