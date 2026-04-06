@extends('layouts.admin')
@section('title', 'Kelola Reservasi')
@section('content')
@php
    $statusOptions = ['pending','confirmed','on_process','completed','cancelled'];
    $statusLabel = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'on_process' => 'On Process',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
    $totalItems = $bookings->total();
    $shownItems = $bookings->count();
    $activeFilter = $search !== '' || $status !== '';
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
        <div class="pointer-events-none absolute -right-16 -top-14 h-44 w-44 rounded-full" style="background: radial-gradient(circle, color-mix(in srgb, var(--theme-primary) 22%, white), transparent 70%);"></div>
        <div class="pointer-events-none absolute -bottom-20 left-1/3 h-52 w-52 rounded-full" style="background: radial-gradient(circle, color-mix(in srgb, var(--theme-secondary) 74%, white), transparent 72%);"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold tracking-[0.16em] text-slate-500 uppercase">Admin Booking</p>
                <h1 class="mt-3 text-3xl leading-tight text-slate-900 md:text-4xl" style="font-family: 'Playfair Display', serif;">Kelola Reservasi</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600 md:text-base">Pantau status reservasi, validasi pembayaran DP, dan kontrol pelunasan dalam satu halaman.</p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:gap-3">
                <article class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-3 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Total Reservasi</p>
                    <p class="mt-1 text-xl font-semibold text-slate-900">{{ $totalItems }}</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-3 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Ditampilkan</p>
                    <p class="mt-1 text-xl font-semibold text-slate-900">{{ $shownItems }}</p>
                </article>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <form method="GET" class="grid gap-3 md:flex md:flex-1 md:items-center">
                <input class="input h-11 md:w-[320px]" type="text" name="q" value="{{ $search }}" placeholder="Cari kode reservasi / nama customer">
                <select class="input h-11 md:w-[220px]" name="status">
                    <option value="">Semua status</option>
                    @foreach($statusOptions as $s)
                        <option value="{{ $s }}" @selected($status === $s)>{{ $statusLabel[$s] }}</option>
                    @endforeach
                </select>
                <button class="btn-primary h-11 px-5" type="submit">Filter</button>
                <a href="{{ route('admin.bookings.index') }}" class="btn-secondary h-11 px-5 {{ $activeFilter ? '' : 'pointer-events-none opacity-50' }}">Reset</a>
            </form>

            <a class="btn-secondary h-11 px-5" href="{{ route('admin.bookings.export') }}">Export CSV</a>
        </div>

        @if($activeFilter)
            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                Filter aktif:
                <span class="font-semibold text-slate-800">{{ $search !== '' ? $search : 'Tanpa keyword' }}</span>
                <span class="mx-1">|</span>
                <span class="font-semibold text-slate-800">{{ $status !== '' ? ($statusLabel[$status] ?? $status) : 'Semua Status' }}</span>
            </div>
        @endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 md:px-6 md:py-4">
            <h2 class="text-lg font-semibold text-slate-900">Daftar Reservasi</h2>
            <span class="text-xs text-slate-500">Per halaman: {{ $shownItems }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-admin min-w-[1320px]">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Customer</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>DP</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Verifikasi DP</th>
                        <th>Aksi Pelunasan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $item)
                        @php
                            $dpPayment = $item->payments->where('payment_type', 'dp')->sortByDesc('id')->first();
                            $finalPayment = $item->payments->where('payment_type', 'final')->sortByDesc('id')->first();
                            $finalAmount = max(0, (float) $item->grand_total - (float) $item->dp_amount);

                            $bookingStatusClass = match($item->status) {
                                'completed' => 'bg-emerald-100 text-emerald-700',
                                'confirmed' => 'bg-blue-100 text-blue-700',
                                'on_process' => 'bg-indigo-100 text-indigo-700',
                                'pending' => 'bg-amber-100 text-amber-700',
                                'cancelled' => 'bg-rose-100 text-rose-700',
                                default => 'bg-slate-100 text-slate-700',
                            };

                            $paymentStatusClass = match($item->payment_status) {
                                'paid' => 'bg-emerald-100 text-emerald-700',
                                'dp_paid' => 'bg-blue-100 text-blue-700',
                                'unpaid' => 'bg-amber-100 text-amber-700',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp

                        <tr>
                            <td class="font-semibold text-slate-800">{{ $item->booking_code }}</td>
                            <td>
                                <p class="font-medium text-slate-800">{{ $item->customer?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $item->customer?->phone ?? '-' }}</p>
                            </td>
                            <td>{{ $item->booking_date?->format('d M Y') }} {{ $item->booking_time }}</td>
                            <td class="font-medium text-slate-800">Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->dp_amount, 0, ',', '.') }}</td>
                            <td>
                                <div class="space-y-1">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $bookingStatusClass }}">{{ str_replace('_', ' ', $item->status) }}</span>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase {{ $paymentStatusClass }}">{{ $item->payment_status }}</span>
                                </div>
                            </td>
                            <td>
                                <p class="text-xs"><span class="font-semibold">Status Lunas:</span> {{ $item->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}</p>
                                <p class="text-xs"><span class="font-semibold">Sisa Bayar:</span> Rp {{ number_format($finalAmount, 0, ',', '.') }}</p>
                            </td>
                            <td>
                                @if($dpPayment)
                                    <div class="space-y-2 text-xs">
                                        <p>{{ $dpPayment->payer_name }} | {{ $dpPayment->bank_name ?? '-' }}</p>
                                        <p>{{ $dpPayment->paid_at?->format('d M Y H:i') }}</p>
                                        @if($dpPayment->proof_image)
                                            <a class="btn-secondary text-xs" href="{{ asset('storage/' . $dpPayment->proof_image) }}" target="_blank">Lihat Bukti</a>
                                        @endif
                                        @if($dpPayment->status === 'pending')
                                            <form method="POST" action="{{ route('admin.bookings.verify-payment', $dpPayment) }}" class="space-y-1">
                                                @csrf
                                                <div class="flex flex-wrap gap-2">
                                                    <button class="btn-primary text-xs" type="submit" name="status" value="verified">Disetujui</button>
                                                    <button class="btn-secondary text-xs" type="submit" name="status" value="rejected">Ditolak</button>
                                                </div>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-500">Verifikasi DP sudah final.</span>
                                        @endif
                                        <p>Status bukti: <strong>{{ $dpPayment->status }}</strong></p>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500">Belum ada bukti DP</span>
                                @endif
                            </td>
                            <td>
                                <div class="space-y-2 text-xs">
                                    <form method="POST" action="{{ route('admin.bookings.set-settlement-status', $item) }}">
                                        @csrf
                                        <input type="hidden" name="settlement_status" value="paid">
                                        <button class="btn-primary text-xs" type="submit" @disabled($item->payment_status === 'paid')>Set Lunas</button>
                                    </form>

                                    @if($finalPayment)
                                        <p>{{ $finalPayment->payer_name }} | {{ $finalPayment->bank_name ?? '-' }}</p>
                                        <p>{{ $finalPayment->paid_at?->format('d M Y H:i') }}</p>
                                        <p>Status: <strong>{{ $finalPayment->status }}</strong></p>
                                    @else
                                        <span class="text-xs text-slate-500">Belum ada data pelunasan.</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-500">Belum ada data reservasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div>{{ $bookings->links() }}</div>
</div>
@endsection
