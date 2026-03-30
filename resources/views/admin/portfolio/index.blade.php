@extends('layouts.admin')
@section('title', 'Kelola Portfolio')
@section('content')
<div class="flex flex-wrap gap-3 justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold">Kelola Portfolio</h1>
    <a class="btn-primary" href="{{ route('admin.portfolios.create') }}">Tambah Portfolio</a>
</div>

<form class="mb-4" method="GET">
    <input class="input" type="text" name="q" value="{{ $search }}" placeholder="Cari judul portfolio...">
</form>

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th>Judul</th><th>Kategori</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach ($portfolios as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->category?->name }}</td>
                    <td>{{ $item->work_date?->format('d M Y') }}</td>
                    <td>{{ $item->is_published ? 'Publish' : 'Draft' }}</td>
                    <td class="flex gap-2">
                        <a class="btn-secondary" href="{{ route('admin.portfolios.edit', $item) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.portfolios.destroy', $item) }}">@csrf @method('DELETE')<button class="btn-danger">Hapus</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $portfolios->links() }}</div>
@endsection
