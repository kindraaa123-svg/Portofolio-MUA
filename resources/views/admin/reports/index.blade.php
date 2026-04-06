@extends('layouts.admin')
@section('title', 'Laporan')
@section('content')
@php
    $nextOrder = $order === 'asc' ? 'desc' : 'asc';
    $hasData = $transactionDates->isNotEmpty();
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
        <div class="pointer-events-none absolute -right-14 -top-14 h-44 w-44 rounded-full" style="background: radial-gradient(circle, color-mix(in srgb, var(--theme-primary) 24%, white), transparent 70%);"></div>
        <div class="pointer-events-none absolute -bottom-20 left-1/3 h-52 w-52 rounded-full" style="background: radial-gradient(circle, color-mix(in srgb, var(--theme-secondary) 72%, white), transparent 72%);"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold tracking-[0.16em] text-slate-500 uppercase">Finance Report</p>
                <h1 class="mt-3 text-3xl leading-tight text-slate-900 md:text-4xl" style="font-family: 'Playfair Display', serif;">Laporan Keuangan</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600 md:text-base">{{ $siteName }} | Periode {{ $from }} s/d {{ $to }}</p>
            </div>

            <article class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-3 text-sm text-slate-600 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Tanggal Generate</p>
                <p class="mt-1 font-semibold text-slate-900">{{ $generatedAt }}</p>
            </article>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Income</p>
            <h3 class="mt-2 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_income_verified'], 0, ',', '.') }}</h3>
            <p class="mt-1 text-xs text-slate-500">Transaksi verified positif</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Outcome</p>
            <h3 class="mt-2 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_outcome_verified'], 0, ',', '.') }}</h3>
            <p class="mt-1 text-xs text-slate-500">Refund / nominal negatif + pengeluaran bulanan manual</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Laba Bersih</p>
            <h3 class="mt-2 text-2xl font-semibold {{ $summary['net_income'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">Rp {{ number_format($summary['net_income'], 0, ',', '.') }}</h3>
            <p class="mt-1 text-xs text-slate-500">Income dikurangi outcome</p>
        </article>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
        <form method="GET" class="grid gap-3 md:grid-cols-[1fr_1fr_auto_auto_auto] md:items-end">
            <label class="field">
                <span>Dari Tanggal</span>
                <input class="input" type="date" name="from" value="{{ $from }}" required>
            </label>
            <label class="field">
                <span>Sampai Tanggal</span>
                <input class="input" type="date" name="to" value="{{ $to }}" required>
            </label>
            <input type="hidden" name="order" value="{{ $order }}">
            <button class="btn-primary h-11 px-5" type="submit">Filter</button>
            <a class="btn-secondary h-11 px-5 text-center" href="{{ route('admin.reports.index') }}">Reset</a>
            <a class="btn-secondary h-11 px-5 text-center" href="{{ route('admin.reports.index', ['from' => $from, 'to' => $to, 'order' => $nextOrder]) }}">Urut {{ strtoupper($nextOrder) }}</a>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-900">Input Pengeluaran Bulanan</h2>
                <p class="text-sm text-slate-500">Klik tombol tambah untuk input pengeluaran bulanan. Data akan disimpan tanpa reload halaman.</p>
            </div>
            <button id="open-monthly-expense-modal" type="button" class="btn-primary h-11 px-5">Tambah Pengeluaran</button>
        </article>

        <aside class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
            <h3 class="text-base font-semibold text-slate-900">Pengeluaran Bulanan Terbaru</h3>
            <p class="mt-1 text-xs text-slate-500">Data 12 bulan terakhir</p>

            <div id="monthly-expense-list" class="mt-3 space-y-2">
                @forelse($monthlyExpenses as $expense)
                    <div data-period="{{ $expense->period_month?->format('Y-m-d') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $expense->period_month?->translatedFormat('F Y') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Rp {{ number_format((float) $expense->amount, 0, ',', '.') }}</p>
                        @if($expense->note)
                            <p class="text-xs text-slate-500">{{ $expense->note }}</p>
                        @endif
                    </div>
                @empty
                    <p id="monthly-expense-empty-state" class="rounded-xl border border-dashed border-slate-300 px-3 py-4 text-center text-xs text-slate-500">Belum ada data pengeluaran bulanan.</p>
                @endforelse
            </div>
        </aside>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 md:px-6 md:py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Transaksi Per Tanggal</h2>
                <p class="text-sm text-slate-500">Aksi cepat untuk print, PDF, atau Excel per hari transaksi.</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                <span class="h-2 w-2 rounded-full" style="background-color: var(--theme-primary);"></span>
                {{ $transactionDates->count() }} tanggal transaksi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-admin min-w-[820px]">
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
                            <td class="font-medium text-slate-700">{{ $index + 1 }}</td>
                            <td>
                                <p class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}</p>
                                <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($date)->translatedFormat('l') }}</p>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a class="btn-secondary text-xs" href="{{ route('admin.reports.print', $dateQuery) }}" target="_blank">Print</a>
                                    <a class="btn-secondary text-xs" href="{{ route('admin.reports.export-pdf', $dateQuery) }}" target="_blank">PDF</a>
                                    <a class="btn-secondary text-xs" href="{{ route('admin.reports.export-excel', $dateQuery) }}">Excel</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-slate-500">Belum ada transaksi pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(! $hasData)
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-500 md:px-6">
                Coba ubah rentang tanggal untuk melihat data laporan lainnya.
            </div>
        @endif
    </section>
