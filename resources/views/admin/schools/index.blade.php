@extends('layouts.app')

@section('title', 'مدیریت مدارس')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold">مدارس</h2>
        <a href="{{ route('admin.schools.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + مدرسه جدید
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif

    @if ($schools->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز مدرسه‌ای ساخته نشده.
        </div>
    @else
        <div class="grid gap-3">
            @foreach ($schools as $school)
                <div class="bg-white rounded-2xl border p-5 md:p-6">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
                        <div class="flex-1">
                            <h3 class="font-bold text-lg">{{ $school->name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                نوع: 
                                @if ($school->type === 'school') مدرسه
                                @elseif ($school->type === 'college') دانشکده
                                @else آموزشگاه
                                @endif
                                @if ($school->city) · {{ $school->city }} @endif
                                · {{ $school->users_count }} کاربر
                            </p>
                            @if ($school->plan_max_total_users)
                                <p class="text-xs text-purple-600 mt-1">
                                    ⚙️ پلن: {{ $school->plan_max_total_users }} کاربر · {{ $school->plan_max_concurrent }} کلاس هم‌زمان
                                </p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.schools.plan.edit', $school) }}"
                               class="bg-purple-100 text-purple-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-purple-200 whitespace-nowrap">
                                ⚙️ پلن
                            </a>
                            <a href="{{ route('admin.schools.edit', $school) }}"
                               class="border border-gray-300 text-gray-600 px-3 py-2 rounded-lg text-xs font-medium hover:bg-gray-50 whitespace-nowrap">
                                ویرایش
                            </a>
                            @if (!$school->is_active)
                                <span class="text-xs bg-gray-100 text-gray-500 px-3 py-2 rounded-full">غیرفعال</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
