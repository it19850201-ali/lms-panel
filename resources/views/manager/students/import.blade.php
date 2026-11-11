@extends('layouts.app')

@section('title', 'ایمپورت ' . $studentLabel . 'ان')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-1">ایمپورت {{ $studentLabel }}ان از اکسل</h2>
        <a href="{{ route('manager.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به پنل</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    @if ($levels->isEmpty())
        <div class="bg-yellow-50 text-yellow-700 rounded-lg p-6 mb-6 text-sm">
            اول باید {{ $levelLabel }} و {{ $classLabel }} بسازی.
            <a href="{{ route('manager.levels.create') }}" class="underline">ساخت {{ $levelLabel }}</a>
        </div>
    @else
        <div class="bg-blue-50 text-blue-800 rounded-2xl p-6 mb-6 text-sm leading-7">
            <h3 class="font-bold mb-2">راهنما</h3>
            <ol class="list-decimal list-inside space-y-1">
                <li>{{ $levelLabel }} و {{ $classLabel }} مقصد را انتخاب کنید</li>
                <li>قالب اکسل را دانلود و پر کنید</li>
                <li>ستون‌ها: <strong>نام، نام خانوادگی، کد ملی، موبایل</strong></li>
                <li>همه‌ی {{ $studentLabel }}ان به {{ $classLabel }} انتخاب‌شده اضافه می‌شوند</li>
                <li>{{ $studentLabel }}ان تکراری (بر اساس کد ملی) فقط آپدیت می‌شوند</li>
            </ol>
        </div>

        <div class="bg-white rounded-2xl border p-6 mb-6 flex justify-between items-center">
            <div>
                <h3 class="font-bold">قالب اکسل</h3>
                <p class="text-sm text-gray-500 mt-1">فقط ۴ ستون ساده</p>
            </div>
            <a href="{{ route('manager.students.template') }}"
               class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200">
                دانلود قالب
            </a>
        </div>

        <form method="POST" action="{{ route('manager.students.import.store') }}"
              enctype="multipart/form-data"
              class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-2">{{ $levelLabel }}</label>
                <select name="academic_level_id" id="academic_level_id"
                        class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— انتخاب کنید —</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}" {{ old('academic_level_id') == $level->id ? 'selected' : '' }}>
                            {{ $level->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">{{ $classLabel }}</label>
                <select name="klass_id" id="klass_id" required
                        class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— ابتدا {{ $levelLabel }} را انتخاب کنید —</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">فایل اکسل</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">فرمت‌های مجاز: xlsx، xls، csv — حداکثر ۵ مگابایت</p>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
                شروع پردازش
            </button>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const levelSelect = document.getElementById('academic_level_id');
    const klassSelect = document.getElementById('klass_id');
    const apiUrl = '{{ route('manager.api.klasses') }}';

    levelSelect.addEventListener('change', function () {
        const levelId = this.value;
        klassSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';

        if (!levelId) {
            klassSelect.innerHTML = '<option value="">— ابتدا {{ $levelLabel }} را انتخاب کنید —</option>';
            return;
        }

        fetch(apiUrl + '?academic_level_id=' + encodeURIComponent(levelId), {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            klassSelect.innerHTML = '<option value="">— انتخاب کنید —</option>';
            if (data.length === 0) {
                klassSelect.innerHTML = '<option value="">هیچ {{ $classLabel }}ی در این {{ $levelLabel }} نیست</option>';
                return;
            }
            data.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.id;
                opt.textContent = k.name;
                klassSelect.appendChild(opt);
            });
        })
        .catch(() => {
            klassSelect.innerHTML = '<option value="">خطا در بارگذاری</option>';
        });
    });
});
</script>
@endpush
