@extends('layouts.app')

@section('title', 'مدرسه جدید')

@section('content')
<div class="max-w-xl mx-auto">
    <h2 class="text-2xl font-bold mb-8">ساخت مدرسه جدید</h2>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.schools.store') }}"
          class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2">نام مدرسه / دانشکده</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">نوع</label>
            <select name="type" class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="school" {{ old('type') === 'school' ? 'selected' : '' }}>مدرسه</option>
                <option value="college" {{ old('type') === 'college' ? 'selected' : '' }}>دانشکده / دانشگاه</option>
                <option value="institute" {{ old('type') === 'institute' ? 'selected' : '' }}>آموزشگاه</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">شهر (اختیاری)</label>
            <input type="text" name="city" value="{{ old('city') }}"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">تلفن (اختیاری)</label>
            <input type="text" name="phone" value="{{ old('phone') }}" dir="ltr"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">آدرس (اختیاری)</label>
            <textarea name="address" rows="2"
                      class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address') }}</textarea>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
            ساخت مدرسه
        </button>
    </form>
</div>
@endsection
