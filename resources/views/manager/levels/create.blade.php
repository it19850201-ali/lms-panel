@extends('layouts.app')

@section('title', 'پایه جدید')

@section('content')
<div class="max-w-md mx-auto">
    <h2 class="text-2xl font-bold mb-8">پایه / گروه جدید</h2>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('manager.levels.store') }}"
          class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">نام پایه / گروه</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   placeholder="مثلاً هفتم یا کارشناسی کامپیوتر"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">ترتیب نمایش (اختیاری)</label>
            <input type="number" name="order" value="{{ old('order', 0) }}" min="0" dir="ltr"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <p class="text-xs text-gray-400 mt-1">عدد کمتر = بالاتر در لیست. مثلاً هفتم=1، هشتم=2</p>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
            ذخیره
        </button>
    </form>
</div>
@endsection
