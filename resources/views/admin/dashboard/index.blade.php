@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
        <div class="pointer-events-none absolute -right-14 -top-14 h-40 w-40 rounded-full" style="background: radial-gradient(circle, color-mix(in srgb, var(--theme-primary) 28%, white), transparent 68%);"></div>
        <div class="pointer-events-none absolute -bottom-20 left-1/3 h-48 w-48 rounded-full" style="background: radial-gradient(circle, color-mix(in srgb, var(--theme-secondary) 74%, white), transparent 70%);"></div>

        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold tracking-[0.16em] text-slate-500 uppercase">Admin Overview</p>
                <h1 class="mt-3 text-3xl leading-tight text-slate-900 md:text-4xl" style="font-family: 'Playfair Display', serif;">Dashboard Operasional</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600 md:text-base">Ringkasan performa booking, pemasukan, dan aktivitas terbaru untuk monitoring harian.</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-3 text-sm text-slate-600 shadow-sm backdrop-blur">
                <p class="font-semibold text-slate-800">{{ now()->format('d M Y') }}</p>
                <p>Update otomatis berdasarkan transaksi tervalidasi.</p>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Booking</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['bookings'] }}</h2>
            <p class="mt-1 text-xs text-slate-500">Semua reservasi tercatat</p>
        </article>
        <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pending Booking</p>
            <h2 class="mt-2 text-3xl font-semibold text-amber-600">{{ $stats['pendingBookings'] }}</h2>
            <p class="mt-1 text-xs text-slate-500">Butuh konfirmasi lanjutan</p>
        </article>
        <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Portfolio</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['portfolios'] }}</h2>
            <p class="mt-1 text-xs text-slate-500">Konten tampil di website</p>
        </article>
        <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Layanan</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['services'] }}</h2>
            <p class="mt-1 text-xs text-slate-500">Paket dan addon aktif</p>
        </article>
        <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Testimoni</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['testimonials'] }}</h2>
            <p class="mt-1 text-xs text-slate-500">Ulasan dari pelanggan</p>
        </article>
        <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Revenue Selesai</p>
            <h2 class="mt-2 text-3xl font-semibold" style="color: color-mix(in srgb, var(--theme-primary) 78%, black);">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</h2>
            <p class="mt-1 text-xs text-slate-500">Akumulasi booking completed</p>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-6">
            <div id="income-chart-root" data-points='@json($hourlyToday)' class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900 md:text-2xl">Grafik Pemasukan Harian</h2>
                        <p class="text-sm text-slate-500">Per jam untuk transaksi hari ini</p>
                    </div>
                    <select id="chart-type-select" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300">
                        <option value="bar">Batang</option>
                        <option value="line">Garis</option>
                    </select>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3 md:p-4">
                    <div id="chart-tooltip" class="pointer-events-none absolute hidden rounded-md bg-slate-900 px-2 py-1 text-xs font-medium text-white shadow-lg"></div>
                    <div id="chart-canvas" class="h-[340px] md:h-[380px]"></div>
                </div>
            </div>
        </article>

        <aside class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
            <h3 class="text-lg font-semibold text-slate-900">Ringkasan Pemasukan</h3>
            <div class="space-y-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Hari Ini</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($incomeReport['today']['amount'], 0, ',', '.') }}</p>
                    <p class="text-sm text-slate-500">{{ $incomeReport['today']['count'] }} transaksi</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kemarin</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($transactionSummary['yesterday']['amount'], 0, ',', '.') }}</p>
                    <p class="text-sm text-slate-500">{{ $transactionSummary['yesterday']['count'] }} transaksi</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bulan Ini</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($transactionSummary['this_month']['amount'], 0, ',', '.') }}</p>
                    <p class="text-sm text-slate-500">{{ $transactionSummary['this_month']['count'] }} transaksi</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bulan Lalu</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($transactionSummary['last_month']['amount'], 0, ',', '.') }}</p>
                    <p class="text-sm text-slate-500">{{ $transactionSummary['last_month']['count'] }} transaksi</p>
                </div>
            </div>
        </aside>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 md:px-6 md:py-4">
            <h2 class="text-lg font-semibold text-slate-900">Booking Terbaru</h2>
            <span class="text-xs text-slate-500">{{ $latestBookings->count() }} data ditampilkan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-admin min-w-[760px]">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Customer</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestBookings as $item)
                        <tr>
                            <td class="font-medium text-slate-800">{{ $item->booking_code }}</td>
                            <td>{{ $item->customer?->name }}</td>
                            <td>{{ $item->booking_date?->format('d M Y') }} {{ $item->booking_time }}</td>
                            <td>
                                @php
                                    $statusClass = match($item->status) {
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'cancelled' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $statusClass }}">{{ $item->status }}</span>
                            </td>
                            <td class="font-semibold text-slate-900">Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">Belum ada data booking.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
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
        canvas.innerHTML = '<div class="grid h-full place-items-center rounded-lg border border-slate-200 bg-white text-sm text-slate-500">Belum ada income hari ini</div>';
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
            bars += `<rect class="chart-hit" data-label="${p.hour}" data-amount="${amount}" x="${x}" y="${y}" width="${barWidth}" height="${barHeight}" rx="2" fill="${getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim() || '#274f97'}" />`;

            if (i % 2 === 0 || i === points.length - 1) {
                labels += `<text x="${x + (barWidth / 2)}" y="${height - 12}" text-anchor="middle" font-size="11" fill="#64748b" style="font-family:${chartFont};">${p.hour.slice(0, 2)}:00</text>`;
            }
        });

        canvas.innerHTML = `<div class="h-full rounded-lg border border-slate-200 bg-white p-2">
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
        const strokeColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim() || '#274f97';

        const polyPoints = points.map((p, i) => `${toX(i)},${toY(Number(p.amount) || 0)}`).join(' ');
        const dots = points.map((p, i) => {
            const amount = Number(p.amount) || 0;
            const x = toX(i);
            const y = toY(amount);
            return `<circle class="chart-hit" data-label="${p.hour}" data-amount="${amount}" cx="${x}" cy="${y}" r="4" fill="${strokeColor}" />`;
        }).join('');
        const labels = points.map((p, i) => {
            if (i % 2 !== 0 && i !== points.length - 1) return '';
            return `<text x="${toX(i)}" y="${height - 12}" text-anchor="middle" font-size="11" fill="#64748b" style="font-family:${chartFont};">${p.hour.slice(0, 2)}:00</text>`;
        }).join('');

        canvas.innerHTML = `<div class="h-full rounded-lg border border-slate-200 bg-white p-3">
            <svg viewBox="0 0 ${width} ${height}" class="h-full w-full" style="font-family:${chartFont};">
                ${renderGridLines(width, height, margin, 5)}
                <polyline points="${polyPoints}" fill="none" stroke="${strokeColor}" stroke-width="2.5" />
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
