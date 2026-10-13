@extends('layouts.app')

@section('title', 'زنگ جدید')

@section('content')
<div class="max-w-md mx-auto">
    <h2 class="text-2xl font-bold mb-8">زنگ جدید</h2>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('manager.bells.store') }}"
          class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">نام زنگ</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   placeholder="مثلاً زنگ اول یا تفریح اول"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">ساعت شروع</label>
                <input type="text" name="start_time" value="{{ old('start_time', '07:30') }}"
                       placeholder="07:30" dir="ltr"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
                <p class="text-xs text-gray-400 mt-1">فرمت: HH:MM</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">ساعت پایان</label>
                <input type="text" name="end_time" value="{{ old('end_time', '08:15') }}"
                       placeholder="08:15" dir="ltr"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
                <p class="text-xs text-gray-400 mt-1">فرمت: HH:MM</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">ترتیب</label>
            <input type="number" name="order" value="{{ old('order', 0) }}" min="0" dir="ltr"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <p class="text-xs text-gray-400 mt-1">عدد کمتر = بالاتر در برنامه. مثلاً زنگ اول=1</p>
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_break" value="1" {{ old('is_break') ? 'checked' : '' }}
                       class="w-4 h-4">
                <span class="text-sm">این زنگ تفریح است (درس نمی‌گیرد)</span>
            </label>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
            ذخیره
        </button>
    </form>
</div>
@endsection
