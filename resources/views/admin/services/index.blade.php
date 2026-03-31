@extends('layouts.admin')
@section('title', 'Kelola Daftar Harga')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="text-2xl font-semibold">Kelola Daftar Harga Layanan dan Add-on</h1>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <section class="card-premium bg-white">
        <h2 class="font-semibold text-lg mb-3">Tambah Layanan</h2>
        <form method="POST" action="{{ route('admin.services.store') }}" class="space-y-3">
            @csrf
            <label class="field"><span>Nama Layanan</span><input name="name" required></label>
            <label class="field"><span>Kategori Layanan</span><select name="service_category_id"><option value="">Pilih kategori</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></label>
            <label class="field"><span>Harga</span><input type="number" name="price" min="0" required></label>
            <label class="field"><span>Durasi (menit)</span><input type="number" name="duration_minutes" min="30" value="90" required></label>
            <label class="field"><span>Biaya Layanan ke Rumah</span><input type="number" name="home_service_fee" min="0" value="0"></label>
            <label class="field"><span>Deskripsi</span><textarea name="description"></textarea></label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_home_service_available" value="1"> Layanan ke rumah tersedia</label>
            <button class="btn-primary">Tambah Layanan</button>
        </form>
    </section>

    <section class="card-premium bg-white">
        <h2 class="font-semibold text-lg mb-3">Tambah Add-on</h2>
        <form method="POST" action="{{ route('admin.addons.store') }}" class="space-y-3">
            @csrf
            <label class="field"><span>Nama Add-on</span><input name="name" required></label>
            <label class="field"><span>Harga</span><input type="number" name="price" min="0" required></label>
            <label class="field"><span>Deskripsi</span><textarea name="description"></textarea></label>
            <button class="btn-primary">Tambah Add-on</button>
        </form>
    </section>
</div>

<div class="mt-8 card-premium bg-white overflow-x-auto">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <h2 class="font-semibold text-lg">Daftar Layanan</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a class="btn-secondary text-xs" href="{{ route('admin.services.export-services-xlsx') }}">Export Layanan (.xlsx)</a>
            <form method="POST" action="{{ route('admin.services.import-services-xlsx') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="xlsx_file" accept=".xlsx" required class="input text-xs max-w-[220px]">
                <button type="submit" class="btn-secondary text-xs">Import Layanan (.xlsx)</button>
            </form>
        </div>
    </div>
    <table class="table-admin">
        <thead><tr><th>Nama</th><th>Kategori</th><th>Harga</th><th>Durasi</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach ($services as $item)
                @php
                    $servicePayload = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'service_category_id' => $item->service_category_id,
                        'description' => $item->description,
                        'duration_minutes' => $item->duration_minutes,
                        'price' => (float) $item->price,
                        'home_service_fee' => (float) $item->home_service_fee,
                        'is_home_service_available' => (bool) $item->is_home_service_available,
                        'is_active' => (bool) $item->is_active,
                    ];
                @endphp
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->category?->name ?? '-' }}</td>
                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>{{ $item->duration_minutes }} menit</td>
                    <td>
                        <button
                            type="button"
                            class="btn-secondary text-xs"
                            onclick='bukaModalLayanan(@json($servicePayload))'
                        >Kelola</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $services->links() }}</div>
</div>

<div class="mt-8 card-premium bg-white overflow-x-auto">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <h2 class="font-semibold text-lg">Daftar Add-on</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a class="btn-secondary text-xs" href="{{ route('admin.services.export-addons-xlsx') }}">Export Add-on (.xlsx)</a>
            <form method="POST" action="{{ route('admin.services.import-addons-xlsx') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="xlsx_file" accept=".xlsx" required class="input text-xs max-w-[220px]">
                <button type="submit" class="btn-secondary text-xs">Import Add-on (.xlsx)</button>
            </form>
        </div>
    </div>
    <table class="table-admin">
        <thead><tr><th>Nama</th><th>Harga</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach($addons as $item)
                @php
                    $addonPayload = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => (float) $item->price,
                        'is_active' => (bool) $item->is_active,
                    ];
                @endphp
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>
                        <button
                            type="button"
                            class="btn-secondary text-xs"
                            onclick='bukaModalAddon(@json($addonPayload))'
                        >Kelola</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $addons->links() }}</div>
