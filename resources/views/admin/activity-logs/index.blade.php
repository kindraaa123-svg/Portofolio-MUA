@extends('layouts.admin')
@section('title', 'Activity Log')
@section('content')
<section class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="relative bg-white p-6 md:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Audit Trail</p>
        <h1 class="mt-2 font-serif text-3xl leading-tight text-slate-900 md:text-4xl">Activity Log</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600">Pantau semua aktivitas sistem dan admin untuk kebutuhan monitoring dan audit.</p>
    </div>
</section>

<section class="card-premium bg-white mb-5">
    @unless($isSuperadmin)
        <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            Log aktivitas milik superadmin disembunyikan untuk akun Anda.
        </div>
    @endunless

    <form method="GET" class="grid gap-3 md:grid-cols-[1fr_1fr_auto_auto]">
        <label class="field">
            <span>Module</span>
            <select class="input" name="module">
                <option value="">Semua module</option>
                @foreach($modules as $item)
                    <option value="{{ $item }}" @selected($module === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </label>

        <label class="field">
            <span>Action</span>
            <input class="input" type="text" name="action" value="{{ $action }}" placeholder="Cari action">
        </label>

        <button class="btn-primary self-end" type="submit">Filter</button>
        <a class="btn-secondary self-end" href="{{ route('admin.activity-logs.index') }}">Reset</a>
    </form>
</section>

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin min-w-[1400px]">
        <thead>
            <tr>
                <th>Tanggal/Jam</th>
                <th>User</th>
                <th>Level</th>
                <th>Module</th>
                <th>Action</th>
                <th>Detail Aktivitas</th>
                <th>IP</th>
                <th>Lokasi</th>
                <th>Latitude</th>
                <th>Longitude</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                <td>{{ $log->user_name ?: ($log->user?->name ?? 'System/Public') }}</td>
                <td>
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                        {{ $log->user_level ?: ($log->user?->role?->name ?? '-') }}
                    </span>
                </td>
                <td>
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                        {{ $log->module }}
                    </span>
                </td>
                <td>
                    @php
                        $actionTone = match (true) {
                            str_contains($log->action, 'delete'),
                            str_contains($log->action, 'failed') => 'bg-red-100 text-red-700',
                            str_contains($log->action, 'create'),
                            str_contains($log->action, 'import'),
                            str_contains($log->action, 'restore') => 'bg-emerald-100 text-emerald-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                    @endphp
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $actionTone }}">
                        {{ $log->action }}
                    </span>
                </td>
                <td>{{ $log->description() }}</td>
                <td><code class="text-xs">{{ $log->ip_address ?: '-' }}</code></td>
                <td>{{ $log->geo_location ?: '-' }}</td>
                <td>{{ $log->latitude ?: '-' }}</td>
                <td>{{ $log->longitude ?: '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center text-slate-500">Belum ada log aktivitas.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $logs->links() }}</div>
@endsection
