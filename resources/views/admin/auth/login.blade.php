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
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page min-h-screen p-4 md:p-8">
    <div class="login-layout mx-auto grid min-h-[calc(100vh-2rem)] w-full max-w-6xl overflow-hidden rounded-3xl border md:min-h-[calc(100vh-4rem)] md:grid-cols-2">
        <section class="login-hero relative overflow-hidden p-8 md:order-2 md:p-12">
            <div class="relative z-10 max-w-md space-y-6">
                <p class="login-eyebrow">Private Access</p>
                <h1 class="font-serif text-4xl leading-tight text-white md:text-5xl">{{ $globalSetting->site_name ?? 'MUA Portfolio' }}</h1>
            </div>
            <div class="login-orb login-orb-one" aria-hidden="true"></div>
            <div class="login-orb login-orb-two" aria-hidden="true"></div>
        </section>

        <section class="login-form-wrap p-6 sm:p-8 md:order-1 md:p-12">
            <form method="POST" action="{{ route('admin.login.submit') }}" class="mx-auto w-full max-w-md space-y-5">
                @csrf
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Masuk Admin</p>
                    <h2 class="font-serif text-3xl leading-tight text-slate-900">Welcome Back</h2>
                </div>

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
                @endif

                <label class="field login-field">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com">
                </label>
                <label class="field login-field">
                    <span>Password</span>
                    <input type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password">
                </label>

                <input type="hidden" name="_latitude" id="login-latitude">
                <input type="hidden" name="_longitude" id="login-longitude">
                <input type="hidden" name="_geo_location" id="login-geo-location">

                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300"> Ingat saya
                </label>

                <button class="btn-primary login-submit w-full" type="submit">Masuk ke Dashboard</button>
            </form>
        </section>
    </div>

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
