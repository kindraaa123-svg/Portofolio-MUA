@extends('layouts.admin')
@section('title', 'Backup Database')
@section('content')
<section class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="relative bg-white p-6 md:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Database Safety</p>
        <h1 class="mt-2 font-serif text-3xl leading-tight text-slate-900 md:text-4xl">Backup Database</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600">Lakukan export backup SQL, import restore, dan pantau riwayat proses backup database.</p>
    </div>
</section>

<div class="grid md:grid-cols-2 gap-6 mb-6">
    <section class="card-premium bg-white space-y-4">
        <div class="border-b border-slate-200 pb-3">
            <h2 class="text-lg font-semibold text-slate-900">Export Database (.sql)</h2>
            <p class="mt-1 text-sm text-slate-600">Buat file backup SQL terbaru dari database aktif.</p>
        </div>
        <form method="POST" action="{{ route('admin.backup.export') }}">
            @csrf
            <button class="btn-primary w-full sm:w-auto">Export SQL</button>
        </form>
    </section>

    <section class="card-premium bg-white space-y-4">
        <div class="border-b border-slate-200 pb-3">
            <h2 class="text-lg font-semibold text-slate-900">Import Database (.sql)</h2>
            <p class="mt-1 text-sm text-slate-600">Restore database menggunakan file SQL.</p>
        </div>
        <form method="POST" action="{{ route('admin.backup.import') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="sql_file" required accept=".sql,text/plain" class="input w-full">
            <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                Pastikan file SQL valid. Import akan menimpa data yang ada.
            </p>
            <button class="btn-primary w-full sm:w-auto">Import SQL</button>
        </form>
    </section>
</div>

<section class="card-premium bg-white">
    <div class="mb-4 flex items-end justify-between gap-3 border-b border-slate-200 pb-3">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Riwayat Backup & Import</h2>
            <p class="mt-1 text-sm text-slate-600">Catatan proses export/import terbaru.</p>
        </div>
        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">{{ $logs->total() }} Riwayat</span>
    </div>

    <div class="overflow-x-auto">
        <table class="table-admin min-w-[980px]">
            <thead>
                <tr>
                    <th>Tanggal/Jam</th>
                    <th>Tipe</th>
                    <th>File</th>
                    <th>Ukuran</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                        <td>
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                {{ $log->type === 'database-export' ? 'Export' : 'Import' }}
                            </span>
                        </td>
                        <td class="max-w-[260px] truncate">{{ $log->file_name }}</td>
                        <td>{{ number_format(((int) $log->file_size) / 1024, 1) }} KB</td>
                        <td>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $log->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $log->status === 'completed' ? 'Completed' : 'Failed' }}
                            </span>
                        </td>
                        <td class="max-w-[320px] truncate">{{ $log->notes ?: '-' }}</td>
                        <td>
                            @if ($log->status === 'completed' && $log->type === 'database-export')
                                <a class="btn-secondary text-xs" href="{{ route('admin.backup.download', $log) }}">Download</a>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-500 py-6">Belum ada riwayat backup/import.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</section>
@endsection
