<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password</title>
    <style>
        .fp-page { background-color: #f3f6fb; }
        .fp-panel { background-color: #0f274f; }
        .fp-card { border: 1px solid #c9d5e8; }
        .fp-subtitle { color: #334e7d; }
        .fp-input { border-color: #bfccdf; background-color: #fff; }
        .fp-input:focus { border-color: #1f3f75; box-shadow: 0 0 0 2px rgba(31, 63, 117, .14); }
        .fp-btn { background-color: #163a70; color: #fff; }
        .fp-btn:hover { background-color: #0f2c58; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="fp-page min-h-screen">
    <main class="mx-auto grid min-h-screen w-full max-w-5xl items-center px-4 py-8 md:px-8">
        <section class="fp-card grid overflow-hidden rounded-3xl bg-white shadow-xl md:grid-cols-[1.05fr_0.95fr]">
            <div class="fp-panel hidden p-10 md:block">
                <div class="max-w-md space-y-5">
                    <p class="inline-flex rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white">Security</p>
                    <h1 class="font-serif text-4xl leading-tight text-white">Reset Password</h1>
                    <p class="text-sm text-white/85">Masukkan email akun terdaftar. Kami akan kirim link reset password ke email Anda.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8 md:p-10">
                <form method="POST" action="{{ route('password.email') }}" class="mx-auto w-full max-w-md space-y-5">
                    @csrf
                    <div class="space-y-2">
                        <p class="fp-subtitle text-xs font-semibold uppercase tracking-[0.2em]">Lupa Password</p>
                        <h2 class="font-serif text-3xl text-slate-900">Reset via Email</h2>
                        <p class="text-sm text-slate-500">Link reset akan dikirim ke email yang valid.</p>
                    </div>

                    @if (session('status'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
                    @endif

                    <label class="field">
                        <span>Email Terdaftar</span>
                        <input class="fp-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com">
                    </label>

                    <button class="fp-btn w-full rounded-xl px-5 py-3 text-base font-semibold" type="submit">Kirim Link Reset</button>

                    <div class="text-center text-sm">
                        <a href="{{ route('admin.login') }}" class="font-medium text-slate-700 hover:text-slate-900">Kembali ke Login</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
