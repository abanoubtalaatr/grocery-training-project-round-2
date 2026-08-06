<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول — Grocery Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; }
        body {
            background: #020817;
            min-height: 100vh;
        }
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
        }
        .orb-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(16,185,129,0.12) 0%, transparent 70%);
            top: -200px; right: -200px;
        }
        .orb-2 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(6,182,212,0.08) 0%, transparent 70%);
            bottom: -150px; left: -150px;
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255,255,255,0.07);
        }
        .input-field {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.2s ease;
            color: white;
        }
        .input-field:focus {
            background: rgba(16,185,129,0.05);
            border-color: rgba(16,185,129,0.4);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
            outline: none;
        }
        .input-field::placeholder { color: #475569; }
        .btn-login {
            background: linear-gradient(135deg, #059669, #0891b2);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.4s ease;
        }
        .btn-login:hover::after { transform: translateX(100%); }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5,150,105,0.4);
        }
        .btn-login:active { transform: translateY(0); }
        .grid-bg {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(16,185,129,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(16,185,129,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

<div class="grid-bg"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="w-full max-w-md relative z-10">

    <!-- Header -->
    <div class="text-center mb-8">
        <div class="inline-flex w-16 h-16 rounded-2xl items-center justify-center mb-4"
             style="background: linear-gradient(135deg, #059669, #0891b2); box-shadow: 0 10px 30px rgba(5,150,105,0.4);">
            <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Grocery Admin</h1>
        <p class="text-sm text-slate-400 mt-1">قم بتسجيل الدخول للوصول إلى لوحة التحكم</p>
    </div>

    <!-- Card -->
    <div class="glass-card rounded-2xl p-8">

        @if(session('error'))
        <div class="mb-5 flex items-center gap-3 p-3.5 rounded-xl text-sm"
             style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #fca5a5;">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-5 p-3.5 rounded-xl text-sm"
             style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #fca5a5;">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                <li class="flex items-center gap-2">
                    <span class="w-1 h-1 rounded-full bg-red-400 flex-shrink-0"></span>
                    {{ $error }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">
                    البريد الإلكتروني
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           placeholder="admin@example.com"
                           class="input-field w-full rounded-xl px-4 py-3 pr-10 text-sm"
                           required autofocus>
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">
                    كلمة المرور
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           class="input-field w-full rounded-xl px-4 py-3 pr-10 text-sm"
                           required>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember"
                           class="w-4 h-4 rounded border-slate-600 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 bg-slate-800">
                    <span class="text-sm text-slate-400">تذكرني</span>
                </label>
            </div>

            <!-- Submit -->
            <button type="submit" id="loginBtn"
                    class="btn-login w-full py-3 px-6 rounded-xl text-white font-semibold text-sm">
                <span id="loginText">تسجيل الدخول</span>
                <span id="loginSpinner" class="hidden items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    جارٍ الدخول...
                </span>
            </button>
        </form>

        <!-- Hint -->
        <div class="mt-6 pt-5 border-t border-slate-800/60">
            <p class="text-xs text-center text-slate-500">
                للوصول عبر Filament:
                <a href="/admin" class="text-emerald-400 hover:underline mr-1">admin panel</a>
            </p>
        </div>
    </div>

    <!-- Copyright -->
    <p class="text-center text-xs text-slate-600 mt-6">
        © {{ date('Y') }} Grocery Admin. جميع الحقوق محفوظة.
    </p>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('loginText').classList.add('hidden');
        document.getElementById('loginSpinner').classList.remove('hidden');
        document.getElementById('loginSpinner').classList.add('flex');
        document.getElementById('loginBtn').disabled = true;
    });
</script>
</body>
</html>
