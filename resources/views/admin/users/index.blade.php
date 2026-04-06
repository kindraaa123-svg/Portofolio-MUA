@extends('layouts.admin')
@section('title', 'User Data')
@section('content')
<section class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="relative bg-white p-6 md:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">User Management</p>
        <h1 class="mt-2 font-serif text-3xl leading-tight text-slate-900 md:text-4xl">User Data</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600">Kelola akun admin dan user, import/export data, serta reset password dari satu halaman.</p>
    </div>
</section>

<div class="grid xl:grid-cols-3 gap-6 mb-8">
    <section class="card-premium bg-white xl:col-span-1 space-y-4">
        <div class="border-b border-slate-200 pb-3">
            <h2 class="text-lg font-semibold text-slate-900">Tambah User/Admin</h2>
            <p class="mt-1 text-sm text-slate-600">Buat akun baru dan tentukan level akses.</p>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
            @csrf
            <label class="field"><span>Nama</span><input name="name" required></label>
            <label class="field"><span>Email</span><input type="email" name="email" required></label>
            <label class="field"><span>Phone</span><input name="phone"></label>
            <label class="field"><span>Level</span>
                <select name="role_id" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </label>
            <p class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                Password user otomatis default: <strong>12345</strong>
            </p>
            <button class="btn-primary">Simpan User</button>
        </form>
    </section>

    <section class="card-premium bg-white xl:col-span-2 space-y-5">
        <div class="border-b border-slate-200 pb-4">
            <h2 class="text-lg font-semibold text-slate-900">Daftar User</h2>
            <p class="mt-1 text-sm text-slate-600">Cari, filter role, import/export data, reset password, dan hapus akun.</p>
        </div>

        <div class="grid gap-3 md:grid-cols-[auto_1fr]">
            <a href="{{ route('admin.users.export-xlsx') }}" class="btn-secondary text-xs justify-center md:justify-start">Export Excel (.xlsx)</a>
            <form method="POST" action="{{ route('admin.users.import-xlsx') }}" enctype="multipart/form-data" class="grid gap-2 sm:grid-cols-[1fr_auto]">
                @csrf
                <input type="file" name="xlsx_file" accept=".xlsx" class="input text-xs" required>
                <button type="submit" class="btn-primary text-xs">Import Excel</button>
            </form>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            <div class="field min-w-[220px] flex-1">
                <span>Cari User</span>
                <input id="user-search" type="text" placeholder="Cari nama, email, atau phone">
            </div>
            <div class="field min-w-[220px] flex-1">
                <span>Filter Role</span>
                <select id="role-filter">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table-admin min-w-[980px]">
                <thead><tr><th>User</th><th>Kontak</th><th>Level</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody id="users-table-body">@include('admin.users.partials.table-rows', ['users' => $users])</tbody>
            </table>
        </div>

        <div id="users-pagination" class="mt-2">{{ $users->links() }}</div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('user-search');
    const roleFilter = document.getElementById('role-filter');
    const tableBody = document.getElementById('users-table-body');
    const pagination = document.getElementById('users-pagination');
    const endpoint = "{{ route('admin.users.list') }}";
    let debounceTimer = null;

    if (!searchInput || !roleFilter || !tableBody || !pagination) {
        return;
    }

    const renderUsers = async (pageUrl = null) => {
        const url = new URL(pageUrl || endpoint, window.location.origin);
        if (!pageUrl) {
            const search = searchInput.value.trim();
            const roleId = roleFilter.value;
            if (search) {
                url.searchParams.set('search', search);
            }
            if (roleId) {
                url.searchParams.set('role_id', roleId);
            }
        }

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) return;
            const data = await response.json();
            tableBody.innerHTML = data.rows ?? '';
            pagination.innerHTML = data.pagination ?? '';
        } catch (e) {
            // noop
        }
    };

    const debouncedRender = () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => renderUsers(), 300);
    };

    searchInput.addEventListener('input', debouncedRender);
    roleFilter.addEventListener('change', () => renderUsers());

    pagination.addEventListener('click', (event) => {
        const link = event.target.closest('a');
        if (!link) return;
        event.preventDefault();
        renderUsers(link.href);
    });
});
</script>
@endsection