</div>

<div id="modal-layanan" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="mx-auto mt-6 max-w-2xl rounded-2xl bg-white p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Edit Layanan</h3>
            <button type="button" class="btn-secondary text-xs" onclick="tutupModal('modal-layanan')">Tutup</button>
        </div>

        <form id="form-edit-layanan" method="POST" class="space-y-3">
            @csrf
            @method('PUT')
            <label class="field"><span>Nama Layanan</span><input name="name" id="layanan-name" required></label>
            <label class="field"><span>Kategori Layanan</span>
                <select name="service_category_id" id="layanan-category">
                    <option value="">Pilih kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field"><span>Harga</span><input type="number" name="price" id="layanan-price" min="0" required></label>
            <label class="field"><span>Durasi (menit)</span><input type="number" name="duration_minutes" id="layanan-duration" min="30" required></label>
            <label class="field"><span>Biaya Layanan ke Rumah</span><input type="number" name="home_service_fee" id="layanan-home-fee" min="0"></label>
            <label class="field"><span>Deskripsi</span><textarea name="description" id="layanan-description"></textarea></label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_home_service_available" id="layanan-home" value="1"> Layanan ke rumah tersedia</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" id="layanan-active" value="1"> Layanan aktif</label>

            <div class="flex items-center gap-2 pt-2">
                <button class="btn-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>

        <form id="form-hapus-layanan" method="POST" class="mt-3" onsubmit="return confirm('Hapus layanan ini?')">
            @csrf
            @method('DELETE')
            <button class="btn-danger" type="submit">Hapus</button>
        </form>
    </div>
</div>

<div id="modal-addon" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="mx-auto mt-6 max-w-xl rounded-2xl bg-white p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Edit Add-on</h3>
            <button type="button" class="btn-secondary text-xs" onclick="tutupModal('modal-addon')">Tutup</button>
        </div>

        <form id="form-edit-addon" method="POST" class="space-y-3">
            @csrf
            @method('PUT')
            <label class="field"><span>Nama Add-on</span><input name="name" id="addon-name" required></label>
            <label class="field"><span>Harga</span><input type="number" name="price" id="addon-price" min="0" required></label>
            <label class="field"><span>Deskripsi</span><textarea name="description" id="addon-description"></textarea></label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" id="addon-active" value="1"> Add-on aktif</label>

            <div class="flex items-center gap-2 pt-2">
                <button class="btn-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>

        <form id="form-hapus-addon" method="POST" class="mt-3" onsubmit="return confirm('Hapus add-on ini?')">
            @csrf
            @method('DELETE')
            <button class="btn-danger" type="submit">Hapus</button>
        </form>
    </div>
</div>

<script>
function tutupModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function bukaModalLayanan(data) {
    document.getElementById('form-edit-layanan').action = `/admin/pricelist/service/${data.id}`;
    document.getElementById('form-hapus-layanan').action = `/admin/pricelist/service/${data.id}`;

    document.getElementById('layanan-name').value = data.name ?? '';
    document.getElementById('layanan-category').value = data.service_category_id ?? '';
    document.getElementById('layanan-price').value = data.price ?? 0;
    document.getElementById('layanan-duration').value = data.duration_minutes ?? 90;
    document.getElementById('layanan-home-fee').value = data.home_service_fee ?? 0;
    document.getElementById('layanan-description').value = data.description ?? '';
    document.getElementById('layanan-home').checked = !!data.is_home_service_available;
    document.getElementById('layanan-active').checked = !!data.is_active;

    document.getElementById('modal-layanan').classList.remove('hidden');
}

function bukaModalAddon(data) {
    document.getElementById('form-edit-addon').action = `/admin/pricelist/addon/${data.id}`;
    document.getElementById('form-hapus-addon').action = `/admin/pricelist/addon/${data.id}`;

    document.getElementById('addon-name').value = data.name ?? '';
    document.getElementById('addon-price').value = data.price ?? 0;
    document.getElementById('addon-description').value = data.description ?? '';
    document.getElementById('addon-active').checked = !!data.is_active;

    document.getElementById('modal-addon').classList.remove('hidden');
}
</script>
@endsection
