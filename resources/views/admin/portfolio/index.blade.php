@extends('layouts.admin')
@section('title', 'Kelola Portfolio')
@section('content')
<div class="flex flex-wrap gap-3 justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold">Kelola Portfolio</h1>
    <div class="flex flex-wrap items-center gap-2">
        <a class="btn-secondary text-xs" href="{{ route('admin.portfolios.export-xlsx') }}">Export Excel (.xlsx)</a>
        <form method="POST" action="{{ route('admin.portfolios.import-xlsx') }}" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <input type="file" name="xlsx_file" accept=".xlsx" class="input text-xs" required>
            <button type="submit" class="btn-primary text-xs">Import Excel</button>
        </form>
        <a class="btn-primary" href="{{ route('admin.portfolios.create') }}">Tambah Portfolio</a>
    </div>
</div>

<form class="mb-4" method="GET">
    <input class="input" type="text" name="q" value="{{ $search }}" placeholder="Cari judul portfolio...">
</form>

<p class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
    Format import: <strong>title</strong> (wajib), lalu opsional: slug, category, summary, description, work_date (YYYY-MM-DD), client_name, is_published (1/0), cover_image_path.
</p>

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
