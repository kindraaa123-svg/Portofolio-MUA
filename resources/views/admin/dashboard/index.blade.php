@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="space-y-6">
<h1 class="text-3xl font-semibold text-slate-900">Dashboard</h1>

<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
    <article class="admin-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Booking</p>
        <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['bookings'] }}</h2>
    </article>
    <article class="admin-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pending Booking</p>
        <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['pendingBookings'] }}</h2>
    </article>
    <article class="admin-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Portfolio</p>
        <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['portfolios'] }}</h2>
    </article>
    <article class="admin-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Layanan</p>
        <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['services'] }}</h2>
    </article>
    <article class="admin-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Testimoni</p>
        <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['testimonials'] }}</h2>
    </article>
    <article class="admin-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Revenue Selesai</p>
        <h2 class="mt-2 text-3xl font-semibold text-slate-900">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</h2>
    </article>
</div>

<div class="flex flex-col gap-6 md:flex-row md:items-start">
    <section class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white p-6">
        <div id="income-chart-root" data-points='@json($hourlyToday)' class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl leading-tight font-semibold text-slate-800 md:text-2xl" style="font-family: 'DM Sans', sans-serif;">Grafik Pemasukan Harian (Hari Ini)</h2>
                <select id="chart-type-select" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    <option value="bar">Batang</option>
                    <option value="line">Garis</option>
                </select>
            </div>

            <div class="relative rounded-xl border border-slate-200 bg-white p-4">
                <div id="chart-tooltip" class="pointer-events-none absolute hidden rounded-md bg-slate-900 px-2 py-1 text-xs font-medium text-white shadow-lg"></div>
                <div id="chart-canvas" class="h-[380px]"></div>
            </div>
        </div>
    </section>

    <aside class="w-full shrink-0 rounded-2xl border border-slate-200 bg-white p-4 md:w-72 lg:w-80 lg:p-5">
        <h3 class="mb-3 text-lg font-semibold text-slate-800" style="font-family: 'DM Sans', sans-serif;">Ringkasan Pemasukan</h3>
        <div class="space-y-4">
            <div>
                <p class="text-base font-semibold text-slate-500">Hari Ini</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($incomeReport['today']['amount'], 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">{{ $incomeReport['today']['count'] }} transaksi</p>
            </div>
            <div>
                <p class="text-base font-semibold text-slate-500">Kemarin</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($transactionSummary['yesterday']['amount'], 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">{{ $transactionSummary['yesterday']['count'] }} transaksi</p>
            </div>
            <div>
                <p class="text-base font-semibold text-slate-500">Bulan Ini</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($transactionSummary['this_month']['amount'], 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">{{ $transactionSummary['this_month']['count'] }} transaksi</p>
            </div>
            <div>
                <p class="text-base font-semibold text-slate-500">Bulan Lalu</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($transactionSummary['last_month']['amount'], 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">{{ $transactionSummary['last_month']['count'] }} transaksi</p>
            </div>
        </div>
    </aside>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('income-chart-root');
    if (!root) return;

    const points = JSON.parse(root.dataset.points || '[]');
    const canvas = document.getElementById('chart-canvas');
    const tooltip = document.getElementById('chart-tooltip');
    const typeSelect = document.getElementById('chart-type-select');
    const chartFont = "'DM Sans', ui-sans-serif, system-ui, sans-serif";
    const maxAmountRaw = Math.max(...points.map((p) => Number(p.amount) || 0), 0);
    const maxAmount = maxAmountRaw > 0 ? maxAmountRaw : 1;

    const formatRupiah = (amount) => `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(Number(amount) || 0))}`;
    const formatAxis = (value) => {
        if (value >= 1000000) return `${(value / 1000000).toFixed(1)} jt`;
        if (value >= 1000) return `${Math.round(value / 1000)} rb`;
        return value.toFixed(0);
    };

    const showTooltip = (event, label, amount) => {
        tooltip.textContent = `${label}: ${formatRupiah(amount)}`;
        tooltip.classList.remove('hidden');
        const parentRect = canvas.getBoundingClientRect();
        const x = event.clientX - parentRect.left + 10;
        const y = event.clientY - parentRect.top - 34;
        tooltip.style.left = `${x}px`;
        tooltip.style.top = `${Math.max(4, y)}px`;
    };
    const hideTooltip = () => tooltip.classList.add('hidden');

    const renderEmpty = () => {
        canvas.innerHTML = '<div class="grid h-full place-items-center rounded-lg border border-slate-100 bg-white text-sm text-slate-500">Belum ada income hari ini</div>';
    };

    const renderGridLines = (width, height, margin, steps = 5) => {
        let html = '';
        for (let i = 0; i <= steps; i++) {
            const y = margin.top + ((height - margin.top - margin.bottom) / steps) * i;
            const value = (maxAmount / steps) * (steps - i);
            html += `<line x1="${margin.left}" y1="${y}" x2="${width - margin.right}" y2="${y}" stroke="#e2e8f0" stroke-width="1" />`;
            html += `<text x="${margin.left - 8}" y="${y + 4}" text-anchor="end" font-size="11" fill="#64748b" style="font-family:${chartFont};">${formatAxis(value)}</text>`;
        }
        return html;
    };

    const renderBar = () => {
        if (!points.length) return renderEmpty();

        const width = Math.max(canvas.clientWidth || 700, 700);
        const height = 360;
        const margin = { top: 18, right: 12, bottom: 44, left: 56 };
        const plotWidth = width - margin.left - margin.right;
        const plotHeight = height - margin.top - margin.bottom;
        const step = plotWidth / points.length;
        const barWidth = Math.max(5, step * 0.68);

        let bars = '';
        let labels = '';
        points.forEach((p, i) => {
            const amount = Number(p.amount) || 0;
            const barHeight = amount > 0 ? Math.max(3, (amount / maxAmount) * plotHeight) : 0;
            const x = margin.left + (i * step) + ((step - barWidth) / 2);
            const y = margin.top + plotHeight - barHeight;
            bars += `<rect class="chart-hit" data-label="${p.hour}" data-amount="${amount}" x="${x}" y="${y}" width="${barWidth}" height="${barHeight}" rx="2" fill="#274f97" />`;

            if (i % 2 === 0 || i === points.length - 1) {
                labels += `<text x="${x + (barWidth / 2)}" y="${height - 12}" text-anchor="middle" font-size="11" fill="#64748b" style="font-family:${chartFont};">${p.hour.slice(0, 2)}:00</text>`;
            }
        });

        canvas.innerHTML = `<div class="h-full rounded-lg border border-slate-100 bg-white p-2">
            <svg viewBox="0 0 ${width} ${height}" class="h-full w-full" style="font-family:${chartFont};">
                ${renderGridLines(width, height, margin, 5)}
                ${bars}
                ${labels}
            </svg>
        </div>`;
    };

    const renderLine = () => {
        if (!points.length) return renderEmpty();

        const width = Math.max(canvas.clientWidth || 300, 300);
        const height = 360;
        const margin = { top: 18, right: 12, bottom: 44, left: 56 };
        const plotWidth = width - margin.left - margin.right;
        const plotHeight = height - margin.top - margin.bottom;
        const step = plotWidth / Math.max(points.length - 1, 1);
        const toY = (value) => margin.top + plotHeight - ((value / maxAmount) * plotHeight);
        const toX = (idx) => margin.left + (idx * step);

        const polyPoints = points.map((p, i) => `${toX(i)},${toY(Number(p.amount) || 0)}`).join(' ');
        const dots = points.map((p, i) => {
            const amount = Number(p.amount) || 0;
            const x = toX(i);
            const y = toY(amount);
            return `<circle class="chart-hit" data-label="${p.hour}" data-amount="${amount}" cx="${x}" cy="${y}" r="4" fill="#274f97" />`;
        }).join('');
        const labels = points.map((p, i) => {
            if (i % 2 !== 0 && i !== points.length - 1) return '';
            return `<text x="${toX(i)}" y="${height - 12}" text-anchor="middle" font-size="11" fill="#64748b" style="font-family:${chartFont};">${p.hour.slice(0, 2)}:00</text>`;
        }).join('');

        canvas.innerHTML = `<div class="h-full rounded-lg border border-slate-100 bg-white p-3">
            <svg viewBox="0 0 ${width} ${height}" class="h-full w-full" style="font-family:${chartFont};">
                ${renderGridLines(width, height, margin, 5)}
                <polyline points="${polyPoints}" fill="none" stroke="#274f97" stroke-width="2.5" />
                ${dots}
                ${labels}
            </svg>
        </div>`;
    };

    const setMode = (mode) => {
        hideTooltip();
        if (mode === 'line') renderLine();
        else renderBar();
    };

    root.addEventListener('mousemove', (event) => {
        const target = event.target.closest('.chart-hit');
        if (!target) {
            hideTooltip();
            return;
        }
        showTooltip(event, target.dataset.label || '-', target.dataset.amount || 0);
    });
    root.addEventListener('mouseleave', hideTooltip);
    typeSelect?.addEventListener('change', (event) => setMode(event.target.value || 'bar'));
    window.addEventListener('resize', () => {
        const active = typeSelect?.value || 'bar';
        setMode(active);
    });

    setMode('bar');
});
</script>
@endsection
