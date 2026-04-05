@extends('layouts.public')
@section('title', 'Reservasi Online')
@section('content')
<section class="container mx-auto max-w-7xl px-4 py-12 md:py-16">
    <div class="rounded-3xl border border-blue-200 bg-blue-50 p-6 shadow-sm md:p-9">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-700">Reservasi MUA</p>
            <h1 class="mt-3 font-serif text-3xl text-slate-900 md:text-5xl">Pesan Jadwal Makeup Kamu</h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600 md:text-base">
                Isi data diri, pilih layanan, lalu upload bukti DP untuk mengunci jadwal.
                Tim admin akan verifikasi pembayaran 50% sebelum reservasi dinyatakan confirmed.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <p class="font-semibold">Ada data yang belum valid:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <form method="POST" action="{{ route('booking.store') }}" enctype="multipart/form-data" class="space-y-6" id="booking-form">
            @csrf

            <div class="card-premium border-blue-100 bg-white">
                <div class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-xl font-semibold text-slate-900">Data Pelanggan</h2>
                    <p class="mt-1 text-sm text-slate-500">Gunakan nomor WhatsApp aktif untuk notifikasi status reservasi.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="field md:col-span-2"><span>Nama Lengkap</span><input type="text" name="name" required value="{{ old('name') }}" placeholder="Contoh: Intan Permata"></label>
                    <label class="field"><span>No. WhatsApp</span><input type="text" name="phone" required value="{{ old('phone') }}" placeholder="08xxxxxxxxxx"></label>
                    <label class="field"><span>Email (opsional)</span><input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com"></label>
                </div>
            </div>

            <div class="card-premium border-blue-100 bg-white">
                <div class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-xl font-semibold text-slate-900">Layanan dan Jadwal</h2>
                    <p class="mt-1 text-sm text-slate-500">Pilih layanan, tanggal, jam, dan tipe lokasi makeup.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="field md:col-span-2"><span>Layanan</span>
                        <select name="service_id" id="service-id" required>
                            <option value="">Pilih layanan</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-home-fee="{{ $service->home_service_fee }}" @selected(old('service_id') == $service->id)>{{ $service->name }} - Rp {{ number_format($service->price, 0, ',', '.') }} ({{ $service->duration_minutes }} menit)</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field"><span>Tanggal Booking</span><input type="date" name="booking_date" id="booking-date" required min="{{ now()->format('Y-m-d') }}" value="{{ old('booking_date') }}"></label>
                    <label class="field"><span>Jam Booking</span>
                        <select name="booking_time" id="booking-time" required>
                            <option value="{{ old('booking_time') }}">{{ old('booking_time') ?: 'Pilih tanggal dulu' }}</option>
                        </select>
                    </label>
                    <label class="field"><span>Lokasi Makeup</span>
                        <select name="location_type" id="location-type" required>
                            <option value="studio" @selected(old('location_type') === 'studio')>Studio</option>
                            <option value="home_service" @selected(old('location_type') === 'home_service')>Layanan ke Rumah</option>
                        </select>
                    </label>
                </div>
                <label class="field mt-4" id="location-address-field"><span>Alamat (wajib jika home service)</span><textarea name="location_address" rows="2" placeholder="Isi alamat lengkap lokasi makeup">{{ old('location_address') }}</textarea></label>
            </div>

            <div class="card-premium border-blue-100 bg-white">
                <div class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-xl font-semibold text-slate-900">Add-on dan Pembayaran DP</h2>
                    <p class="mt-1 text-sm text-slate-500">Pilih add-on jika diperlukan lalu isi detail transfer DP.</p>
                </div>

                <fieldset>
                    <legend class="mb-2 text-sm font-semibold text-slate-700">Pilih Add-on (opsional)</legend>
                    <div class="grid gap-3 md:grid-cols-2">
                        @forelse ($addons as $addon)
                            <label class="flex items-center justify-between gap-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" data-addon-price="{{ $addon->price }}" @checked(in_array($addon->id, old('addon_ids', [])))>
                                    <span>{{ $addon->name }}</span>
                                </div>
                                <strong>Rp {{ number_format($addon->price, 0, ',', '.') }}</strong>
                            </label>
                        @empty
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">Belum ada add-on aktif saat ini.</div>
                        @endforelse
                    </div>
                </fieldset>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="field"><span>Nama Pengirim Transfer DP</span><input type="text" name="payer_name" required value="{{ old('payer_name') }}" placeholder="Nama pemilik rekening"></label>
                    <label class="field"><span>Upload Bukti Transfer DP</span><input type="file" name="dp_proof" required accept="image/*"></label>
                </div>
            </div>

            <div class="card-premium border-blue-100 bg-white">
                <label class="field"><span>Catatan tambahan</span><textarea name="notes" rows="3" placeholder="Opsional: detail request makeup, preferensi, dll.">{{ old('notes') }}</textarea></label>
                <div class="mt-5 rounded-xl border border-blue-100 bg-blue-50/60 px-4 py-3 text-xs text-slate-600">
                    Setelah form dikirim, admin akan memverifikasi DP lalu menghubungi Anda via Email.
                </div>
                <button class="btn-primary mt-4 w-full md:w-auto" type="submit">Kirim Reservasi</button>
            </div>
        </form>

        <aside class="space-y-4 lg:sticky lg:top-24 lg:h-fit">
            @if (!empty($globalSetting?->bank_account_bank_name) || !empty($globalSetting?->bank_account_number))
                <div class="rounded-2xl border border-slate-200 bg-white p-5 text-sm shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Informasi Transfer</p>
                    <div class="mt-3 space-y-3">
                        @if (!empty($globalSetting?->bank_account_bank_name))
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-500">Nama Bank</p>
                                <p class="mt-1 font-semibold text-slate-900">{{ $globalSetting->bank_account_bank_name }}</p>
                            </div>
                        @endif
                        @if (!empty($globalSetting?->bank_account_number))
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-500">Nomor Rekening</p>
                                <p class="mt-1 break-all text-lg font-bold tracking-wide text-slate-900">{{ $globalSetting->bank_account_number }}</p>
                            </div>
                        @endif
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Transfer DP ke rekening di atas sebelum kirim form.</p>
                </div>
            @endif

            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 text-sm text-slate-700 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-blue-800">Ringkasan Biaya</p>
                <div class="space-y-2">
                    <p class="flex items-center justify-between gap-3"><span>Subtotal layanan</span><strong id="summary-subtotal">Rp 0</strong></p>
                    <p class="flex items-center justify-between gap-3"><span>Total add-on</span><strong id="summary-addon">Rp 0</strong></p>
                    <p class="flex items-center justify-between gap-3"><span>Biaya home service</span><strong id="summary-home">Rp 0</strong></p>
                    <p class="mt-2 flex items-center justify-between gap-3 border-t border-blue-200 pt-2 text-base text-slate-900"><span>Grand total</span><strong id="summary-total">Rp 0</strong></p>
                    <p class="flex items-center justify-between gap-3 rounded-lg bg-blue-100 px-3 py-2 text-base font-semibold text-blue-900"><span>DP wajib 50%</span><strong id="summary-dp">Rp 0</strong></p>
                </div>
            </div>
        </aside>
    </div>
