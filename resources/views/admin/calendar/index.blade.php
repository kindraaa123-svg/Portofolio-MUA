@extends('layouts.admin')
@section('title', 'Kalender Booking')
@section('content')
<h1 class="text-2xl font-semibold mb-6">Kalender Booking</h1>
<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th>Tanggal</th><th>Jam</th><th>Kode</th><th>Customer</th><th>Status</th></tr></thead>
        <tbody>
            @foreach ($bookings as $item)
                <tr>
                    <td>{{ $item->booking_date?->format('d M Y') }}</td>
                    <td>{{ $item->booking_time }}</td>
                    <td>{{ $item->booking_code }}</td>
                    <td>{{ $item->customer?->name }}</td>
                    <td>{{ $item->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
