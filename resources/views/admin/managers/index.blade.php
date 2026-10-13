@extends('layouts.app')

@section('title', 'مدیران')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold">مدیران مدارس</h2>
        <a href="{{ route('admin.managers.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + مدیر جدید
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif

    @if ($managers->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز مدیری ساخته نشده.
        </div>
    @else
        <div class="grid gap-3">
            @foreach ($managers as $m)
                <div class="bg-white rounded-2xl border p-6">
                    <h3 class="font-bold text-lg">{{ $m->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1" dir="ltr">{{ $m->mobile }}</p>
                    <p class="text-sm text-gray-500">
                        مدرسه: {{ $m->school?->name ?? '—' }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
