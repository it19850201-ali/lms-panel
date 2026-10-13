@extends('layouts.app')

@section('title', 'ویرایش کلاس')

@section('content')
<div class="max-w-md mx-auto">
    <h2 class="text-2xl font-bold mb-8">ویرایش کلاس</h2>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('manager.klasses.update', $klass) }}"
          class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium mb-2">پایه / گروه</label>
            <select name="academic_level_id"
                    class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach ($levels as $level)
                    <option value="{{ $level->id }}" {{ old('academic_level_id', $klass->academic_level_id) == $level->id ? 'selected' : '' }}>
                        {{ $level->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">نام کلاس</label>
            <input type="text" name="name" value="{{ old('name', $klass->name) }}"
                   class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700">
                ذخیره تغییرات
            </button>
            <a href="{{ route('manager.klasses') }}"
               class="px-6 bg-gray-100 text-gray-700 rounded-lg py-3 font-medium hover:bg-gray-200">
                انصراف
            </a>
        </div>
    </form>
</div>
@endsection
