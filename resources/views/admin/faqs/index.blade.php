@extends('layouts.admin')
@section('title', 'FAQ')
@section('content')
<h1 class="text-2xl font-semibold mb-6">FAQ</h1>
<form method="POST" action="{{ route('admin.faqs.store') }}" class="card-premium bg-white space-y-3 mb-8">
    @csrf
    <label class="field"><span>Pertanyaan</span><input name="question" required></label>
    <label class="field"><span>Jawaban</span><textarea name="answer" required></textarea></label>
    <label class="field"><span>Urutan</span><input type="number" name="sort_order" min="0" value="0"></label>
    <button class="btn-primary">Tambah FAQ</button>
</form>

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th>Pertanyaan</th><th>Urutan</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach($faqs as $item)
                <tr>
                    <td>{{ $item->question }}</td>
                    <td>{{ $item->sort_order }}</td>
                    <td><form method="POST" action="{{ route('admin.faqs.destroy', $item) }}">@csrf @method('DELETE')<button class="btn-danger">Hapus</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $faqs->links() }}</div>
</div>
@endsection
