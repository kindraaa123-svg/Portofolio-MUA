@extends('layouts.admin')
@section('title', 'Pengaturan Website')
@section('content')
<section class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="relative bg-white p-6 md:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Admin Configuration</p>
        <h1 class="mt-2 font-serif text-3xl leading-tight text-slate-900 md:text-4xl">Pengaturan Website</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600">Atur identitas brand, media homepage, kontak, dan warna tema agar tampilan publik dan admin selalu konsisten.</p>
    </div>
</section>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="setting-form" class="space-y-6">
    @csrf

    <section class="card-premium bg-white space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3 border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Identitas Website</h2>
                <p class="mt-1 text-sm text-slate-600">Nama brand, deskripsi, dan aset visual utama.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">Section 1</span>
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
                <small class="text-xs text-slate-500">Disarankan rasio 1:1 agar tetap proporsional.</small>
            </label>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-sm mb-2 text-slate-700">Pratinjau Logo</p>
                <div class="flex min-h-28 items-center justify-center rounded-lg bg-white p-2">
                    <img id="logo-preview" src="{{ !empty($setting?->logo) ? asset('storage/' . $setting->logo) : '' }}" class="h-24 w-24 object-cover rounded-lg {{ empty($setting?->logo) ? 'hidden' : '' }}" alt="Pratinjau logo">
                    <span id="logo-empty-state" class="text-xs text-slate-400 {{ !empty($setting?->logo) ? 'hidden' : '' }}">Belum ada logo</span>
                </div>
            </div>

            <label class="field md:col-span-2">
                <span>Banner Homepage (dengan crop)</span>
                <input type="file" name="home_banner" id="banner-input" data-preview-target="banner-preview" accept="image/*">
                <small class="text-xs text-slate-500">Disarankan rasio lebar (sekitar 16:7).</small>
            </label>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                <p class="text-sm mb-2 text-slate-700">Pratinjau Banner</p>
                <div class="flex min-h-48 items-center justify-center rounded-lg bg-white p-2">
                    <img id="banner-preview" src="{{ !empty($setting?->home_banner) ? asset('storage/' . $setting->home_banner) : '' }}" class="h-44 w-full object-cover rounded-lg {{ empty($setting?->home_banner) ? 'hidden' : '' }}" alt="Pratinjau banner">
                    <span id="banner-empty-state" class="text-xs text-slate-400 {{ !empty($setting?->home_banner) ? 'hidden' : '' }}">Belum ada banner</span>
                </div>
            </div>
        </div>
    </section>

    <section class="card-premium bg-white space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3 border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Kontak dan Alamat</h2>
                <p class="mt-1 text-sm text-slate-600">Informasi untuk halaman kontak dan komunikasi reservasi.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">Section 2</span>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <label class="field"><span>No Kontak</span><input name="contact_phone" value="{{ old('contact_phone', $setting?->contact_phone) }}"></label>
            <label class="field"><span>Email</span><input type="email" name="contact_email" value="{{ old('contact_email', $setting?->contact_email) }}"></label>
            <label class="field"><span>Nomor WhatsApp</span><input name="whatsapp_number" value="{{ old('whatsapp_number', $setting?->whatsapp_number) }}"></label>
            <label class="field"><span>Instagram URL</span><input name="instagram_url" value="{{ old('instagram_url', $setting?->instagram_url) }}"></label>
            <label class="field"><span>Nama Bank Tujuan DP</span><input name="bank_account_bank_name" value="{{ old('bank_account_bank_name', $setting?->bank_account_bank_name) }}"></label>
            <label class="field"><span>Nomor Rekening Tujuan DP</span><input name="bank_account_number" value="{{ old('bank_account_number', $setting?->bank_account_number) }}"></label>
            <label class="field md:col-span-2"><span>Alamat</span><textarea name="address" rows="3">{{ old('address', $setting?->address) }}</textarea></label>
        </div>

        <div class="border-t border-slate-200 pt-4">
            <button class="btn-primary w-full sm:w-auto" type="submit">Simpan Data Website</button>
        </div>
    </section>

</form>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const logoPreview = document.getElementById('logo-preview');
    const bannerPreview = document.getElementById('banner-preview');
    const logoEmptyState = document.getElementById('logo-empty-state');
    const bannerEmptyState = document.getElementById('banner-empty-state');

    const toggleImageEmptyState = () => {
        if (logoPreview && logoEmptyState) {
            logoEmptyState.classList.toggle('hidden', !logoPreview.classList.contains('hidden'));
        }
        if (bannerPreview && bannerEmptyState) {
            bannerEmptyState.classList.toggle('hidden', !bannerPreview.classList.contains('hidden'));
        }
    };

    document.addEventListener('change', toggleImageEmptyState);
    toggleImageEmptyState();
});
</script>
@endsection
