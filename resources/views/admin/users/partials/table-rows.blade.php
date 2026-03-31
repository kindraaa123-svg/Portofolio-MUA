@forelse($users as $user)
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
@empty
<tr>
    <td colspan="5" class="text-center text-slate-500 py-6">Data user tidak ditemukan.</td>
</tr>
@endforelse
