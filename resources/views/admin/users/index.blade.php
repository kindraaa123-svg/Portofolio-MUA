@extends('layouts.admin')
@section('title', 'User Data')
@section('content')
<h1 class="text-2xl font-semibold mb-6">User Data</h1>

<div class="grid xl:grid-cols-3 gap-6 mb-8">
    <section class="card-premium bg-white xl:col-span-1">
        <h2 class="font-semibold mb-3">Tambah User/Admin</h2>
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

    <section class="card-premium bg-white xl:col-span-2 overflow-x-auto">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold">Import / Export User (.xlsx)</h2>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.users.export-xlsx') }}" class="btn-secondary text-xs">Export Excel (.xlsx)</a>
                <form method="POST" action="{{ route('admin.users.import-xlsx') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="file" name="xlsx_file" accept=".xlsx" class="input text-xs" required>
                    <button type="submit" class="btn-primary text-xs">Import Excel</button>
                </form>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div class="field min-w-[220px] flex-1">
                <span>Cari User</span>
                <input id="user-search" type="text" placeholder="Cari nama, email, atau phone">
            </div>
            <div class="field min-w-[220px]">
                <span>Filter Role</span>
                <select id="role-filter">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <h2 class="font-semibold mb-3">Daftar User</h2>
        <table class="table-admin">
            <thead><tr><th>Nama</th><th>Email</th><th>Level</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody id="users-table-body">@include('admin.users.partials.table-rows', ['users' => $users])</tbody>
        </table>
        <div id="users-pagination" class="mt-4">{{ $users->links() }}</div>
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
