<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'سامانه آموزش مجازی')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .app-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            padding: 10px 0;
            font-size: 12px;
            color: #9ca3af;
            z-index: 50;
        }
        body {
            padding-bottom: 45px;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800" style="font-family: Vazirmatn, sans-serif;">

    {{-- Impersonate Banner --}}
    @if (session('impersonator_id'))
        <div style="background: #f97316; color: white; padding: 12px 16px; text-align: center; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 12px;">
            <span>🔍 شما در حالت مشاهده اکانت <strong>{{ auth()->user()->name }}</strong> هستید</span>
            <form method="POST" action="{{ route('admin.stop-impersonate') }}" style="display: inline;">
                @csrf
                <button type="submit" style="background: white; color: #c2410c; padding: 6px 12px; border-radius: 6px; font-weight: bold; border: none; cursor: pointer;">
                    بازگشت به ادمین
                </button>
            </form>
        </div>
    @endif

    {{-- هشدار تغییر رمز --}}
    @auth
        @if (auth()->user()->shouldChangePassword() && !request()->routeIs('profile.*'))
            <div class="bg-yellow-50 border-b border-yellow-200 text-yellow-800 px-4 py-2 text-center text-sm">
                ⚠️ رمز عبور شما همان کد ملی است.
                <a href="{{ route('profile.show') }}#change-password" class="font-bold underline">
                    همین حالا رمز خود را تغییر دهید
                </a>
            </div>
        @endif
    @endauth

    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center flex-wrap gap-2">
            <a href="{{ url('/') }}" class="text-lg font-bold text-indigo-600">
                سامانه آموزش مجازی
            </a>

            @auth
                <div class="flex items-center gap-3 flex-wrap">

                    {{-- دانش‌آموز --}}
                    @if (auth()->user()->isStudent())
                        <a href="{{ route('student.home') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600 font-medium">
                            خانه‌ی من
                        </a>
                        <a href="{{ route('my-schedule') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600">
                            برنامه من
                        </a>
                        <a href="{{ route('my-attendance') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600">
                            حضور من
                        </a>
                        <a href="{{ route('materials') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600">
                            منابع کلاس
                        </a>
                    @endif

                    {{-- معلم --}}
                    @if (auth()->user()->isTeacher())
                        <a href="{{ route('my-schedule') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600 font-medium">
                            برنامه من
                        </a>
                        <a href="{{ route('my-attendance') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600">
                            حضور من
                        </a>
                        <a href="{{ route('materials') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600">
                            منابع کلاس
                        </a>
                    @endif

                    {{-- ادمین --}}
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600 font-medium">
                            پنل مدیر کل
                        </a>
                        <a href="{{ route('admin.users') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600">
                            کاربران
                        </a>
                    @endif

                    {{-- مدیر --}}
                    @if (auth()->user()->isManager())
                        <a href="{{ route('manager.dashboard') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600 font-medium">
                            پنل مدیر
                        </a>
                    @endif

                    {{-- پروفایل --}}
                    <a href="{{ route('profile.show') }}"
                       class="text-sm text-gray-600 hover:text-indigo-600">
                        پروفایل
                    </a>
                    <a href="{{ route('announcements.feed') }}"
                       class="text-sm text-gray-600 hover:text-indigo-600">
                        اطلاعیه‌ها
                    </a>
                    @if (auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isTeacher())
                        <a href="{{ route('announcements.index') }}"
                           class="text-sm text-gray-600 hover:text-indigo-600">
                            مدیریت اطلاعیه‌ها
                        </a>
                    @endif

                    {{-- خروج --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600">
                            خروج
                        </button>
                    </form>
                </div>
            @else
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-indigo-600">
                        ورود
                    </a>
                    <a href="{{ route('register') }}"
                       class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        ثبت‌نام معلم
                    </a>
                </div>
            @endauth
        </div>
    </nav>

    <main class="py-8 px-4">
        @yield('content')
    </main>
    <footer class="app-footer">
        © {{ date('Y') }} سامانه آموزش مجازی — طراحی و توسعه: علی مرادیان
    </footer>
    @stack('scripts')
</body>
</html>