</section>

<script>
const bookingDate = document.getElementById('booking-date');
const bookingTime = document.getElementById('booking-time');
const serviceSelect = document.getElementById('service-id');
const locationType = document.getElementById('location-type');
const addonChecks = document.querySelectorAll('[data-addon-price]');
const locationAddressField = document.getElementById('location-address-field');

const toRupiah = (num) => 'Rp ' + Number(num || 0).toLocaleString('id-ID');

async function fetchAvailableTimes() {
    if (!bookingDate.value) return;
    if (!serviceSelect?.value) {
        bookingTime.innerHTML = '<option value="">Pilih layanan dulu</option>';
        return;
    }

    const query = new URLSearchParams({
        date: bookingDate.value,
        service_id: serviceSelect.value,
    });
    const res = await fetch(`{{ route('booking.available-times') }}?${query.toString()}`);
    const data = await res.json();
    bookingTime.innerHTML = '<option value="">Pilih jam booking</option>';
    data.times.forEach(time => {
        const option = document.createElement('option');
        option.value = time;
        option.textContent = time;
        bookingTime.appendChild(option);
    });
}

function calculateTotal() {
    const selected = serviceSelect.options[serviceSelect.selectedIndex];
    const servicePrice = Number(selected?.dataset?.price || 0);
    const homeFee = locationType.value === 'home_service' ? Number(selected?.dataset?.homeFee || 0) : 0;
    let addonTotal = 0;
    addonChecks.forEach(item => { if (item.checked) addonTotal += Number(item.dataset.addonPrice || 0); });
    const grandTotal = servicePrice + addonTotal + homeFee;
    const dpAmount = grandTotal * 0.5;

    document.getElementById('summary-subtotal').textContent = toRupiah(servicePrice);
    document.getElementById('summary-addon').textContent = toRupiah(addonTotal);
    document.getElementById('summary-home').textContent = toRupiah(homeFee);
    document.getElementById('summary-total').textContent = toRupiah(grandTotal);
    document.getElementById('summary-dp').textContent = toRupiah(dpAmount);
}

function toggleLocationAddress() {
    if (!locationAddressField || !locationType) return;
    if (locationType.value === 'home_service') {
        locationAddressField.classList.remove('hidden');
    } else {
        locationAddressField.classList.add('hidden');
    }
}

bookingDate?.addEventListener('change', fetchAvailableTimes);
serviceSelect?.addEventListener('change', () => {
    calculateTotal();
    fetchAvailableTimes();
});
locationType?.addEventListener('change', () => {
    calculateTotal();
    toggleLocationAddress();
});
addonChecks.forEach(item => item.addEventListener('change', calculateTotal));
calculateTotal();
toggleLocationAddress();
</script>
@endsection
