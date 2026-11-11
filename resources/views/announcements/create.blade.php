@extends('layouts.app')

@section('title', 'اطلاعیه جدید')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">اطلاعیه جدید</h2>
        <a href="{{ route('announcements.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('announcements.store') }}" class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">عنوان</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="مثلاً: تعطیلی روز پنجشنبه" class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">متن اطلاعیه</label>
            <textarea name="body" rows="5" required class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('body') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">مخاطب</label>
            <div class="space-y-2">
                @if (auth()->user()->isAdmin() || auth()->user()->isManager())
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="audience_type" value="all" {{ old('audience_type', 'all') === 'all' ? 'checked' : '' }} onchange="toggleKlass(this.value)">
                        <span class="text-sm">همه‌ی {{ $orgLabel }}</span>
                    </label>
                @endif

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="audience_type" value="class" {{ old('audience_type', auth()->user()->isTeacher() ? 'class' : '') === 'class' ? 'checked' : '' }} onchange="toggleKlass(this.value)">
                    <span class="text-sm">یک {{ $classLabel }} خاص</span>
                </label>

                @if (auth()->user()->isAdmin() || auth()->user()->isManager())
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="audience_type" value="teachers" {{ old('audience_type') === 'teachers' ? 'checked' : '' }} onchange="toggleKlass(this.value)">
                        <span class="text-sm">فقط {{ $teacherLabel }}ان</span>
                    </label>
                @endif
            </div>

            @if (auth()->user()->isTeacher())
                <p class="text-xs text-gray-500 mt-2">ℹ️ شما فقط می‌توانید برای {{ $classLabel }}‌هایی که در آن‌ها درس می‌دهید اطلاعیه منتشر کنید</p>
            @endif
        </div>

        <div id="klass-wrapper" style="display: none;">
            <label class="block text-sm font-medium mb-2">{{ $classLabel }}</label>
            <select name="klass_id" class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">— انتخاب کنید —</option>
                @foreach ($klasses as $k)
                    <option value="{{ $k->id }}" {{ old('klass_id') == $k->id ? 'selected' : '' }}>{{ $k->academicLevel->name }} — {{ $k->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">اولویت</label>
            <select name="priority" required class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>📢 عادی</option>
                <option value="important" {{ old('priority') === 'important' ? 'selected' : '' }}>⚠️ مهم</option>
                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>🚨 فوری</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">تاریخ انقضا (اختیاری)</label>
            <input type="text" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" placeholder="۱۴۰۵/۰۷/۱۵" dir="ltr" autocomplete="off" class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">انتشار اطلاعیه</button>
            <a href="{{ route('announcements.index') }}" class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200 text-center">انصراف</a>
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
function toggleKlass(val) { document.getElementById('klass-wrapper').style.display = val === 'class' ? 'block' : 'none'; }
toggleKlass(document.querySelector('input[name="audience_type"]:checked')?.value || 'all');
(function() {
    var tryInit = function() {
        if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.pDatepicker === 'function') {
            window.jQuery('#expires_at').pDatepicker({ format: 'YYYY/MM/DD HH:mm', initialValue: false, autoClose: true, timePicker: { enabled: true, meridiem: { enabled: false } }, persianDigit: true, observer: true });
        }
    };
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', tryInit); } else { tryInit(); }
})();
</script>
@endpush
