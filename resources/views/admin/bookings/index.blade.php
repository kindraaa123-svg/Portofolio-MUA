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

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th>Kode</th><th>Customer</th><th>Tanggal</th><th>Total</th><th>DP</th><th>Status</th><th>Pembayaran</th><th>Verifikasi DP</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach($bookings as $item)
                @php($dpPayment = $item->payments->where('payment_type', 'dp')->sortByDesc('id')->first())
                @php($finalPayment = $item->payments->where('payment_type', 'final')->sortByDesc('id')->first())
                @php($finalAmount = max(0, (float) $item->grand_total - (float) $item->dp_amount))
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
                                @if($dpPayment->proof_image)
                                    <a class="btn-secondary text-xs" href="{{ asset('storage/' . $dpPayment->proof_image) }}" target="_blank">Lihat Bukti</a>
                                @endif
                                @if($dpPayment->status === 'pending')
                                    <form method="POST" action="{{ route('admin.bookings.verify-payment', $dpPayment) }}" class="space-y-1">
                                        @csrf
                                        <div class="flex flex-wrap gap-2">
                                            <button class="btn-primary text-xs" type="submit" name="status" value="verified">Disetujui</button>
                                            <button class="btn-secondary text-xs" type="submit" name="status" value="rejected">Ditolak</button>
                                        </div>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500">Verifikasi DP sudah final dan tidak bisa diubah.</span>
                                @endif
                                <p>Status bukti: <strong>{{ $dpPayment->status }}</strong></p>
                            </div>
                        @else
                            <span class="text-xs text-slate-500">Belum ada bukti DP</span>
                        @endif
                    </td>
                    <td>
                        <div class="space-y-2 text-xs">
                            <p><strong>Status Lunas:</strong> {{ $item->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}</p>
                            <p><strong>Jumlah Sisa Bayar:</strong> Rp {{ number_format($finalAmount, 0, ',', '.') }}</p>
                            <form method="POST" action="{{ route('admin.bookings.set-settlement-status', $item) }}">
                                @csrf
                                <input type="hidden" name="settlement_status" value="paid">
                                <button class="btn-primary text-xs" type="submit" @disabled($item->payment_status === 'paid')>Set Lunas</button>
                            </form>
                            @if($finalPayment)
                                <p>{{ $finalPayment->payer_name }} | {{ $finalPayment->bank_name ?? '-' }}</p>
                                <p>{{ $finalPayment->paid_at?->format('d M Y H:i') }}</p>
                                <p>Status: <strong>{{ $finalPayment->status }}</strong></p>
                            @else
                                <span class="text-xs text-slate-500">Belum ada data pelunasan.</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $bookings->links() }}</div>
@endsection
