@extends('layouts.admin')
@section('title', 'Profile')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">Profile</h1>
    <p class="mt-1 text-sm text-slate-600">Informasi akun yang sedang login.</p>
</div>

<section class="card-premium bg-white space-y-5 max-w-3xl">
    <div class="pb-3 border-b border-slate-200">
        <h2 class="font-semibold text-lg">Informasi Akun</h2>
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
        <div class="md:col-span-2">
            <button class="btn-primary" type="submit">Simpan Profil</button>
        </div>
    </form>
</section>

<section class="card-premium bg-white space-y-5 max-w-3xl mt-6">
    <div class="pb-3 border-b border-slate-200">
        <h2 class="font-semibold text-lg">Ganti Password</h2>
        <p class="mt-1 text-sm text-slate-600">Masukkan password lama, lalu password baru.</p>
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
        <div class="md:col-span-2">
            <button class="btn-primary" type="submit">Simpan Password Baru</button>
        </div>
    </form>
</section>
@endsection
