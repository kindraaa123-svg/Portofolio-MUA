@extends('layouts.admin')
@section('title', 'Validasi Pembayaran')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-semibold">Validasi Bukti Pembayaran</h1>
</div>

<form method="GET" class="mb-5 grid md:grid-cols-3 gap-3">
    <input class="input" type="text" name="q" value="{{ $search }}" placeholder="Cari kode booking / customer / payer">
    <select class="input" name="status">
        <option value="">Semua status bukti</option>
        @foreach(['pending','verified','rejected'] as $s)
            <option value="{{ $s }}" @selected($status===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="btn-primary">Filter</button>
</form>

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin">
        <thead>
        <tr>
            <th>Booking</th>
            <th>Payer</th>
            <th>Jumlah</th>
            <th>Waktu Bayar</th>
            <th>Status</th>
            <th>Bukti</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @forelse($payments as $payment)
            <tr>
                <td>
                    {{ $payment->booking?->booking_code ?? '-' }}<br>
                    <small>{{ $payment->booking?->customer?->name ?? '-' }}</small>
                </td>
                <td>{{ $payment->payer_name }}<br><small>{{ $payment->bank_name ?? '-' }}</small></td>
                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                <td>{{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}</td>
                <td><strong>{{ $payment->status }}</strong></td>
                <td>
                    @if($payment->proof_image)
                        <a class="btn-secondary text-xs" target="_blank" href="{{ asset('storage/' . $payment->proof_image) }}">Lihat Bukti</a>
                    @else
                        <span class="text-xs text-slate-500">Tidak ada bukti</span>
                    @endif
                </td>
                <td>
                    @if($payment->status === 'pending')
                        <form method="POST" action="{{ route('admin.bookings.verify-payment', $payment) }}" class="space-y-2 min-w-[220px]">
                            @csrf
                            <div class="flex flex-wrap gap-2">
                                <button class="btn-primary text-xs" type="submit" name="status" value="verified" onclick="return confirm('Yakin verifikasi pembayaran ini? Aksi ini final dan tidak bisa diubah lagi.');">Verifikasi</button>
                                <button class="btn-secondary text-xs" type="submit" name="status" value="rejected" onclick="return confirm('Yakin tolak pembayaran ini? Aksi ini final dan tidak bisa diubah lagi.');">Tolak</button>
                            </div>
                        </form>
                    @else
                        <span class="text-xs text-slate-500">Aksi final sudah dipilih.</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="py-6 text-center text-slate-500">Belum ada data bukti pembayaran.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $payments->links() }}</div>
@endsection
