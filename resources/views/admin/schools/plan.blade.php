@extends('layouts.app')

@section('title', 'تنظیم پلن ' . $school->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">تنظیم پلن — {{ $school->name }}</h2>
        <a href="{{ route('admin.schools') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- آمار فعلی --}}
    <div class="bg-blue-50 rounded-2xl p-5 mb-6">
        <h3 class="font-bold text-sm text-blue-900 mb-3">آمار فعلی</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-blue-600">کاربران فعلی</p>
                <p class="font-bold">{{ $school->current_users_count }} از {{ $school->plan_max_total_users }}</p>
            </div>
            <div>
                <p class="text-xs text-blue-600">جلسات هم‌زمان</p>
                <p class="font-bold">{{ $school->concurrent_sessions_count }} از {{ $school->plan_max_concurrent }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.schools.plan.update', $school) }}"
          class="bg-white rounded-2xl shadow-sm border p-6 md:p-8 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium mb-2">
                🔢 حداکثر کاربر کل مدرسه
            </label>
            <input type="number" name="plan_max_total_users" min="1" max="100000"
                   value="{{ old('plan_max_total_users', $school->plan_max_total_users) }}" required
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <p class="text-xs text-gray-500 mt-1">مجموع دانش‌آموزان و معلمان</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">
                👥 حداکثر دانش‌آموز هر کلاس
            </label>
            <input type="number" name="plan_max_users_per_class" min="1" max="1000"
                   value="{{ old('plan_max_users_per_class', $school->plan_max_users_per_class) }}" required
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <p class="text-xs text-gray-500 mt-1">هر کلاس حداکثر چند دانش‌آموز داشته باشه</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">
                📹 حداکثر دوربین هم‌زمان هر کلاس
            </label>
            <input type="number" name="plan_max_cameras" min="0" max="100"
                   value="{{ old('plan_max_cameras', $school->plan_max_cameras) }}" required
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <p class="text-xs text-gray-500 mt-1">تعداد دوربین‌هایی که هم‌زمان می‌تونن روشن باشن</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">
                🎓 حداکثر کلاس هم‌زمان
            </label>
            <input type="number" name="plan_max_concurrent" min="1" max="200"
                   value="{{ old('plan_max_concurrent', $school->plan_max_concurrent) }}" required
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <p class="text-xs text-gray-500 mt-1">حداکثر کلاس‌هایی که هم‌زمان می‌تونن برگزار بشن</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit"
                    class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
                ذخیره پلن
            </button>
            <a href="{{ route('admin.schools') }}"
               class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200 text-center">
                انصراف
            </a>
        </div>
    </form>
</div>
@endsection
