@extends('layouts.app')

@section('title', 'ویرایش کلاس')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold">ویرایش کلاس</h2>
        <a href="{{ route('courses.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            ← بازگشت
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('courses.update', $course) }}"
          class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium mb-2">نام کلاس</label>
            <input type="text" name="title" value="{{ old('title', $course->title) }}"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">تاریخ شروع (اختیاری)</label>
                <input type="text" name="starts_at" id="starts_at"
                       value="{{ old('starts_at', $course->starts_at ? \App\Helpers\JalaliHelper::toJalali($course->starts_at) : '') }}"
                       placeholder="۱۴۰۵/۰۷/۰۴ ۱۰:۳۰" dir="ltr" autocomplete="off"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">خالی بذاری، از همین حالا فعاله</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">تاریخ پایان (اختیاری)</label>
                <input type="text" name="ends_at" id="ends_at"
                       value="{{ old('ends_at', $course->ends_at ? \App\Helpers\JalaliHelper::toJalali($course->ends_at) : '') }}"
                       placeholder="۱۴۰۵/۰۷/۱۵ ۱۲:۰۰" dir="ltr" autocomplete="off"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">خالی بذاری، هیچ‌وقت بسته نمی‌شه</p>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
                ذخیره تغییرات
            </button>
            <a href="{{ route('courses.index') }}"
               class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200 transition">
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
                    format: 'YYYY/MM/DD HH:mm',
                    initialValue: false,
                    autoClose: true,
                    timePicker: {
                        enabled: true,
                        meridiem: { enabled: false }
                    },
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
