<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0f172a; margin: 22px; }
        .header { display: flex; align-items: center; gap: 14px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 14px; }
        .logo { width: 52px; height: 52px; object-fit: contain; border: 1px solid #e2e8f0; border-radius: 10px; }
        .title h1 { margin: 0; font-size: 24px; }
        .title p { margin: 4px 0 0; font-size: 12px; color: #475569; }
        .meta { margin: 12px 0 18px; font-size: 12px; color: #475569; }
        .section-title { margin: 18px 0 8px; font-size: 14px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; }
        th { background: #f8fafc; text-align: left; }
        .amount { text-align: right; font-variant-numeric: tabular-nums; }
        .strong { font-weight: 700; }
        .notes { margin-top: 16px; font-size: 11px; color: #64748b; }
        @media print {
            .no-print { display: none; }
            body { margin: 10mm; }
        }
    </style>
</head>
<body>
    @if($mode === 'pdf')
        <div class="no-print" style="margin-bottom:10px; font-size:12px; color:#475569;">
            Gunakan opsi <strong>Save as PDF</strong> di dialog print browser.
        </div>
    @endif

    <div class="header">
        @if($siteLogoUrl)
            <img src="{{ $siteLogoUrl }}" alt="Logo" class="logo">
        @endif
        <div class="title">
            <h1>{{ $siteName }}</h1>
            <p>Laporan Keuangan Profesional (Income & Outcome)</p>
        </div>
    </div>

    <div class="meta">
        Periode: {{ $from }} s/d {{ $to }}<br>
        Tanggal Cetak: {{ $generatedAt }}<br>
        Basis Pengakuan: <strong>Cash Basis (pembayaran verified + pengeluaran bulanan manual)</strong>
    </div>

    <p class="section-title">Laporan Laba Rugi</p>
    <table>
        <tbody>
            <tr>
                <td>Total Income (Verified)</td>
                <td class="amount">Rp {{ number_format($summary['total_income_verified'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Outcome (Verified)</td>
                <td class="amount">Rp {{ number_format($summary['total_outcome_verified'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Outcome dari transaksi verified</td>
                <td class="amount">Rp {{ number_format($summary['total_outcome_verified_payments'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Outcome dari pengeluaran bulanan manual</td>
                <td class="amount">Rp {{ number_format($summary['total_outcome_manual'], 0, ',', '.') }}</td>
            </tr>
            <tr class="strong">
                <td>Laba Bersih Periode</td>
                <td class="amount">Rp {{ number_format($summary['net_income'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p class="section-title">Rincian Income & Outcome Harian</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="amount">Income</th>
                <th class="amount">Outcome</th>
                <th class="amount">Net</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyFinancialRows as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                    <td class="amount">Rp {{ number_format($row['income'], 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($row['outcome'], 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($row['net'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tidak ada transaksi verified pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="notes">
        Catatan: Income diakui saat pembayaran verified. Outcome mencakup refund/nominal negatif verified dan pengeluaran bulanan manual.
    </p>

    <script>
        @if($autoPrint)
        window.addEventListener('load', () => window.print());
        @endif
    </script>
</body>
</html>
