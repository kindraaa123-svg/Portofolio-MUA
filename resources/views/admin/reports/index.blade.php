@extends('layouts.admin')
@section('title', 'Laporan')
@section('content')
<style>
    .report-hero {
        background:
            radial-gradient(circle at 85% 20%, rgba(56, 189, 248, .25), transparent 35%),
            linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #334155 100%);
        border: 1px solid rgba(148, 163, 184, 0.22);
    }
    .report-kpi {
        border: 1px solid #dbe4ef;
        border-radius: 16px;
        background: #fff;
        padding: 16px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
    }
    .report-kpi p {
        margin: 0;
        color: #5b6b80;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 500;
    }
    .report-kpi h3 {
        margin: 10px 0 0;
        font-size: 24px;
        color: #0f172a;
        font-weight: 500;
    }
    .report-filter {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        padding: 12px;
    }
    .report-table th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .report-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 500;
        color: #0f172a;
        background: #e2e8f0;
    }
    .report-dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #0ea5e9;
    }
    .report-actions a {
        min-width: 70px;
        text-align: center;
    }
</style>

<div class="mx-auto max-w-6xl space-y-6">
    <section class="report-hero rounded-2xl px-6 py-5 text-white shadow-lg">
        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-300">Finance Dashboard</p>
        <h1 class="mt-2 text-3xl font-medium">Laporan Keuangan</h1>
        <p class="mt-1 text-sm text-slate-200">{{ $siteName }} | Periode {{ $from }} s/d {{ $to }}</p>
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        <article class="report-kpi">
            <p>Total Income</p>
            <h3>Rp {{ number_format($summary['total_income_verified'], 0, ',', '.') }}</h3>
        </article>
        <article class="report-kpi">
            <p>Total Outcome</p>
            <h3>Rp {{ number_format($summary['total_outcome_verified'], 0, ',', '.') }}</h3>
        </article>
        <article class="report-kpi">
            <p>Laba Bersih</p>
            <h3 class="{{ $summary['net_income'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                Rp {{ number_format($summary['net_income'], 0, ',', '.') }}
            </h3>
        </article>
    </section>

    <section class="card-premium bg-white">
        <form method="GET" class="report-filter grid gap-3 md:grid-cols-[1fr_1fr_auto_auto_auto]">
            <label class="field">
                <span>Dari Tanggal</span>
                <input class="input" type="date" name="from" value="{{ $from }}" required>
            </label>
            <label class="field">
                <span>Sampai Tanggal</span>
                <input class="input" type="date" name="to" value="{{ $to }}" required>
            </label>
            <input type="hidden" name="order" value="{{ $order }}">
            <button class="btn-primary self-end" type="submit">Filter</button>
            <a class="btn-secondary self-end text-center" href="{{ route('admin.reports.index') }}">Reset</a>
            <a class="btn-secondary self-end text-center" href="{{ route('admin.reports.index', ['from' => $from, 'to' => $to, 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                Urut {{ strtoupper($order === 'asc' ? 'desc' : 'asc') }}
            </a>
        </form>
    </section>

    <section class="card-premium bg-white">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-medium text-slate-900">Transaksi Per Tanggal</h2>
                <p class="text-sm text-slate-500">Pilih aksi untuk print atau export laporan harian.</p>
            </div>
            <span class="report-pill">
                <span class="report-dot"></span>
                {{ $transactionDates->count() }} tanggal transaksi
            </span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="table-admin report-table min-w-[760px]">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactionDates as $index => $date)
                        @php($dateQuery = ['from' => $date, 'to' => $date])
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="font-normal text-slate-800">{{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}</div>
                                <div class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($date)->translatedFormat('l') }}</div>
                            </td>
                            <td>
                                <div class="report-actions flex flex-wrap gap-2">
                                    <a class="btn-secondary text-xs" href="{{ route('admin.reports.print', $dateQuery) }}" target="_blank">Print</a>
                                    <a class="btn-secondary text-xs" href="{{ route('admin.reports.export-pdf', $dateQuery) }}" target="_blank">PDF</a>
                                    <a class="btn-secondary text-xs" href="{{ route('admin.reports.export-excel', $dateQuery) }}">Excel</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-7 text-center text-slate-500">Belum ada transaksi pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
