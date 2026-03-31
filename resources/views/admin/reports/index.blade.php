@extends('layouts.admin')
@section('title', 'Laporan')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="text-2xl font-semibold">Laporan</h1>
    <div class="flex flex-wrap gap-2">
        <a class="btn-secondary" href="{{ route('admin.reports.export-pdf', request()->query()) }}" target="_blank">Export PDF</a>
        <a class="btn-secondary" href="{{ route('admin.reports.export-excel', request()->query()) }}">Export Excel</a>
        <a class="btn-secondary" href="{{ route('admin.reports.print', request()->query()) }}" target="_blank">Print</a>
    </div>
</div>

<form method="GET" class="grid md:grid-cols-5 gap-3 mb-6">
    <label class="field"><span>Dari Tanggal</span><input type="date" class="input" name="from" value="{{ $from }}"></label>
    <label class="field"><span>Sampai Tanggal</span><input type="date" class="input" name="to" value="{{ $to }}"></label>
    <label class="field"><span>Keyword</span><input type="text" class="input" name="q" value="{{ $keyword }}" placeholder="Kode booking / customer"></label>
    <label class="field">
        <span>Status Booking</span>
        <select class="input" name="status">
            <option value="">Semua</option>
            @foreach($statusOptions as $statusOption)
                <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ $statusOption }}</option>
            @endforeach
        </select>
    </label>
    <label class="field">
        <span>Status Pembayaran</span>
        <select class="input" name="payment_status">
            <option value="">Semua</option>
            @foreach($paymentStatusOptions as $paymentStatusOption)
                <option value="{{ $paymentStatusOption }}" @selected($paymentStatus === $paymentStatusOption)>{{ $paymentStatusOption }}</option>
            @endforeach
        </select>
    </label>
    <button class="btn-primary self-end">Filter</button>
</form>

<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4 mb-8">
    <article class="admin-card"><p>Total Booking</p><h2>{{ $summary['total_bookings'] }}</h2></article>
    <article class="admin-card"><p>Booking Pending</p><h2>{{ $summary['total_pending'] }}</h2></article>
    <article class="admin-card"><p>Booking Confirmed</p><h2>{{ $summary['total_confirmed'] }}</h2></article>
    <article class="admin-card"><p>Total Transaksi</p><h2>Rp {{ number_format($summary['total_transactions'], 0, ',', '.') }}</h2></article>
    <article class="admin-card"><p>DP Verified</p><h2>Rp {{ number_format($summary['total_dp_verified'], 0, ',', '.') }}</h2></article>
    <article class="admin-card"><p>DP Pending</p><h2>Rp {{ number_format($summary['total_dp_pending'], 0, ',', '.') }}</h2></article>
</div>

<div class="card-premium bg-white overflow-x-auto mb-8">
    <h2 class="font-semibold mb-4">Rekap Booking</h2>
    <table class="table-admin">
        <thead><tr><th>Kode</th><th>Customer</th><th>Tanggal</th><th>Status</th><th>Total</th></tr></thead>
        <tbody>
        @forelse($bookings as $item)
            <tr>
                <td>{{ $item->booking_code }}</td>
                <td>{{ $item->customer?->name }}</td>
                <td>{{ $item->booking_date?->format('d M Y') }}</td>
                <td>{{ $item->status }}</td>
                <td>Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="5">Tidak ada data booking pada rentang tanggal ini.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $bookings->links() }}</div>
</div>

<div class="card-premium bg-white overflow-x-auto">
    <h2 class="font-semibold mb-4">Rekap Pembayaran</h2>
    <table class="table-admin">
        <thead><tr><th>Booking</th><th>Pembayar</th><th>Tipe</th><th>Status</th><th>Nominal</th></tr></thead>
        <tbody>
        @forelse($payments as $payment)
            <tr>
                <td>{{ $payment->booking?->booking_code }}</td>
                <td>{{ $payment->payer_name }}</td>
                <td>{{ $payment->payment_type }}</td>
                <td>{{ $payment->status }}</td>
                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="5">Tidak ada data pembayaran pada rentang tanggal ini.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $payments->links() }}</div>
</div>
@endsection