</div>

<div id="monthly-expense-modal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/60 p-3">
    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl md:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Tambah Pengeluaran Bulanan</h3>
                <p class="text-sm text-slate-500">Input nominal pengeluaran untuk bulan berjalan ({{ \Carbon\Carbon::createFromFormat('Y-m', $manualExpenseInputMonth)->translatedFormat('F Y') }}).</p>
            </div>
            <button id="close-monthly-expense-modal" type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-600 hover:bg-slate-50">Tutup</button>
        </div>

        <form id="monthly-expense-form" class="mt-4 grid gap-3 md:grid-cols-2">
            @csrf
            <input type="hidden" name="period_month" value="{{ $manualExpenseInputMonth }}">
            <label class="field">
                <span>Nama Pengeluaran</span>
                <input class="input" type="text" name="expense_name" maxlength="190" placeholder="Contoh: Sewa studio" required>
            </label>
            <label class="field">
                <span>Nominal</span>
                <input class="input" type="number" name="amount" min="0" step="1000" placeholder="Contoh: 2500000" required>
            </label>
            <div id="monthly-expense-form-error" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 md:col-span-2"></div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button id="monthly-expense-cancel" type="button" class="btn-secondary">Batal</button>
                <button id="monthly-expense-submit" type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('monthly-expense-modal');
    const openBtn = document.getElementById('open-monthly-expense-modal');
    const closeBtn = document.getElementById('close-monthly-expense-modal');
    const cancelBtn = document.getElementById('monthly-expense-cancel');
    const form = document.getElementById('monthly-expense-form');
    const submitBtn = document.getElementById('monthly-expense-submit');
    const errorBox = document.getElementById('monthly-expense-form-error');
    const list = document.getElementById('monthly-expense-list');
    const emptyState = document.getElementById('monthly-expense-empty-state');
    const endpoint = @json(route('admin.reports.store-monthly-expense'));

    if (!modal || !openBtn || !form || !submitBtn || !list) return;

    const rupiah = (amount) => `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(Number(amount) || 0))}`;
    const esc = (str) => String(str ?? '').replace(/[&<>"']/g, (s) => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;' }[s]));

    const openModal = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        errorBox.classList.add('hidden');
        errorBox.textContent = '';
    };

    openBtn.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorBox.classList.add('hidden');
        errorBox.textContent = '';
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-60', 'cursor-not-allowed');

        try {
            const formData = new FormData(form);
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const payload = await response.json();
            if (!response.ok) {
                const messages = payload?.errors ? Object.values(payload.errors).flat() : [payload?.message || 'Gagal menyimpan pengeluaran bulanan.'];
                throw new Error(messages.join(' '));
            }

            const expense = payload.expense || {};
            const noteHtml = expense.expense_name ? `<p class="text-xs text-slate-500">${esc(expense.expense_name)}</p>` : '';
            const period = expense.period_month || '';
            const itemHtml = `<div data-period="${esc(period)}" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">${esc(expense.period_label || expense.period_month || '-')}</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">${rupiah(expense.amount || 0)}</p>
                ${noteHtml}
            </div>`;

            if (emptyState) emptyState.remove();
            const existing = period
                ? Array.from(list.querySelectorAll('[data-period]')).find((node) => node.dataset.period === period)
                : null;
            if (existing) {
                existing.outerHTML = itemHtml;
            } else {
                list.insertAdjacentHTML('afterbegin', itemHtml);
            }
            form.reset();
            closeModal();
        } catch (error) {
            errorBox.textContent = error.message || 'Terjadi kesalahan.';
            errorBox.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    });
});
</script>
@endsection

