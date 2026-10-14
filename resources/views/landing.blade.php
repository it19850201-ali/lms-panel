<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <title>سامانه آموزش مجازی</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            min-height: 100vh;
            font-family: Vazirmatn, -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1f2937;
            -webkit-font-smoothing: antialiased;
        }

        body {
            background-color: #f9fafb;
            background-image: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.08) 0px, transparent 50%);
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        a { text-decoration: none; color: inherit; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; width: 100%; }

        /* Navbar */
        .navbar {
            padding: 16px 0;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-inner { display: flex; justify-content: space-between; align-items: center; gap: 16px; }

        .logo { display: flex; align-items: center; gap: 12px; font-size: 18px; font-weight: 800; color: #111827; }

        .logo-icon {
            width: 42px; height: 42px; border-radius: 12px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 20px -4px rgba(139, 92, 246, 0.4);
        }

        .nav-links { display: flex; align-items: center; gap: 8px; }

        .nav-link {
            color: #4b5563; font-size: 14px; font-weight: 500;
            padding: 10px 18px; border-radius: 10px;
            background: none; border: none; cursor: pointer;
            font-family: inherit; transition: all 0.2s;
        }
        .nav-link:hover { color: #6366f1; background: #f3f4f6; }

        .nav-btn {
            color: white; padding: 10px 22px; border-radius: 10px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            box-shadow: 0 4px 14px -4px rgba(139, 92, 246, 0.5);
            border: none; font-family: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .nav-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 20px -4px rgba(139, 92, 246, 0.7); }

        /* Hero */
        .hero {
            flex: 1; display: flex; align-items: center; justify-content: center;
            text-align: center; padding: 80px 0 60px;
        }

        .hero-content { max-width: 900px; margin: 0 auto; }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(139, 92, 246, 0.08);
            border: 1px solid rgba(139, 92, 246, 0.15);
            color: #6d28d9; font-size: 13px; font-weight: 600;
            padding: 8px 20px; border-radius: 999px; margin-bottom: 32px;
        }

        .hero-badge-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #10b981; box-shadow: 0 0 8px #10b981;
            animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        .hero-title {
            font-size: clamp(38px, 6vw, 76px);
            font-weight: 900;
            line-height: 1.2;
            letter-spacing: -0.03em;
            margin-bottom: 24px;
            color: #111827;
        }

        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: clamp(15px, 1.6vw, 18px);
            color: #6b7280; line-height: 1.9;
            max-width: 680px; margin: 0 auto 48px;
        }

        /* Stats */
        .stats-row {
            display: flex; justify-content: center;
            gap: 56px; margin-bottom: 56px; flex-wrap: wrap;
        }

        .stat-item { text-align: center; }

        .stat-value {
            font-size: 32px; font-weight: 800;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label { font-size: 13px; color: #9ca3af; margin-top: 6px; }

        /* Features */
        .features-section { padding: 20px 0 40px; }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        @media (max-width: 900px) { .features-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 520px) {
            .features-grid { grid-template-columns: 1fr; }
            .stats-row { gap: 24px; }
            .hero { padding: 40px 0 30px; }
            .navbar { padding: 12px 0; }
            .logo span { font-size: 16px; }
            .logo-icon { width: 36px; height: 36px; font-size: 17px; }
            .nav-link { padding: 8px 12px; font-size: 13px; }
            .nav-btn { padding: 8px 16px; font-size: 13px; }
            .stat-value { font-size: 26px; }
            .feature-card { padding: 22px 18px; }
        }

        .feature-card {
            padding: 28px 22px; border-radius: 18px;
            background: white;
            border: 1px solid #e5e7eb;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            border-color: #c7d2fe;
            box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.25);
        }

        .feature-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px; font-size: 22px;
            background: linear-gradient(135deg, #eef2ff, #f5f3ff);
            border: 1px solid #e0e7ff;
        }

        .feature-card h3 { font-size: 15px; font-weight: 700; margin-bottom: 8px; color: #111827; }
        .feature-card p { font-size: 13px; color: #6b7280; line-height: 1.85; }

        /* Footer */
        .footer {
            padding: 16px 0;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
            background: white;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
        }

        /* Main و Hero باید فضای فوتر رو در نظر بگیرن */
        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
            padding-bottom: 50px; /* ارتفاع فوتر */
        }
        /* Modal */
        .modal-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
            z-index: 1000; align-items: center; justify-content: center;
            padding: 20px;
        }
        .modal-backdrop.active { display: flex; }

        .modal-card {
            background: white; border-radius: 20px;
            width: 100%; max-width: 420px;
            padding: 36px 32px; position: relative;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.4);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-close {
            position: absolute; top: 16px; left: 16px;
            width: 32px; height: 32px; border-radius: 10px;
            background: #f3f4f6; border: none; color: #6b7280;
            font-size: 18px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .modal-close:hover { background: #e5e7eb; color: #111827; }

        .modal-title { font-size: 22px; font-weight: 800; color: #111827; margin-bottom: 6px; text-align: center; }
        .modal-subtitle { font-size: 13px; color: #6b7280; text-align: center; margin-bottom: 24px; }

        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }

        .form-input {
            width: 100%; padding: 12px 14px;
            border: 1.5px solid #e5e7eb; border-radius: 10px;
            font-size: 14px; font-family: inherit; color: #111827;
            background: #f9fafb; transition: all 0.2s; outline: none;
        }
        .form-input:focus { border-color: #6366f1; background: white; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

        .modal-submit {
            width: 100%; padding: 13px; border-radius: 10px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white; font-size: 15px; font-weight: 700;
            border: none; cursor: pointer; font-family: inherit;
            margin-top: 8px;
            box-shadow: 0 4px 14px -4px rgba(99, 102, 241, 0.5);
        }
        .modal-submit:hover { transform: translateY(-1px); box-shadow: 0 8px 20px -4px rgba(99, 102, 241, 0.7); }

        .modal-error {
            background: #fef2f2; border: 1px solid #fecaca;
            color: #dc2626; font-size: 13px;
            padding: 10px 12px; border-radius: 10px;
            margin-bottom: 16px; text-align: center;
        }

        .modal-footer-text { text-align: center; font-size: 13px; color: #6b7280; margin-top: 20px; }
        .modal-footer-text a { color: #6366f1; font-weight: 600; cursor: pointer; }
        .modal-footer-text a:hover { text-decoration: underline; }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up { opacity: 0; animation: fadeUp 0.7s ease-out forwards; }
        .d1 { animation-delay: 0.1s; } .d2 { animation-delay: 0.2s; } .d3 { animation-delay: 0.3s; }
        .d4 { animation-delay: 0.4s; } .d5 { animation-delay: 0.5s; }
    </style>
	<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="0">
</head>
<body>
<script>
// 🔑 تازه‌سازی CSRF token
(function() {
    function refreshCsrf() {
        return fetch('/csrf-token', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
            cache: 'no-store'
        })
        .then(r => r.json())
        .then(data => {
            if (data.token) {
                var meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', data.token);
                
                document.querySelectorAll('input[name="_token"]').forEach(function(el) {
                    el.value = data.token;
                });
                console.log('CSRF token refreshed');
            }
            return data;
        })
        .catch(function(err) {
            console.warn('CSRF refresh failed:', err);
        });
    }

    // Refresh immediately on page load
    refreshCsrf();
    
    // Refresh every 5 minutes (in case user keeps page open)
    setInterval(refreshCsrf, 300000);
    
    // Expose globally
    window.refreshCsrf = refreshCsrf;
})();
</script>
    <nav class="navbar">
        <div class="container navbar-inner">
            <a href="/" class="logo">
                <div class="logo-icon">🎓</div>
                <span>آموزش مجازی</span>
            </a>
            <div class="nav-links">
                <button type="button" class="nav-link" onclick="openLoginModal()">ورود</button>
                <button type="button" class="nav-btn" onclick="openRegisterModal()">ثبت‌نام معلم</button>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container hero-content">
            <div class="hero-badge fade-up d1">
                <span class="hero-badge-dot"></span>
                سامانه‌ی اختصاصی مدارس و دانشگاه‌ها
            </div>

            <h1 class="hero-title fade-up d2">
                کلاس‌های آنلاین،<br>
                <span class="gradient-text">حرفه‌ای</span> برگزار کنید
            </h1>

            <p class="hero-subtitle fade-up d3">
                پلتفرم جامع مدیریت آموزش برای مدارس و موسسات آموزشی —
                برنامه هفتگی، کلاس زنده، حضور و غیاب، اطلاع‌رسانی و مدیریت کامل کاربران،
                همه در یک محیط یکپارچه.
            </p>

            <div class="stats-row fade-up d4">
                <div class="stat-item">
                    <div class="stat-value">۹۹٪</div>
                    <div class="stat-label">پایداری سرویس</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">۲۴/۷</div>
                    <div class="stat-label">پشتیبانی</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">SSL</div>
                    <div class="stat-label">امنیت کامل</div>
                </div>
            </div>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card fade-up d3">
                    <div class="feature-icon">📚</div>
                    <h3>مدیریت کلاس و دوره</h3>
                    <p>کلاس‌های منظم و فوق‌برنامه، مدیریت پایه و کلاس، ایمپورت از اکسل.</p>
                </div>
                <div class="feature-card fade-up d4">
                    <div class="feature-icon">🎥</div>
                    <h3>کلاس زنده‌ی آنلاین</h3>
                    <p>کلاس زنده با کیفیت بالا، اشتراک صفحه، چت زنده و ضبط جلسات.</p>
                </div>
                <div class="feature-card fade-up d5">
                    <div class="feature-icon">📅</div>
                    <h3>برنامه هفتگی</h3>
                    <p>تعریف زنگ‌ها، ساخت برنامه هفتگی، نمایش گرید زیبا و ایمپورت از اکسل.</p>
                </div>
                <div class="feature-card fade-up d5">
                    <div class="feature-icon">👥</div>
                    <h3>مدیریت کاربران</h3>
                    <p>معلمان، دانش‌آموزان و والدین — با کنترل دسترسی دقیق.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            © {{ date('Y') }} سامانه آموزش مجازی — طراحی و توسعه: علی مرادیان
        </div>
    </footer>
    {{-- ==================== Login Modal ==================== --}}
    <div class="modal-backdrop" id="loginModal">
        <div class="modal-card" onclick="event.stopPropagation()">
            <button type="button" class="modal-close" onclick="closeLoginModal()">✕</button>

            <h2 class="modal-title">ورود به سامانه</h2>
            <p class="modal-subtitle">کد ملی و رمز عبور خود را وارد کنید</p>

            @if ($errors->any() && old('_form') === 'login')
                <div class="modal-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="_form" value="login">

                <div class="form-group">
                    <label class="form-label">کد ملی</label>
                    <input type="text" name="code_melli" class="form-input"
                           value="{{ old('_form') === 'login' ? old('code_melli') : '' }}"
                           maxlength="10" dir="ltr" required>
                </div>

                <div class="form-group">
                    <label class="form-label">رمز عبور</label>
                    <input type="password" name="password" class="form-input" dir="ltr" required>
                </div>
		<button type="submit" class="modal-submit">ورود</button>
            </form>

            <p class="modal-footer-text">
                حساب ندارید؟
                <a onclick="switchToRegister()">ثبت‌نام معلم</a>
            </p>
        </div>
    </div>

    {{-- ==================== Register Modal ==================== --}}
    <div class="modal-backdrop" id="registerModal">
        <div class="modal-card" onclick="event.stopPropagation()" style="max-width: 480px;">
            <button type="button" class="modal-close" onclick="closeRegisterModal()">✕</button>

            <h2 class="modal-title">ثبت‌نام معلم</h2>
            <p class="modal-subtitle">پس از ثبت‌نام، توسط مدیر مدرسه تایید می‌شوید</p>

            @if ($errors->any() && old('_form') === 'register')
                <div class="modal-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <input type="hidden" name="_form" value="register">

                <div class="form-group">
                    <label class="form-label">نام و نام‌خانوادگی</label>
                    <input type="text" name="name" class="form-input"
                           value="{{ old('_form') === 'register' ? old('name') : '' }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">کد ملی (نام کاربری)</label>
                    <input type="text" name="code_melli" class="form-input"
                           value="{{ old('_form') === 'register' ? old('code_melli') : '' }}"
                           maxlength="10" dir="ltr" required>
                </div>

                <div class="form-group">
                    <label class="form-label">شماره موبایل</label>
                    <input type="text" name="mobile" class="form-input"
                           value="{{ old('_form') === 'register' ? old('mobile') : '' }}"
                           maxlength="11" dir="ltr" required>
                </div>

                <div class="form-group">
                    <label class="form-label">رمز عبور</label>
                    <input type="password" name="password" class="form-input" dir="ltr" minlength="6" required>
                </div>

                <div class="form-group">
                    <label class="form-label">تکرار رمز عبور</label>
                    <input type="password" name="password_confirmation" class="form-input" dir="ltr" minlength="6" required>
                </div>

                <div class="form-group">
                    <label class="form-label">مدرسه</label>
                    <select name="school_id" class="form-input" required style="cursor:pointer;">
                        <option value="">— انتخاب کنید —</option>
                        @foreach (\App\Models\School::where('is_active', true)->orderBy('name')->get() as $school)
                            <option value="{{ $school->id }}" {{ old('_form') === 'register' && old('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="modal-submit">ثبت‌نام</button>
            </form>

            <p class="modal-footer-text">
                حساب دارید؟
                <a onclick="switchToLogin()">ورود</a>
            </p>
        </div>
    </div>

    <script>
    // ============ Login Modal ============
    function openLoginModal() {
        if (window.refreshCsrf) window.refreshCsrf();
        document.getElementById('loginModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            document.querySelector('#loginModal input[name="code_melli"]')?.focus();
        }, 100);
    }

    function closeLoginModal() {
        document.getElementById('loginModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    // ============ Register Modal ============
    function openRegisterModal() {
        if (window.refreshCsrf) window.refreshCsrf();
        document.getElementById('registerModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeRegisterModal() {
        document.getElementById('registerModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    // ============ Switch ============
    function switchToRegister() {
        closeLoginModal();
        openRegisterModal();
    }

    function switchToLogin() {
        closeRegisterModal();
        openLoginModal();
    }

    // ============ Backdrop Close ============
    document.getElementById('loginModal').addEventListener('click', function(e) {
        if (e.target === this) closeLoginModal();
    });

    document.getElementById('registerModal').addEventListener('click', function(e) {
        if (e.target === this) closeRegisterModal();
    });

    // ============ Escape ============
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLoginModal();
            closeRegisterModal();
        }
    });

    // ============ Auto-open Modal on Error ============
    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            var form = "{{ old('_form') }}";
            if (form === 'register') {
                openRegisterModal();
            } else if (form === 'login') {
                openLoginModal();
            }
        });
    @endif
    @if (session('show_login'))
        document.addEventListener('DOMContentLoaded', function() {
            openLoginModal();
        });
    @endif
    </script>

</body>
</html>
