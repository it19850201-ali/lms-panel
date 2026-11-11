@extends('layouts.app')

@section('title', 'گزارش حضور و غیاب')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-1">گزارش حضور و غیاب</h2>
            <a href="{{ route('manager.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به پنل</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('manager.attendance.close-stale') }}" onsubmit="return confirm('همه‌ی رکوردهای باز فعلی بسته شوند؟');">
                @csrf
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
                    🔒 بستن رکوردهای باز
                </button>
            </form>
            <a href="{{ route('manager.attendance.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">
                📥 خروجی اکسل
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif

    <form method="GET" class="bg-white rounded-2xl border p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">تاریخ</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">وضعیت</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">همه</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>در حال حضور</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>خارج‌شده</option>
                </select>
            </div>
            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 flex-1 md:flex-none">فیلتر</button>
                <a href="{{ route('manager.attendance') }}" class="text-gray-500 px-4 py-2 text-sm hover:text-gray-700 text-center border rounded-lg md:border-0">پاک کردن</a>
            </div>
        </div>
    </form>

    @if ($logs->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هیچ رکوردی یافت نشد.
        </div>
    @else
        <div class="md:hidden space-y-3">
            @foreach ($logs as $log)
                @php $slot = $log->scheduleSlot; @endphp
                <div class="bg-white rounded-2xl border p-4 {{ $log->isPresent() ? 'border-r-4 border-r-green-500' : '' }}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-bold text-sm">{{ $log->full_name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if ($log->isModerator())
                                    <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs">{{ $teacherLabel }}</span>
                                @else
                                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">{{ $studentLabel }}</span>
                                @endif
                            </p>
                        </div>
                        @if ($log->isPresent())
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">حاضر</span>
                        @else
                            <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">خارج</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 mt-3 pt-3 border-t">
                        <div><span class="text-gray-400">{{ $classLabel }}: </span> @if ($slot && $slot->klass) {{ $slot->klass->academicLevel->name }} — {{ $slot->klass->name }} @else — @endif</div>
                        <div><span class="text-gray-400">{{ $isUni ? 'ساعت' : 'زنگ' }}: </span> @if ($slot && $slot->bell) {{ $slot->bell->name }} @elseif ($slot && $slot->is_extra) فوق‌برنامه @else — @endif</div>
                        <div><span class="text-gray-400">تاریخ: </span><span dir="ltr">{{ $log->session_date->format('Y-m-d') }}</span></div>
                        <div><span class="text-gray-400">ورود: </span><span dir="ltr">{{ $log->first_joined_at?->format('H:i') ?? '—' }}</span></div>
                        <div><span class="text-gray-400">خروج: </span><span dir="ltr">{{ $log->left_at?->format('H:i') ?? '—' }}</span></div>
                        <div><span class="text-gray-400">مدت: </span><span class="font-bold text-indigo-600">{{ $log->calculateDuration() }} دقیقه</span></div>
                    </div>
                    <div class="mt-3 pt-3 border-t text-left">
                        <a href="{{ route('manager.attendance.show', $log) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">مشاهده جزئیات ←</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hidden md:block bg-white rounded-2xl border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">تاریخ</th>
                        <th class="px-4 py-3 text-right">{{ $classLabel }}</th>
                        <th class="px-4 py-3 text-right">{{ $isUni ? 'ساعت' : 'زنگ' }}</th>
                        <th class="px-4 py-3 text-right">نام</th>
                        <th class="px-4 py-3 text-right">نقش</th>
                        <th class="px-4 py-3 text-right">ورود</th>
                        <th class="px-4 py-3 text-right">خروج</th>
                        <th class="px-4 py-3 text-right">مدت</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        @php $slot = $log->scheduleSlot; @endphp
                        <tr class="border-b last:border-b-0 hover:bg-gray-50 {{ $log->isPresent() ? 'bg-green-50/30' : '' }}">
                            <td class="px-4 py-3 text-gray-400">{{ $log->id }}</td>
                            <td class="px-4 py-3" dir="ltr">{{ $log->session_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">@if ($slot && $slot->klass) {{ $slot->klass->academicLevel->name }} — {{ $slot->klass->name }} @else — @endif</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">@if ($slot && $slot->bell) {{ $slot->bell->name }} @elseif ($slot && $slot->is_extra) فوق‌برنامه @endif</td>
                            <td class="px-4 py-3 font-medium">{{ $log->full_name }}</td>
                            <td class="px-4 py-3">
                                @if ($log->isModerator())
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full">{{ $teacherLabel }}</span>
                                @else
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">{{ $studentLabel }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500" dir="ltr">{{ $log->first_joined_at?->format('H:i') }}</td>
                            <td class="px-4 py-3 text-xs" dir="ltr">@if ($log->left_at) <span class="text-gray-500">{{ $log->left_at->format('H:i') }}</span> @else <span class="text-green-600 font-medium">حاضر</span> @endif</td>
                            <td class="px-4 py-3 text-xs">@if ($log->left_at) <span class="font-medium">{{ $log->calculateDuration() }} دقیقه</span> @else <span class="text-green-600">{{ $log->calculateDuration() }}+</span> @endif</td>
                            <td class="px-4 py-3 text-left"><a href="{{ route('manager.attendance.show', $log) }}" class="text-indigo-600 hover:text-indigo-800 text-xs">جزئیات</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
