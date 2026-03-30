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
            <label class="field"><span>Password</span><input type="password" name="password" required></label>
            <button class="btn-primary">Simpan User</button>
        </form>
    </section>

    <section class="card-premium bg-white xl:col-span-2 overflow-x-auto">
        <h2 class="font-semibold mb-3">Daftar User</h2>
        <table class="table-admin">
            <thead><tr><th>Nama</th><th>Email</th><th>Level</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}<br><small>{{ $user->phone }}</small></td>
                    <td>{{ $user->role?->name ?? '-' }}</td>
                    <td>
                        <span class="rounded-full px-2 py-1 text-xs {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <div class="space-y-2 min-w-[220px]">
                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                                @csrf
                                <button class="btn-secondary text-xs w-full">Reset Password (12345)</button>
                            </form>

                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus akun ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn-danger w-full">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
</div>

<div>{{ $users->links() }}</div>
@endsection
