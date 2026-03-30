@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<h1 class="text-3xl font-semibold text-slate-900 mb-6">Dashboard</h1>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <article class="admin-card"><p>Total Booking</p><h2>{{ $stats['bookings'] }}</h2></article>
    <article class="admin-card"><p>Pending Booking</p><h2>{{ $stats['pendingBookings'] }}</h2></article>
    <article class="admin-card"><p>Total Portfolio</p><h2>{{ $stats['portfolios'] }}</h2></article>
    <article class="admin-card"><p>Total Layanan</p><h2>{{ $stats['services'] }}</h2></article>
    <article class="admin-card"><p>Testimoni</p><h2>{{ $stats['testimonials'] }}</h2></article>
    <article class="admin-card"><p>Revenue Selesai</p><h2>Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</h2></article>
</div>

<div class="card-premium bg-white">
    <h2 class="text-lg font-semibold mb-4">Booking Terbaru</h2>
    <div class="overflow-x-auto">
        <table class="table-admin">
            <thead><tr><th>Kode</th><th>Customer</th><th>Tanggal</th><th>Status</th><th>Total</th></tr></thead>
            <tbody>
                @foreach ($latestBookings as $item)
                    <tr>
                        <td>{{ $item->booking_code }}</td>
                        <td>{{ $item->customer?->name }}</td>
                        <td>{{ $item->booking_date?->format('d M Y') }} {{ $item->booking_time }}</td>
                        <td>{{ $item->status }}</td>
                        <td>Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
