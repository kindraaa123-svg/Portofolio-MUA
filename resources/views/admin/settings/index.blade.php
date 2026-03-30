@extends('layouts.admin')
@section('title', 'Pengaturan Website')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold">Pengaturan Website</h1>
        <p class="text-sm text-slate-600 mt-1">Kelola identitas, kontak, dan tampilan utama website.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="setting-form" class="grid xl:grid-cols-3 gap-6">
    @csrf

    <section class="card-premium bg-white xl:col-span-2 space-y-5">
        <div class="pb-3 border-b border-slate-200">
            <h2 class="font-semibold text-lg">Identitas Website</h2>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <label class="field md:col-span-2">
                <span>Nama Website</span>
                <input name="site_name" value="{{ old('site_name', $setting?->site_name) }}" required>
            </label>

            <label class="field md:col-span-2">
                <span>Deskripsi Singkat</span>
                <input name="tagline" value="{{ old('tagline', $setting?->tagline) }}">
            </label>

            <label class="field">
                <span>Logo Website (dengan crop)</span>
                <input type="file" name="logo" id="logo-input" data-preview-target="logo-preview" accept="image/*">
            </label>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-sm mb-2">Pratinjau Logo</p>
                <img id="logo-preview" src="{{ !empty($setting?->logo) ? asset('storage/' . $setting->logo) : '' }}" class="h-24 w-24 object-cover rounded-lg {{ empty($setting?->logo) ? 'hidden' : '' }}" alt="Pratinjau logo">
            </div>

            <label class="field md:col-span-2">
                <span>Banner Homepage (dengan crop)</span>
                <input type="file" name="home_banner" id="banner-input" data-preview-target="banner-preview" accept="image/*">
            </label>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                <p class="text-sm mb-2">Pratinjau Banner</p>
                <img id="banner-preview" src="{{ !empty($setting?->home_banner) ? asset('storage/' . $setting->home_banner) : '' }}" class="h-44 w-full object-cover rounded-lg {{ empty($setting?->home_banner) ? 'hidden' : '' }}" alt="Pratinjau banner">
            </div>
        </div>
    </section>

    <div class="space-y-6">
        <section class="card-premium bg-white space-y-4">
            <div class="pb-3 border-b border-slate-200">
                <h2 class="font-semibold text-lg">Kontak dan Alamat</h2>
            </div>

            <label class="field"><span>No Kontak</span><input name="contact_phone" value="{{ old('contact_phone', $setting?->contact_phone) }}"></label>
            <label class="field"><span>Email</span><input type="email" name="contact_email" value="{{ old('contact_email', $setting?->contact_email) }}"></label>
            <label class="field"><span>Nomor WhatsApp</span><input name="whatsapp_number" value="{{ old('whatsapp_number', $setting?->whatsapp_number) }}"></label>
            <label class="field"><span>Instagram URL</span><input name="instagram_url" value="{{ old('instagram_url', $setting?->instagram_url) }}"></label>
            <label class="field"><span>Alamat</span><textarea name="address" rows="3">{{ old('address', $setting?->address) }}</textarea></label>
        </section>

        <section class="card-premium bg-white space-y-4">
            <div class="pb-3 border-b border-slate-200">
                <h2 class="font-semibold text-lg">Warna Website</h2>
            </div>

            <label class="field">
                <span>Warna Utama</span>
                <input type="color" name="theme_primary" value="{{ old('theme_primary', $setting?->theme_primary ?? '#c05b7b') }}">
            </label>
            <label class="field">
                <span>Warna Pendukung</span>
                <input type="color" name="theme_secondary" value="{{ old('theme_secondary', $setting?->theme_secondary ?? '#fce7ef') }}">
            </label>

            <button class="btn-primary w-full" type="submit">Simpan Pengaturan</button>
        </section>
    </div>
</form>
@endsection
