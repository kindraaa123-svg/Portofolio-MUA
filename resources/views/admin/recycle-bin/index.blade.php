@extends('layouts.admin')
@section('title', 'Recycle Bin')
@section('content')
@php
    $totalItems = $items->total();
    $shownItems = $items->count();
    $activeFilter = $module !== '' || $keyword !== '';
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
        <div class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full" style="background: radial-gradient(circle, color-mix(in srgb, var(--theme-primary) 24%, white), transparent 70%);"></div>
        <div class="pointer-events-none absolute -bottom-20 left-1/4 h-52 w-52 rounded-full" style="background: radial-gradient(circle, color-mix(in srgb, var(--theme-secondary) 70%, white), transparent 72%);"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold tracking-[0.16em] text-slate-500 uppercase">Admin Tools</p>
                <h1 class="mt-3 text-3xl leading-tight text-slate-900 md:text-4xl" style="font-family: 'Playfair Display', serif;">Recycle Bin</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600 md:text-base">Kelola data yang sudah dihapus, lakukan restore, atau hapus permanen dengan aman.</p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:gap-3">
                <article class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-3 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Total Data</p>
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
        <form method="GET" class="grid gap-3 md:grid-cols-[220px_minmax(0,1fr)_auto_auto] md:items-center">
            <select class="input h-11" name="module">
                <option value="">Semua module</option>
                @foreach($moduleOptions as $option)
                    <option value="{{ $option }}" @selected($module === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
            <input class="input h-11" type="text" name="q" value="{{ $keyword }}" placeholder="Cari nama, judul, atau tipe model">
            <button class="btn-primary h-11 px-5" type="submit">Filter</button>
            <a href="{{ route('admin.recycle-bin.index') }}" class="btn-secondary h-11 px-5 {{ $activeFilter ? '' : 'pointer-events-none opacity-50' }}">Reset</a>
        </form>

        @if($activeFilter)
            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                Filter aktif:
                <span class="font-semibold text-slate-800">{{ $module !== '' ? ucfirst($module) : 'Semua Module' }}</span>
                <span class="mx-1">|</span>
                <span class="font-semibold text-slate-800">{{ $keyword !== '' ? $keyword : 'Tanpa kata kunci' }}</span>
            </div>
        @endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 md:px-6 md:py-4">
            <h2 class="text-lg font-semibold text-slate-900">Data Terhapus</h2>
            <span class="text-xs text-slate-500">Per halaman: {{ $shownItems }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-admin min-w-[980px]">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Nama/Judul</th>
                        <th>Model</th>
                        <th>Dihapus Pada</th>
                        <th>Dihapus Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $moduleClass = match($item->module) {
                                'portfolio' => 'bg-indigo-100 text-indigo-700',
                                'service' => 'bg-emerald-100 text-emerald-700',
                                'addon' => 'bg-amber-100 text-amber-700',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp
                        <tr>
                            <td>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $moduleClass }}">{{ ucfirst($item->module) }}</span>
                            </td>
                            <td class="font-medium text-slate-800">{{ $item->payload['title'] ?? $item->payload['name'] ?? '-' }}</td>
                            <td>{{ class_basename($item->model_type) }} #{{ $item->model_id }}</td>
                            <td>{{ $item->deleted_at?->format('d M Y H:i:s') }}</td>
                            <td>{{ $item->deletedBy?->name ?? '-' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('admin.recycle-bin.restore', $item) }}">
                                        @csrf
                                        <button class="btn-primary" type="submit">Restore</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.recycle-bin.destroy', $item) }}" onsubmit="return confirm('Hapus permanen data ini? Tindakan ini tidak bisa dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-secondary" type="submit">Delete Permanent</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500">Tidak ada data di recycle bin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div>{{ $items->links() }}</div>
</div>
@endsection
