@extends('layouts.admin')
@section('title', 'Backup Database')
@section('content')
<h1 class="text-2xl font-semibold mb-6">Backup Database</h1>

<div class="grid md:grid-cols-2 gap-6 mb-6">
    <section class="card-premium bg-white">
        <h2 class="font-semibold mb-3">Export Database (.sql)</h2>
        <form method="POST" action="{{ route('admin.backup.export') }}">
            @csrf
            <button class="btn-primary">Export SQL</button>
        </form>
    </section>

    <section class="card-premium bg-white">
        <h2 class="font-semibold mb-3">Import Database (.sql)</h2>
        <form method="POST" action="{{ route('admin.backup.import') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="sql_file" required accept=".sql,text/plain" class="input w-full">
            <button class="btn-primary">Import SQL</button>
        </form>
    </section>
</div>

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th>Nama File</th><th>Tipe</th><th>Size</th><th>Status</th><th>Waktu</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach($logs as $item)
                <tr>
                    <td>{{ $item->file_name }}</td>
                    <td>{{ $item->type }}</td>
                    <td>{{ number_format($item->file_size / 1024, 2) }} KB</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->created_at?->format('d M Y H:i') }}</td>
                    <td>
                        @if($item->type === 'database-export' && $item->status === 'completed')
                            <a class="btn-secondary text-xs" href="{{ route('admin.backup.download', $item) }}">Download</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
