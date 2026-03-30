@extends('layouts.admin')
@section('title', 'Recycle Bin')
@section('content')
<h1 class="text-2xl font-semibold mb-6">Recycle Bin</h1>
<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th>Module</th><th>Model</th><th>Deleted At</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->module }}</td>
                    <td>{{ class_basename($item->model_type) }} #{{ $item->model_id }}</td>
                    <td>{{ $item->deleted_at }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.recycle-bin.restore', $item) }}">@csrf<button class="btn-primary">Restore</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
