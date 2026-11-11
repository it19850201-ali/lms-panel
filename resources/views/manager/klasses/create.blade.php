@extends('layouts.app')

@section('title', $classLabel . ' جدید')

@section('content')
<div class="max-w-md mx-auto">
    <h2 class="text-2xl font-bold mb-8">{{ $classLabel }} جدید</h2>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    @if ($levels->isEmpty())
        <div class="bg-yellow-50 text-yellow-700 rounded-lg p-4 mb-6 text-sm">
            اول باید یه {{ $levelLabel }} بسازی. <a href="{{ route('manager.levels.create') }}" class="underline">ساخت {{ $levelLabel }}</a>
        </div>
    @else
        <form method="POST" action="{{ route('manager.klasses.store') }}"
              class="bg-white rounded-2xl shadow-sm border p-8 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-2">{{ $levelLabel }} / گروه</label>
                <select name="academic_level_id"
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
                <label class="block text-sm font-medium mb-2">نام {{ $classLabel }}</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="مثلاً {{ $isUni ? 'گروه A' : '۷۰۱' }}"
                       class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition">
                ذخیره
            </button>
        </form>
    @endif
</div>
@endsection
