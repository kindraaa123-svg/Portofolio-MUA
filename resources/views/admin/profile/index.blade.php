@extends('layouts.admin')
@section('title', 'Profile')
@section('content')
<section class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="relative bg-white p-6 md:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Account Center</p>
        <h1 class="mt-2 font-serif text-3xl leading-tight text-slate-900 md:text-4xl">Profile</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600">Kelola informasi akun dan keamanan password untuk user yang sedang login.</p>

        <div class="mt-4 grid gap-2 sm:inline-grid">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                <span class="font-semibold text-slate-900">Nama:</span> {{ $user?->name ?? '-' }}
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                <span class="font-semibold text-slate-900">Email:</span> {{ $user?->email ?? '-' }}
            </div>
        </div>
    </div>
</section>

<div class="grid gap-6 xl:grid-cols-2">
    <section class="card-premium bg-white space-y-5">
        <div class="pb-3 border-b border-slate-200">
            <h2 class="font-semibold text-lg text-slate-900">Informasi Akun</h2>
            <p class="mt-1 text-sm text-slate-600">Edit nama, email, dan nomor HP.</p>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <label class="field md:col-span-2">
                <span>Nama</span>
                <input name="name" value="{{ old('name', $user?->name) }}" required>
            </label>
            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email', $user?->email) }}" required>
            </label>
            <label class="field">
                <span>No. HP</span>
                <input name="phone" value="{{ old('phone', $user?->phone) }}">
            </label>
            <div class="md:col-span-2 pt-1">
                <button class="btn-primary w-full sm:w-auto" type="submit">Simpan Profil</button>
            </div>
        </form>
    </section>

    <section class="card-premium bg-white space-y-5">
        <div class="pb-3 border-b border-slate-200">
            <h2 class="font-semibold text-lg text-slate-900">Ganti Password</h2>
            <p class="mt-1 text-sm text-slate-600">Masukkan password lama, lalu set password baru.</p>
        </div>

        <form method="POST" action="{{ route('admin.profile.update-password') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <label class="field md:col-span-2">
                <span>Password Lama</span>
                <input type="password" name="current_password" autocomplete="current-password" required>
            </label>
            <label class="field">
                <span>Password Baru</span>
                <input type="password" name="new_password" autocomplete="new-password" required>
            </label>
            <label class="field">
                <span>Konfirmasi Password Baru</span>
                <input type="password" name="new_password_confirmation" autocomplete="new-password" required>
            </label>
            <div class="md:col-span-2 pt-1">
                <button class="btn-primary w-full sm:w-auto" type="submit">Simpan Password Baru</button>
            </div>
        </form>
    </section>
</div>
@endsection
