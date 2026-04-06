@extends('layouts.admin')
@section('title', 'Kelola Daftar Harga')
@section('content')
<section class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="relative bg-white p-6 md:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Pricing Manager</p>
        <h1 class="mt-2 font-serif text-3xl leading-tight text-slate-900 md:text-4xl">Kelola Daftar Harga</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600">Atur layanan utama dan add-on, termasuk import/export data pricelist dari file Excel.</p>
    </div>
</section>

<section class="card-premium bg-white mb-6">
    <form method="GET" class="grid gap-3 md:grid-cols-[1fr_auto]">
        <label class="field">
            <span>Cari Layanan</span>
            <input class="input w-full" type="text" name="q" value="{{ $search }}" placeholder="Cari nama layanan...">
        </label>
        <button class="btn-secondary self-end" type="submit">Cari</button>
    </form>
</section>

<div class="grid gap-6 lg:grid-cols-2">
    <section class="card-premium bg-white space-y-4">
        <div class="border-b border-slate-200 pb-3">
            <h2 class="text-lg font-semibold text-slate-900">Tambah Layanan</h2>
            <p class="mt-1 text-sm text-slate-600">Tambahkan paket layanan baru ke pricelist.</p>
        </div>
        <form method="POST" action="{{ route('admin.services.store') }}" class="space-y-3">
            @csrf
            <label class="field"><span>Nama Layanan</span><input name="name" required></label>
            <label class="field"><span>Kategori Layanan</span><select name="service_category_id"><option value="">Pilih kategori</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="field"><span>Harga</span><input type="number" name="price" min="0" required></label>
                <label class="field"><span>Durasi (menit)</span><input type="number" name="duration_minutes" min="30" value="90" required></label>
            </div>
            <label class="field"><span>Biaya Layanan ke Rumah</span><input type="number" name="home_service_fee" min="0" value="0"></label>
            <label class="field"><span>Deskripsi</span><textarea name="description"></textarea></label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_home_service_available" value="1"> Layanan ke rumah tersedia</label>
            <button class="btn-primary w-full sm:w-auto">Tambah Layanan</button>
        </form>
    </section>

    <section class="card-premium bg-white space-y-4">
        <div class="border-b border-slate-200 pb-3">
            <h2 class="text-lg font-semibold text-slate-900">Tambah Add-on</h2>
            <p class="mt-1 text-sm text-slate-600">Tambahkan item add-on untuk melengkapi layanan utama.</p>
        </div>
        <form method="POST" action="{{ route('admin.addons.store') }}" class="space-y-3">
            @csrf
            <label class="field"><span>Nama Add-on</span><input name="name" required></label>
            <label class="field"><span>Harga</span><input type="number" name="price" min="0" required></label>
            <label class="field"><span>Deskripsi</span><textarea name="description"></textarea></label>
            <button class="btn-primary w-full sm:w-auto">Tambah Add-on</button>
        </form>
    </section>
</div>

<section class="mt-8 card-premium bg-white space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Daftar Layanan</h2>
            <p class="mt-1 text-sm text-slate-600">Klik `Kelola` untuk edit atau hapus layanan.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a class="btn-secondary text-xs" href="{{ route('admin.services.export-services-xlsx') }}">Export Layanan (.xlsx)</a>
            <form method="POST" action="{{ route('admin.services.import-services-xlsx') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="xlsx_file" accept=".xlsx" required class="input text-xs max-w-[220px]">
                <button type="submit" class="btn-secondary text-xs">Import Layanan (.xlsx)</button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table-admin min-w-[980px]">
            <thead><tr><th>Layanan</th><th>Kategori</th><th>Harga</th><th>Durasi</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse ($services as $item)
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
                        <td>
                            <p class="font-semibold text-slate-800">{{ $item->name }}</p>
                            <p class="text-xs text-slate-500">{{ Str::limit($item->description ?: '-', 70) }}</p>
                        </td>
                        <td>{{ $item->category?->name ?? '-' }}</td>
                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>{{ $item->duration_minutes }} menit</td>
                        <td>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn-secondary text-xs" onclick='bukaModalLayanan(@json($servicePayload))'>Kelola</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-500 py-6">Belum ada layanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2">{{ $services->links() }}</div>
</section>

<section class="mt-8 card-premium bg-white space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Daftar Add-on</h2>
            <p class="mt-1 text-sm text-slate-600">Klik `Kelola` untuk edit atau hapus add-on.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a class="btn-secondary text-xs" href="{{ route('admin.services.export-addons-xlsx') }}">Export Add-on (.xlsx)</a>
            <form method="POST" action="{{ route('admin.services.import-addons-xlsx') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="xlsx_file" accept=".xlsx" required class="input text-xs max-w-[220px]">
                <button type="submit" class="btn-secondary text-xs">Import Add-on (.xlsx)</button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table-admin min-w-[900px]">
            <thead><tr><th>Nama Add-on</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($addons as $item)
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
                        <td>
                            <p class="font-semibold text-slate-800">{{ $item->name }}</p>
                            <p class="text-xs text-slate-500">{{ Str::limit($item->description ?: '-', 70) }}</p>
                        </td>
                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn-secondary text-xs" onclick='bukaModalAddon(@json($addonPayload))'>Kelola</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-slate-500 py-6">Belum ada add-on.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2">{{ $addons->links() }}</div>
</section>

<div id="modal-layanan" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="mx-auto mt-6 max-w-2xl rounded-2xl bg-white p-6">
        <div class="mb-4 flex items-center justify-between">
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
        <div class="mb-4 flex items-center justify-between">
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
