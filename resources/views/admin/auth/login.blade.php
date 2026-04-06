<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>
    <style>
        :root {
            --theme-primary: {{ $globalSetting->theme_primary ?? '#2563eb' }};
            --theme-secondary: {{ $globalSetting->theme_secondary ?? '#dbeafe' }};
        }

        .login-navy-shell {
            background-color: #f3f6fb;
        }

        .login-navy-panel {
            background-color: #0f274f;
        }

        .login-navy-subtitle {
            color: #334e7d;
        }

        .login-navy-form input[type='email'],
        .login-navy-form input[type='password'] {
            border-color: #bfccdf;
            background-color: #ffffff;
        }

        .login-navy-form input[type='email']:focus,
        .login-navy-form input[type='password']:focus {
            border-color: #1f3f75;
            box-shadow: 0 0 0 2px rgba(31, 63, 117, 0.14);
        }

        .login-navy-btn {
            background-color: #163a70;
            color: #fff;
        }

        .login-navy-btn:hover {
            background-color: #0f2c58;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-navy-shell min-h-screen">
    <main class="mx-auto grid min-h-screen w-full max-w-6xl items-center px-4 py-8 md:px-8">
        <section class="grid overflow-hidden rounded-3xl border border-slate-300 bg-white shadow-xl md:grid-cols-[1.05fr_0.95fr]">
            <div class="login-navy-panel relative hidden p-10 md:block">
                <div class="relative z-10 max-w-md space-y-5">
                    <p class="inline-flex rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white">Admin Panel</p>
                    <h1 class="font-serif text-4xl leading-tight text-white">{{ $globalSetting->site_name ?? 'MUA Portfolio' }}</h1>
                    <p class="text-sm text-white/85">Masuk untuk mengelola konten website, reservasi, dan laporan.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8 md:p-10">
                <form method="POST" action="{{ route('admin.login.submit') }}" class="login-navy-form mx-auto w-full max-w-md space-y-5">
                    @csrf
                    <div class="space-y-2">
                        <p class="login-navy-subtitle text-xs font-semibold uppercase tracking-[0.2em]">Masuk Admin</p>
                        <h2 class="font-serif text-3xl text-slate-900">Welcome Back</h2>
                        <p class="text-sm text-slate-500">Silakan login menggunakan akun admin.</p>
                    </div>

                    @if (session('success'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
                    @endif

                    <label class="field">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com">
                    </label>

                    <label class="field">
                        <span>Password</span>
                        <input type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password">
                    </label>

                    <input type="hidden" name="_latitude" id="login-latitude">
                    <input type="hidden" name="_longitude" id="login-longitude">
                    <input type="hidden" name="_geo_location" id="login-geo-location">

                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300"> Ingat saya
                    </label>

                    <div class="text-right">
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-slate-700 hover:text-slate-900">Lupa Password?</a>
                    </div>

                    <button class="login-navy-btn w-full rounded-xl px-5 py-3 text-base font-semibold" type="submit">Masuk ke Dashboard</button>
                </form>
            </div>
        </section>
    </main>

    <script>
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            document.getElementById('login-latitude').value = pos.coords.latitude;
            document.getElementById('login-longitude').value = pos.coords.longitude;
            document.getElementById('login-geo-location').value = 'Browser Geolocation';
        });
    }
    </script>
</body>
</html>
