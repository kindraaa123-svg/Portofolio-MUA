<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
    <style>
        .rp-page { background-color: #f3f6fb; }
        .rp-panel { background-color: #0f274f; }
        .rp-card { border: 1px solid #c9d5e8; }
        .rp-subtitle { color: #334e7d; }
        .rp-input { border-color: #bfccdf; background-color: #fff; }
        .rp-input:focus { border-color: #1f3f75; box-shadow: 0 0 0 2px rgba(31, 63, 117, .14); }
        .rp-btn { background-color: #163a70; color: #fff; }
        .rp-btn:hover { background-color: #0f2c58; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="rp-page min-h-screen">
    <main class="mx-auto grid min-h-screen w-full max-w-5xl items-center px-4 py-8 md:px-8">
        <section class="rp-card grid overflow-hidden rounded-3xl bg-white shadow-xl md:grid-cols-[1.05fr_0.95fr]">
            <div class="rp-panel hidden p-10 md:block">
                <div class="max-w-md space-y-5">
                    <p class="inline-flex rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white">Security</p>
                    <h1 class="font-serif text-4xl leading-tight text-white">Buat Password Baru</h1>
                    <p class="text-sm text-white/85">Masukkan password baru minimal 8 karakter untuk mengamankan akun Anda.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8 md:p-10">
                <form method="POST" action="{{ route('password.update') }}" class="mx-auto w-full max-w-md space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="space-y-2">
                        <p class="rp-subtitle text-xs font-semibold uppercase tracking-[0.2em]">Reset Password</p>
                        <h2 class="font-serif text-3xl text-slate-900">Set Password Baru</h2>
                        <p class="text-sm text-slate-500">Gunakan kombinasi yang kuat dan mudah diingat.</p>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
                    @endif

                    <label class="field">
                        <span>Email</span>
                        <input class="rp-input" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email" placeholder="nama@email.com">
                    </label>

                    <label class="field">
                        <span>Password Baru</span>
                        <input class="rp-input" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter">
                    </label>

                    <label class="field">
                        <span>Konfirmasi Password</span>
                        <input class="rp-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password baru">
                    </label>

                    <button class="rp-btn w-full rounded-xl px-5 py-3 text-base font-semibold" type="submit">Reset Password</button>

                    <div class="text-center text-sm">
                        <a href="{{ route('admin.login') }}" class="font-medium text-slate-700 hover:text-slate-900">Kembali ke Login</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
