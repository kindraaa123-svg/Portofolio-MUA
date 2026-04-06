@extends('layouts.admin')
@section('title', 'Kelola Portfolio')
@section('content')
<section class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="relative bg-white p-6 md:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Portfolio Manager</p>
        <h1 class="mt-2 font-serif text-3xl leading-tight text-slate-900 md:text-4xl">Kelola Portfolio</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600">Tambah, cari, import, dan atur status publish portfolio dalam satu dashboard.</p>
    </div>
</section>

<section class="card-premium bg-white mb-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form class="w-full md:max-w-md" method="GET">
            <label class="field">
                <span class="text-xs uppercase tracking-[0.12em] text-slate-500">Cari Portfolio</span>
                <input class="input w-full" type="text" name="q" value="{{ $search }}" placeholder="Cari judul portfolio...">
            </label>
        </form>
        <button type="button" class="btn-primary w-full justify-center sm:w-auto" id="open-create-portfolio-modal">Tambah Portfolio</button>
    </div>

    <div class="grid gap-3 lg:grid-cols-[auto_1fr]">
        <a class="btn-secondary text-xs justify-center sm:justify-start" href="{{ route('admin.portfolios.export-xlsx') }}">Export Excel (.xlsx)</a>
        <form method="POST" action="{{ route('admin.portfolios.import-xlsx') }}" enctype="multipart/form-data" class="grid gap-2 sm:grid-cols-[1fr_auto]">
            @csrf
            <input type="file" name="xlsx_file" accept=".xlsx" class="input text-xs" required>
            <button type="submit" class="btn-primary text-xs">Import Excel</button>
        </form>
    </div>

    <p class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
        Format import: <strong>title</strong> (wajib), lalu opsional: slug, category, summary, description, work_date (YYYY-MM-DD), client_name, is_published (1/0), cover_image_path.
    </p>
</section>

<div class="card-premium bg-white overflow-x-auto">
    <table class="table-admin min-w-[980px]">
        <thead>
            <tr>
                <th>Portfolio</th>
                <th>Kategori</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($portfolios as $item)
                <tr>
                    @php
                        $portfolioPayload = [
                            'id' => $item->id,
                            'title' => $item->title,
                            'portfolio_category_id' => $item->portfolio_category_id,
                            'summary' => $item->summary,
                            'description' => $item->description,
                            'client_name' => $item->client_name,
                            'work_date' => $item->work_date?->format('Y-m-d'),
                            'is_published' => (bool) $item->is_published,
                        ];
                    @endphp
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="h-14 w-14 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                                @if ($item->cover_image)
                                    <img src="{{ asset('storage/' . $item->cover_image) }}" alt="{{ $item->title }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-[10px] font-semibold uppercase tracking-[0.08em] text-slate-400">No Img</div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-800">{{ $item->title }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $item->client_name ?: '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td>{{ $item->category?->name ?? '-' }}</td>
                    <td>{{ $item->work_date?->format('d M Y') ?? '-' }}</td>
                    <td>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $item->is_published ? 'Publish' : 'Draft' }}
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn-secondary" onclick='openManagePortfolioModal(@json($portfolioPayload))'>Kelola</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-500">Belum ada data portfolio.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $portfolios->links() }}</div>

