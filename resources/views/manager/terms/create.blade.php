@extends('layouts.app')

@section('title', 'ترم جدید')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">ترم جدید</h2>
        <a href="{{ route('manager.terms') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('manager.terms.store') }}"
          class="bg-white rounded-2xl shadow-sm border p-6 md:p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">نام ترم</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   placeholder="مثلاً پاییز ۱۴۰۵ یا نیم‌سال اول ۱۴۰۵"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">تاریخ شروع</label>
                <input type="text" name="starts_at" id="starts_at" value="{{ old('starts_at') }}"
                       placeholder="۱۴۰۵/۰۷/۰۱" dir="ltr" required autocomplete="off"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">تاریخ پایان</label>
                <input type="text" name="ends_at" id="ends_at" value="{{ old('ends_at') }}"
                       placeholder="۱۴۰۵/۱۰/۳۰" dir="ltr" required autocomplete="off"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
            </div>
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                       class="w-4 h-4">
                <span class="text-sm">این ترم فعال باشد (بقیه ترم‌ها غیرفعال می‌شوند)</span>
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700">
                ذخیره
            </button>
            <a href="{{ route('manager.terms') }}"
               class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200 text-center">
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
(function() {
    var tryInit = function() {
        if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.pDatepicker === 'function') {
            window.jQuery('#starts_at, #ends_at').pDatepicker({
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
