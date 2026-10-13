@extends('layouts.app')

@section('title', 'ویرایش زنگ')

@section('content')
<div class="max-w-md mx-auto">
    <h2 class="text-2xl font-bold mb-8">ویرایش زنگ</h2>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('manager.bells.update', $bell) }}"
          class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium mb-2">نام زنگ</label>
            <input type="text" name="name" value="{{ old('name', $bell->name) }}"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">ساعت شروع</label>
                <input type="text" name="start_time" value="{{ old('start_time', $bell->start_time_short) }}"
                       dir="ltr"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">ساعت پایان</label>
                <input type="text" name="end_time" value="{{ old('end_time', $bell->end_time_short) }}"
                       dir="ltr"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">ترتیب</label>
            <input type="number" name="order" value="{{ old('order', $bell->order) }}" min="0" dir="ltr"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_break" value="1" {{ old('is_break', $bell->is_break) ? 'checked' : '' }}
                       class="w-4 h-4">
                <span class="text-sm">این زنگ تفریح است</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
                ذخیره تغییرات
            </button>
            <a href="{{ route('manager.bells') }}"
               class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200 transition">
                انصراف
            </a>
        </div>
    </form>
</div>
@endsection
