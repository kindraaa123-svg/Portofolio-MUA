<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; margin: 24px; }
        h1, h2 { margin: 0 0 12px; }
        .meta { margin-bottom: 16px; font-size: 13px; color: #475569; }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .brand img { width: 52px; height: 52px; object-fit: contain; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; }
        .brand h2 { margin: 0; font-size: 20px; }
        .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 20px; }
        .card { border: 1px solid #dbeafe; border-radius: 10px; padding: 10px 12px; background: #f8fafc; }
        .card p { margin: 0; font-size: 12px; color: #64748b; }
        .card h3 { margin: 6px 0 0; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
        .note { margin-bottom: 12px; padding: 8px 10px; border: 1px solid #e2e8f0; background: #fffbeb; font-size: 12px; }
        @media print {
            .no-print { display: none; }
            body { margin: 10mm; }
        }
    </style>
</head>
<body>
    @if($mode === 'pdf')
        <div class="note no-print">Gunakan pilihan <strong>Save as PDF</strong> pada dialog print browser untuk menyimpan sebagai PDF.</div>
    @endif

    <div class="brand">
        @if($siteLogoUrl)
            <img src="{{ $siteLogoUrl }}" alt="Website Logo">
        @endif
        <div>
            <h2>{{ $siteName }}</h2>
            <h1>Laporan Keuangan</h1>
        </div>
    </div>
    <div class="meta">
        Periode: {{ $from }} s/d {{ $to }} |
        Keyword: {{ $keyword !== '' ? $keyword : '-' }} |
        Status Booking: {{ $status !== '' ? $status : '-' }} |
        Status Pembayaran: {{ $paymentStatus !== '' ? $paymentStatus : '-' }}
    </div>

    <div class="grid">
        <article class="card"><p>Total Booking</p><h3>{{ $summary['total_bookings'] }}</h3></article>
        <article class="card"><p>Booking Pending</p><h3>{{ $summary['total_pending'] }}</h3></article>
        <article class="card"><p>Booking Confirmed</p><h3>{{ $summary['total_confirmed'] }}</h3></article>
        <article class="card"><p>Total Transaksi</p><h3>Rp {{ number_format($summary['total_transactions'], 0, ',', '.') }}</h3></article>
        <article class="card"><p>DP Verified</p><h3>Rp {{ number_format($summary['total_dp_verified'], 0, ',', '.') }}</h3></article>
        <article class="card"><p>DP Pending</p><h3>Rp {{ number_format($summary['total_dp_pending'], 0, ',', '.') }}</h3></article>
    </div>

    <h2>Rekap Booking</h2>
    <table>
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
            <tr><td colspan="5">Tidak ada data booking pada filter ini.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Rekap Pembayaran</h2>
    <table>
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
            <tr><td colspan="5">Tidak ada data pembayaran pada filter ini.</td></tr>
        @endforelse
        </tbody>
    </table>

    <script>
        @if($autoPrint)
        window.addEventListener('load', () => window.print());
        @endif
    </script>
</body>
</html>
