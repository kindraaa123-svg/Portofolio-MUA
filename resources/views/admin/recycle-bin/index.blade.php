@extends('layouts.admin')
@section('title', 'Recycle Bin')
@section('content')
<h1 class="text-2xl font-semibold mb-6">Recycle Bin</h1>

<form method="GET" class="grid md:grid-cols-3 gap-3 mb-5">
    <select class="input" name="module">
        <option value="">Semua module</option>
        @foreach($moduleOptions as $option)
            <option value="{{ $option }}" @selected($module === $option)>{{ ucfirst($option) }}</option>
        @endforeach
    </select>
    <input class="input" type="text" name="q" value="{{ $keyword }}" placeholder="Cari nama/judul/module">
    <button class="btn-primary">Filter</button>
</form>

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th>Module</th><th>Nama/Judul</th><th>Model</th><th>Dihapus Pada</th><th>Dihapus Oleh</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ ucfirst($item->module) }}</td>
                    <td>{{ $item->payload['title'] ?? $item->payload['name'] ?? '-' }}</td>
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
                <tr><td colspan="6">Tidak ada data di recycle bin.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
