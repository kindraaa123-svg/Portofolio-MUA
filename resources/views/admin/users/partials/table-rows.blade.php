@forelse($users as $user)
<tr>
    <td>
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">
                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="truncate font-semibold text-slate-800">{{ $user->name }}</p>
                <p class="truncate text-xs text-slate-500">ID: {{ $user->id }}</p>
            </div>
        </div>
    </td>
    <td>
        <p class="text-slate-800">{{ $user->email }}</p>
        <p class="text-xs text-slate-500">{{ $user->phone ?: '-' }}</p>
    </td>
    <td>
        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
            {{ $user->role?->name ?? '-' }}
        </span>
    </td>
    <td>
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </td>
    <td>
        <div class="flex min-w-[220px] flex-col gap-2">
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
