@extends('layouts.admin')
@section('title', 'Testimoni')
@section('content')
<h1 class="text-2xl font-semibold mb-6">Testimoni</h1>
<form method="POST" action="{{ route('admin.testimonials.store') }}" class="card-premium bg-white space-y-3 mb-8">
    @csrf
    <div class="grid md:grid-cols-3 gap-3">
        <label class="field"><span>Nama</span><input name="name" required></label>
        <label class="field"><span>Title</span><input name="title"></label>
        <label class="field"><span>Rating</span><input type="number" name="rating" min="1" max="5" value="5" required></label>
    </div>
    <label class="field"><span>Pesan</span><textarea name="message" required></textarea></label>
    <button class="btn-primary">Tambah Testimoni</button>
</form>

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th>Nama</th><th>Rating</th><th>Pesan</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach($testimonials as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->rating }}</td>
                    <td>{{ $item->message }}</td>
                    <td><form method="POST" action="{{ route('admin.testimonials.destroy', $item) }}">@csrf @method('DELETE')<button class="btn-danger">Hapus</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $testimonials->links() }}</div>
</div>
@endsection
