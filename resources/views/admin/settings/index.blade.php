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
            <label class="field"><span>Nama Bank Tujuan DP</span><input name="bank_account_bank_name" value="{{ old('bank_account_bank_name', $setting?->bank_account_bank_name) }}"></label>
            <label class="field"><span>Nomor Rekening Tujuan DP</span><input name="bank_account_number" value="{{ old('bank_account_number', $setting?->bank_account_number) }}"></label>
            <label class="field"><span>Instagram URL</span><input name="instagram_url" value="{{ old('instagram_url', $setting?->instagram_url) }}"></label>
            <label class="field"><span>Alamat</span><textarea name="address" rows="3">{{ old('address', $setting?->address) }}</textarea></label>
            <button class="btn-secondary w-full" type="submit">Simpan Data Website</button>
        </section>

        <section class="card-premium bg-white space-y-4">
            <div class="pb-3 border-b border-slate-200">
                <h2 class="font-semibold text-lg">Warna Website</h2>
                <p class="mt-1 text-xs text-slate-500">Warna ini juga dipakai untuk tema sidebar admin dan disimpan ke database.</p>
            </div>

            <label class="field">
                <span>Warna Utama (Sidebar & Tombol)</span>
                <div class="flex items-center gap-3">
                    <input type="color" name="theme_primary" id="theme-primary-input" value="{{ old('theme_primary', $setting?->theme_primary ?? '#2563eb') }}">
                    <span id="theme-primary-hex" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-medium text-slate-700"></span>
                </div>
            </label>
            <label class="field">
                <span>Warna Pendukung (Hover Sidebar)</span>
                <div class="flex items-center gap-3">
                    <input type="color" name="theme_secondary" id="theme-secondary-input" value="{{ old('theme_secondary', $setting?->theme_secondary ?? '#dbeafe') }}">
                    <span id="theme-secondary-hex" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-medium text-slate-700"></span>
                </div>
            </label>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs text-slate-600">Preview Warna Sidebar</p>
                <div id="sidebar-theme-preview" class="mt-2 h-14 rounded-lg border border-white/35"></div>
            </div>

            <button class="btn-primary w-full" type="submit" formaction="{{ route('admin.settings.update-theme') }}" formmethod="POST">Simpan Warna ke Database</button>
        </section>
    </div>
</form>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const primaryInput = document.getElementById('theme-primary-input');
    const secondaryInput = document.getElementById('theme-secondary-input');
    const primaryHex = document.getElementById('theme-primary-hex');
    const secondaryHex = document.getElementById('theme-secondary-hex');
    const preview = document.getElementById('sidebar-theme-preview');
    const adminSidebar = document.getElementById('admin-sidebar');

    if (!primaryInput || !secondaryInput || !primaryHex || !secondaryHex || !preview) {
        return;
    }

    const applyThemePreview = () => {
        const primary = (primaryInput.value || '#2563eb').toUpperCase();
        const secondary = (secondaryInput.value || '#dbeafe').toUpperCase();

        primaryHex.textContent = primary;
        secondaryHex.textContent = secondary;

        document.documentElement.style.setProperty('--theme-primary', primary);
        document.documentElement.style.setProperty('--theme-secondary', secondary);

        preview.style.backgroundColor = primary;
        preview.style.backgroundImage = 'none';

        if (adminSidebar) {
            adminSidebar.style.backgroundColor = primary;
            adminSidebar.style.backgroundImage = 'none';
        }
    };

    primaryInput.addEventListener('input', applyThemePreview);
    primaryInput.addEventListener('change', applyThemePreview);
    secondaryInput.addEventListener('input', applyThemePreview);
    secondaryInput.addEventListener('change', applyThemePreview);
    applyThemePreview();
});
</script>
@endsection
