@extends('layouts.app')

@section('title', 'کاربران')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold mb-1">مدیریت کاربران</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت به پنل</a>
        </div>
        <form method="POST" action="{{ route('admin.users.bulk-reset-password') }}"
              onsubmit="return confirm('آیا مطمئنید؟ رمز همه کاربران انتخاب‌شده به کد ملی برمی‌گردد.');"
              class="flex gap-2 items-center">
            @csrf
            <select name="scope" class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option value="all">همه کاربران</option>
                <option value="role">فقط یک نقش</option>
            </select>
            <select name="role" class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option value="manager">مدیران</option>
                <option value="teacher">معلمان</option>
                <option value="student">دانش‌آموزان</option>
            </select>
            <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-700 whitespace-nowrap">
                🔄 ریست گروهی رمزها
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="bg-white rounded-2xl border p-4 mb-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[150px]">
            <label class="block text-xs text-gray-500 mb-1">جستجو</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="نام یا موبایل یا کد ملی"
                   class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">مدرسه</label>
            <select name="school_id" class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option value="">همه</option>
                @foreach ($schools as $s)
                    <option value="{{ $s->id }}" {{ request('school_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">نقش</label>
            <select name="role" class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option value="">همه</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>ادمین</option>
                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>مدیر</option>
                <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>معلم</option>
                <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>دانش‌آموز</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">وضعیت</label>
            <select name="status" class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option value="">همه</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>تایید‌شده</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>در انتظار</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>رد‌شده</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            فیلتر
        </button>
        <a href="{{ route('admin.users') }}" class="text-gray-500 px-3 py-2 text-sm hover:text-gray-700">پاک کردن</a>
    </form>

    @if ($users->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            کاربری یافت نشد.
        </div>
    @else
        <div class="bg-white rounded-2xl border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">نام</th>
                        <th class="px-4 py-3 text-right">موبایل</th>
                        <th class="px-4 py-3 text-right">کد ملی</th>
                        <th class="px-4 py-3 text-right">نقش</th>
                        <th class="px-4 py-3 text-right">مدرسه</th>
                        <th class="px-4 py-3 text-right">وضعیت</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr class="border-b last:border-b-0 hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $u->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                            <td class="px-4 py-3" dir="ltr">{{ $u->mobile }}</td>
                            <td class="px-4 py-3 text-gray-500" dir="ltr">{{ $u->code_melli }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full
                                    @if ($u->role === 'admin') bg-red-100 text-red-700
                                    @elseif ($u->role === 'manager') bg-purple-100 text-purple-700
                                    @elseif ($u->role === 'teacher') bg-green-100 text-green-700
                                    @else bg-blue-100 text-blue-700
                                    @endif">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $u->school?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs
                                    @if ($u->status === 'approved') text-green-600
                                    @elseif ($u->status === 'pending') text-yellow-600
                                    @else text-red-600
                                    @endif">
                                    {{ $u->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-left">
                                <div class="flex gap-1 justify-end">
                                    @if (!$u->isAdmin())
                                        <form method="POST" action="{{ route('admin.users.reset-password', $u) }}"
                                              onsubmit="return confirm('رمز {{ $u->name }} به کد ملی برگرده؟');">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs border border-indigo-200 text-indigo-600 px-2 py-1 rounded hover:bg-orange-50">
                                                🔑 ریست رمز
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.impersonate', $u) }}"
                                              onsubmit="return confirm('به اکانت {{ $u->name }} وارد می‌شوید؟');">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs border border-indigo-200 text-indigo-600 px-2 py-1 rounded hover:bg-indigo-50">
                                                👤 ورود
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
