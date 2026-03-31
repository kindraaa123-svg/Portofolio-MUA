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
@endsection
