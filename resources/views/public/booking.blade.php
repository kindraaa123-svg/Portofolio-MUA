@extends('layouts.public')
@section('title', 'Reservasi Online')
@section('content')
<section class="container mx-auto px-4 py-16 max-w-5xl">
    <h1 class="font-serif text-4xl text-rose-900 mb-3">Reservasi Online</h1>
    <p class="text-rose-900/80 mb-8">Booking dikonfirmasi setelah admin memverifikasi pembayaran DP 50% dari total layanan.</p>

    <form method="POST" action="{{ route('booking.store') }}" enctype="multipart/form-data" class="card-premium space-y-6" id="booking-form">
        @csrf
        <div class="grid md:grid-cols-2 gap-4">
            <label class="field"><span>Nama</span><input type="text" name="name" required value="{{ old('name') }}"></label>
            <label class="field"><span>No. WhatsApp</span><input type="text" name="phone" required value="{{ old('phone') }}"></label>
            <label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}"></label>
            <label class="field"><span>Instagram</span><input type="text" name="instagram" value="{{ old('instagram') }}"></label>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <label class="field"><span>Layanan</span>
                <select name="service_id" id="service-id" required>
                    <option value="">Pilih layanan</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-home-fee="{{ $service->home_service_fee }}" @selected(old('service_id') == $service->id)>{{ $service->name }} - Rp {{ number_format($service->price, 0, ',', '.') }}</option>
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

        <label class="field"><span>Alamat (wajib jika home service)</span><textarea name="location_address" rows="2">{{ old('location_address') }}</textarea></label>

        <fieldset>
            <legend class="font-semibold mb-2">Pilih Add-on (opsional)</legend>
            <div class="grid md:grid-cols-2 gap-3">
                @foreach ($addons as $addon)
                    <label class="addon-item">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" data-addon-price="{{ $addon->price }}" @checked(in_array($addon->id, old('addon_ids', [])))>
                            <span>{{ $addon->name }}</span>
                        </div>
                        <strong>Rp {{ number_format($addon->price, 0, ',', '.') }}</strong>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="grid md:grid-cols-2 gap-4">
            <label class="field"><span>Nama Pengirim Transfer DP</span><input type="text" name="payer_name" required value="{{ old('payer_name') }}"></label>
            <label class="field"><span>Bank Pengirim</span><input type="text" name="bank_name" value="{{ old('bank_name') }}"></label>
            <label class="field"><span>Tanggal/Jam Transfer</span><input type="datetime-local" name="transfer_at" required value="{{ old('transfer_at') }}"></label>
            <label class="field"><span>Upload Bukti Transfer DP</span><input type="file" name="dp_proof" required accept="image/*"></label>
        </div>

        <label class="field"><span>Catatan tambahan</span><textarea name="notes" rows="3">{{ old('notes') }}</textarea></label>

        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm space-y-1">
            <p>Subtotal layanan: <strong id="summary-subtotal">Rp 0</strong></p>
            <p>Total add-on: <strong id="summary-addon">Rp 0</strong></p>
            <p>Biaya home service: <strong id="summary-home">Rp 0</strong></p>
            <p class="text-lg">Grand total: <strong id="summary-total">Rp 0</strong></p>
            <p class="text-lg text-rose-800">DP wajib 50%: <strong id="summary-dp">Rp 0</strong></p>
        </div>

        <button class="btn-primary" type="submit">Kirim Reservasi</button>
    </form>
</section>

<script>
const bookingDate = document.getElementById('booking-date');
const bookingTime = document.getElementById('booking-time');
const serviceSelect = document.getElementById('service-id');
const locationType = document.getElementById('location-type');
const addonChecks = document.querySelectorAll('[data-addon-price]');

const toRupiah = (num) => 'Rp ' + Number(num || 0).toLocaleString('id-ID');

async function fetchAvailableTimes() {
    if (!bookingDate.value) return;
    const res = await fetch(`{{ route('booking.available-times') }}?date=${bookingDate.value}`);
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

bookingDate?.addEventListener('change', fetchAvailableTimes);
serviceSelect?.addEventListener('change', calculateTotal);
locationType?.addEventListener('change', calculateTotal);
addonChecks.forEach(item => item.addEventListener('change', calculateTotal));
calculateTotal();
</script>
@endsection
