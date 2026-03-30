@extends('layouts.admin')
@section('title', 'Kelola Reservasi')
@section('content')
<div class="flex flex-wrap gap-3 justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold">Kelola Reservasi</h1>
    <a class="btn-secondary" href="{{ route('admin.bookings.export') }}">Export CSV</a>
</div>

<form method="GET" class="grid md:grid-cols-3 gap-3 mb-5">
    <input class="input" type="text" name="q" value="{{ $search }}" placeholder="Cari kode / customer">
    <select class="input" name="status">
        <option value="">Semua status</option>
        @foreach(['pending','confirmed','on_process','completed','cancelled'] as $s)
            <option value="{{ $s }}" @selected($status===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="btn-primary">Filter</button>
</form>

<div class="grid lg:grid-cols-2 gap-4 mb-6">
    <section class="card-premium bg-white">
        <h2 class="font-semibold mb-3">Atur Slot Jam</h2>
        <form method="POST" action="{{ route('admin.bookings.store-slot') }}" class="grid md:grid-cols-2 gap-3">
            @csrf
            <label class="field"><span>Hari</span>
                <select name="day_of_week" required>
                    <option value="0">Minggu</option><option value="1">Senin</option><option value="2">Selasa</option><option value="3">Rabu</option><option value="4">Kamis</option><option value="5">Jumat</option><option value="6">Sabtu</option>
                </select>
            </label>
            <label class="field"><span>Max Booking</span><input type="number" name="max_bookings" value="1" min="1" required></label>
            <label class="field"><span>Jam Mulai</span><input type="time" name="start_time" required></label>
            <label class="field"><span>Jam Selesai</span><input type="time" name="end_time" required></label>
            <button class="btn-primary md:col-span-2">Simpan Slot</button>
        </form>
    </section>

    <section class="card-premium bg-white">
        <h2 class="font-semibold mb-3">Atur Hari Libur/Jam Penuh</h2>
        <form method="POST" action="{{ route('admin.bookings.store-blocked') }}" class="grid md:grid-cols-2 gap-3">
            @csrf
            <label class="field"><span>Tanggal</span><input type="date" name="blocked_date" required></label>
            <label class="field"><span>Alasan</span><input type="text" name="reason"></label>
            <label class="field"><span>Jam Mulai</span><input type="time" name="start_time"></label>
            <label class="field"><span>Jam Selesai</span><input type="time" name="end_time"></label>
            <label class="flex items-center gap-2 md:col-span-2"><input type="checkbox" name="is_full_day" value="1"> Blokir penuh 1 hari</label>
            <button class="btn-primary md:col-span-2">Simpan Jadwal Blokir</button>
        </form>
    </section>
</div>

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th>Kode</th><th>Customer</th><th>Tanggal</th><th>Total</th><th>DP</th><th>Status</th><th>Pembayaran</th><th>Verifikasi DP</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach($bookings as $item)
                @php($dpPayment = $item->payments->where('payment_type', 'dp')->sortByDesc('id')->first())
                <tr>
                    <td>{{ $item->booking_code }}</td>
                    <td>{{ $item->customer?->name }}<br><small>{{ $item->customer?->phone }}</small></td>
                    <td>{{ $item->booking_date?->format('d M Y') }} {{ $item->booking_time }}</td>
                    <td>Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->dp_amount, 0, ',', '.') }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->payment_status }}</td>
                    <td>
                        @if($dpPayment)
                            <div class="space-y-2 text-xs">
                                <p>{{ $dpPayment->payer_name }} | {{ $dpPayment->bank_name ?? '-' }}</p>
                                <p>{{ $dpPayment->paid_at?->format('d M Y H:i') }}</p>
                                <a class="btn-secondary text-xs" href="{{ asset('storage/' . $dpPayment->proof_image) }}" target="_blank">Lihat Bukti</a>
                                <form method="POST" action="{{ route('admin.bookings.verify-payment', $dpPayment) }}" class="space-y-1">
                                    @csrf
                                    <select class="input text-xs" name="status" required>
                                        <option value="verified">Verifikasi</option>
                                        <option value="rejected">Tolak</option>
                                    </select>
                                    <input class="input text-xs" name="note" placeholder="Catatan verifikasi">
                                    <button class="btn-primary text-xs">Simpan</button>
                                </form>
                                <p>Status bukti: <strong>{{ $dpPayment->status }}</strong></p>
                            </div>
                        @else
                            <span class="text-xs text-slate-500">Belum ada bukti DP</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.bookings.update-status', $item) }}" class="space-y-2">
                            @csrf
                            <select name="status" class="input text-xs">
                                @foreach(['pending','confirmed','on_process','completed','cancelled'] as $s)
                                    <option value="{{ $s }}" @selected($item->status === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                            <select name="payment_status" class="input text-xs">
                                @foreach(['unpaid','dp_paid','paid'] as $p)
                                    <option value="{{ $p }}" @selected($item->payment_status === $p)>{{ $p }}</option>
                                @endforeach
                            </select>
                            <input type="number" class="input text-xs" name="dp_amount" value="{{ $item->dp_amount }}" placeholder="DP">
                            <button class="btn-primary text-xs">Update</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $bookings->links() }}</div>
@endsection
