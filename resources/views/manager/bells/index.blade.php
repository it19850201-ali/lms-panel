@extends('layouts.app')

@section('title', 'زنگ‌ها')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-1">زنگ‌های مدرسه</h2>
            <a href="{{ route('manager.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به پنل</a>
        </div>
        <a href="{{ route('manager.bells.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 text-center">
            + زنگ جدید
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <div class="bg-blue-50 text-blue-800 rounded-2xl p-4 mb-6 text-sm leading-6">
        این زنگ‌ها برای <strong>همه‌ی روزهای هفته</strong> یکسان اعمال می‌شوند.
    </div>

    @if ($bells->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز زنگی تعریف نشده.
        </div>
    @else
        {{-- موبایل --}}
        <div class="md:hidden space-y-3">
            @foreach ($bells as $bell)
                <div class="bg-white rounded-2xl border p-4 {{ $bell->is_break ? 'bg-yellow-50 border-yellow-200' : '' }}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-bold">{{ $bell->name }}</p>
                            @if ($bell->is_break)
                                <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-0.5 rounded-full mt-1 inline-block">تفریح</span>
                            @endif
                        </div>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">ترتیب: {{ $bell->order }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-sm mt-3 pt-3 border-t">
                        <div>
                            <span class="text-xs text-gray-400">شروع: </span>
                            <span dir="ltr" class="font-medium">{{ $bell->start_time_short }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400">پایان: </span>
                            <span dir="ltr" class="font-medium">{{ $bell->end_time_short }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-3 pt-3 border-t">
                        <a href="{{ route('manager.bells.edit', $bell) }}"
                           class="flex-1 border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg text-xs hover:bg-gray-50 text-center">
                            ویرایش
                        </a>
                        <form method="POST" action="{{ route('manager.bells.destroy', $bell) }}"
                              onsubmit="return confirm('آیا مطمئنید؟');" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full border border-red-200 text-red-600 px-3 py-1.5 rounded-lg text-xs hover:bg-red-50">
                                حذف
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- دسکتاپ --}}
        <div class="hidden md:block bg-white rounded-2xl border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">نام</th>
                        <th class="px-4 py-3 text-right">شروع</th>
                        <th class="px-4 py-3 text-right">پایان</th>
                        <th class="px-4 py-3 text-right">ترتیب</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bells as $bell)
                        <tr class="border-b last:border-b-0 {{ $bell->is_break ? 'bg-yellow-50' : '' }}">
                            <td class="px-4 py-3">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium">
                                {{ $bell->name }}
                                @if ($bell->is_break)
                                    <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-0.5 rounded-full mr-2">تفریح</span>
                                @endif
                            </td>
                            <td class="px-4 py-3" dir="ltr">{{ $bell->start_time_short }}</td>
                            <td class="px-4 py-3" dir="ltr">{{ $bell->end_time_short }}</td>
                            <td class="px-4 py-3">{{ $bell->order }}</td>
                            <td class="px-4 py-3 text-left">
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('manager.bells.edit', $bell) }}"
                                       class="border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg text-xs hover:bg-gray-50">
                                        ویرایش
                                    </a>
                                    <form method="POST" action="{{ route('manager.bells.destroy', $bell) }}"
                                          onsubmit="return confirm('آیا مطمئنید؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="border border-red-200 text-red-600 px-3 py-1.5 rounded-lg text-xs hover:bg-red-50">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
