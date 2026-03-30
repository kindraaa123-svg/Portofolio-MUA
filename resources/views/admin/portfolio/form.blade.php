@extends('layouts.admin')
@section('title', $portfolio->exists ? 'Edit Portfolio' : 'Tambah Portfolio')
@section('content')
<h1 class="text-2xl font-semibold mb-6">{{ $portfolio->exists ? 'Edit Portfolio' : 'Tambah Portfolio' }}</h1>
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="card-premium bg-white space-y-4">
    @csrf
    @if($portfolio->exists) @method('PUT') @endif
    <div class="grid md:grid-cols-2 gap-4">
        <label class="field"><span>Judul</span><input type="text" name="title" value="{{ old('title', $portfolio->title) }}" required></label>
        <label class="field"><span>Kategori</span>
            <select name="portfolio_category_id">
                <option value="">- Pilih Kategori -</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('portfolio_category_id', $portfolio->portfolio_category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <label class="field"><span>Ringkasan</span><textarea name="summary" rows="2">{{ old('summary', $portfolio->summary) }}</textarea></label>
    <label class="field"><span>Deskripsi</span><textarea name="description" rows="4">{{ old('description', $portfolio->description) }}</textarea></label>
    <div class="grid md:grid-cols-2 gap-4">
        <label class="field"><span>Nama Klien</span><input type="text" name="client_name" value="{{ old('client_name', $portfolio->client_name) }}"></label>
        <label class="field"><span>Tanggal Kerja</span><input type="date" name="work_date" value="{{ old('work_date', optional($portfolio->work_date)->format('Y-m-d')) }}"></label>
    </div>
    <label class="field"><span>Cover Image</span><input type="file" name="cover_image" accept="image/*"></label>
    <label class="field"><span>Galeri (multi image)</span><input type="file" name="images[]" accept="image/*" multiple></label>
    <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $portfolio->is_published ?? true))> Publish</label>
    <button class="btn-primary">Simpan</button>
</form>
@endsection
