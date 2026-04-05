<style>
    body { font-family: dejavusans, sans-serif; font-size: 10pt; color: #111827; }
    h1 { font-size: 17pt; margin: 0 0 4px 0; }
    h2 { font-size: 11pt; margin: 14px 0 6px 0; }
    .meta { font-size: 9pt; color: #374151; margin-bottom: 10px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #d1d5db; padding: 6px; }
    th { background-color: #f3f4f6; text-align: left; }
    .amount { text-align: right; }
</style>

<h1>{{ $siteName }}</h1>
<div class="meta">
    Laporan Keuangan Profesional (Income & Outcome)<br>
    Periode: {{ $from }} s/d {{ $to }}<br>
    Tanggal Cetak: {{ $generatedAt }}<br>
    Basis Pengakuan: Cash Basis (hanya pembayaran status verified)
</div>

<h2>Laporan Laba Rugi</h2>
<table>
    <tr>
        <td>Total Income (Verified)</td>
        <td class="amount">Rp {{ number_format($summary['total_income_verified'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Total Outcome (Verified)</td>
        <td class="amount">Rp {{ number_format($summary['total_outcome_verified'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td><strong>Laba Bersih Periode</strong></td>
        <td class="amount"><strong>Rp {{ number_format($summary['net_income'], 0, ',', '.') }}</strong></td>
    </tr>
</table>

<h2>Rincian Income & Outcome Harian</h2>
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