<div id="create-portfolio-modal" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="mx-auto mt-4 max-w-3xl rounded-2xl bg-white p-6 md:mt-8">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Tambah Portfolio</h3>
            <button type="button" class="btn-secondary text-xs" id="close-create-portfolio-modal">Tutup</button>
        </div>

        <form method="POST" action="{{ route('admin.portfolios.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <label class="field"><span>Judul</span><input type="text" name="title" value="{{ old('title') }}" required></label>
                <label class="field"><span>Kategori</span>
                    <select name="portfolio_category_id">
                        <option value="">- Pilih Kategori -</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('portfolio_category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label class="field"><span>Ringkasan</span><textarea name="summary" rows="2">{{ old('summary') }}</textarea></label>
            <label class="field"><span>Deskripsi</span><textarea name="description" rows="4">{{ old('description') }}</textarea></label>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="field"><span>Nama Klien</span><input type="text" name="client_name" value="{{ old('client_name') }}"></label>
                <label class="field"><span>Tanggal Kerja</span><input type="date" name="work_date" value="{{ old('work_date') }}"></label>
            </div>
            <label class="field"><span>Cover Image</span><input type="file" name="cover_image" accept="image/*"></label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', true))> Publish</label>
            <div class="flex items-center gap-2 pt-2">
                <button class="btn-primary" type="submit">Simpan Portfolio</button>
            </div>
        </form>
    </div>
</div>

<div id="manage-portfolio-modal" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="mx-auto mt-4 max-w-3xl rounded-2xl bg-white p-6 md:mt-8">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Kelola Portfolio</h3>
            <button type="button" class="btn-secondary text-xs" id="close-manage-portfolio-modal">Tutup</button>
        </div>

        <form id="form-edit-portfolio" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid gap-4 md:grid-cols-2">
                <label class="field"><span>Judul</span><input type="text" name="title" id="manage-title" required></label>
                <label class="field"><span>Kategori</span>
                    <select name="portfolio_category_id" id="manage-category">
                        <option value="">- Pilih Kategori -</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label class="field"><span>Ringkasan</span><textarea name="summary" id="manage-summary" rows="2"></textarea></label>
            <label class="field"><span>Deskripsi</span><textarea name="description" id="manage-description" rows="4"></textarea></label>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="field"><span>Nama Klien</span><input type="text" name="client_name" id="manage-client-name"></label>
                <label class="field"><span>Tanggal Kerja</span><input type="date" name="work_date" id="manage-work-date"></label>
            </div>
            <label class="field"><span>Cover Image (opsional)</span><input type="file" name="cover_image" accept="image/*"></label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_published" id="manage-published" value="1"> Publish</label>
            <div class="flex items-center gap-2 pt-2">
                <button class="btn-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>

        <form id="form-delete-portfolio" method="POST" class="mt-3" onsubmit="return confirm('Hapus portfolio ini? Data akan masuk recycle bin.');">
            @csrf
            @method('DELETE')
            <button class="btn-danger" type="submit">Hapus</button>
        </form>
    </div>
</div>

<script>
function openManagePortfolioModal(data) {
    const modal = document.getElementById('manage-portfolio-modal');
    const editForm = document.getElementById('form-edit-portfolio');
    const deleteForm = document.getElementById('form-delete-portfolio');

    if (!modal || !editForm || !deleteForm) {
        return;
    }

    editForm.action = `/admin/portfolio/${data.id}`;
    deleteForm.action = `/admin/portfolio/${data.id}`;

    document.getElementById('manage-title').value = data.title ?? '';
    document.getElementById('manage-category').value = data.portfolio_category_id ?? '';
    document.getElementById('manage-summary').value = data.summary ?? '';
    document.getElementById('manage-description').value = data.description ?? '';
    document.getElementById('manage-client-name').value = data.client_name ?? '';
    document.getElementById('manage-work-date').value = data.work_date ?? '';
    document.getElementById('manage-published').checked = !!data.is_published;

    modal.classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const createModal = document.getElementById('create-portfolio-modal');
    const manageModal = document.getElementById('manage-portfolio-modal');
    const openCreateBtn = document.getElementById('open-create-portfolio-modal');
    const closeCreateBtn = document.getElementById('close-create-portfolio-modal');
    const closeManageBtn = document.getElementById('close-manage-portfolio-modal');

    if (!createModal || !manageModal || !openCreateBtn || !closeCreateBtn || !closeManageBtn) {
        return;
    }

    const openCreateModal = () => createModal.classList.remove('hidden');
    const closeCreateModal = () => createModal.classList.add('hidden');
    const closeManageModal = () => manageModal.classList.add('hidden');

    openCreateBtn.addEventListener('click', openCreateModal);
    closeCreateBtn.addEventListener('click', closeCreateModal);
    closeManageBtn.addEventListener('click', closeManageModal);

    createModal.addEventListener('click', (event) => {
        if (event.target === createModal) {
            closeCreateModal();
        }
    });

    manageModal.addEventListener('click', (event) => {
        if (event.target === manageModal) {
            closeManageModal();
        }
    });

    @if(old('title'))
    openCreateModal();
    @endif
});
</script>
@endsection
