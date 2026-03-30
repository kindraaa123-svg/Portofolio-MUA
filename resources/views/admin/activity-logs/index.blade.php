@extends('layouts.admin')
@section('title', 'Activity Log')
@section('content')
<div class="flex items-center justify-between gap-4 mb-6">
    <h1 class="text-2xl font-semibold">Activity Log</h1>
</div>

<form method="GET" class="grid md:grid-cols-3 gap-3 mb-5">
    <select class="input" name="module">
        <option value="">Semua module</option>
        @foreach($modules as $item)
            <option value="{{ $item }}" @selected($module === $item)>{{ $item }}</option>
        @endforeach
    </select>
    <input class="input" type="text" name="action" value="{{ $action }}" placeholder="Cari action">
    <button class="btn-primary">Filter</button>
</form>

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin min-w-[1200px]">
        <thead><tr><th>Tanggal/Jam</th><th>User</th><th>Level</th><th>Module</th><th>Action</th><th>IP</th><th>Lokasi</th><th>Latitude</th><th>Longitude</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                <td>{{ $log->user_name ?: ($log->user?->name ?? 'System/Public') }}</td>
                <td>{{ $log->user_level ?: ($log->user?->role?->name ?? '-') }}</td>
                <td>{{ $log->module }}</td>
                <td>{{ $log->action }}</td>
                <td>{{ $log->ip_address }}</td>
                <td>{{ $log->geo_location ?: '-' }}</td>
                <td>{{ $log->latitude ?: '-' }}</td>
                <td>{{ $log->longitude ?: '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="9">Belum ada log aktivitas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $logs->links() }}</div>
@endsection
