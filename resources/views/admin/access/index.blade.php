@extends('layouts.admin')
@section('title', 'Hak Akses')
@section('content')
<section class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="relative bg-white p-6 md:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Access Control</p>
        <h1 class="mt-2 font-serif text-3xl leading-tight text-slate-900 md:text-4xl">Hak Akses</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600">Atur menu sidebar yang bisa diakses tiap level user. Perubahan akan langsung diterapkan setelah disimpan.</p>
    </div>
</section>

<form method="POST" action="{{ route('admin.access.update') }}" class="card-premium bg-white space-y-5">
    @csrf
    <div class="flex flex-wrap items-end justify-between gap-3 border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Matrix Level x Menu Sidebar</h2>
            <p class="mt-1 text-sm text-slate-600">Gunakan checkbox untuk mengaktifkan akses menu.</p>
        </div>
        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">{{ $roles->count() }} Level / {{ $permissions->count() }} Menu</span>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200">
        <table class="table-admin min-w-[1160px]">
            <thead>
            <tr>
                <th class="sticky left-0 z-10 bg-slate-100">Level</th>
                @foreach($permissions as $permission)
                    <th class="whitespace-nowrap text-center">{{ $menuLabels[$permission->slug] ?? $permission->slug }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($roles as $role)
                @php($activePermissionIds = $role->permissions->pluck('id')->all())
                <tr>
                    <td class="sticky left-0 z-[1] bg-white min-w-[220px]">
                        <div>
                            <strong class="text-slate-800">{{ $role->name }}</strong>
                            <p class="text-xs text-slate-500">{{ $role->slug }}</p>
                            <div class="mt-2 flex gap-2">
                                <button type="button" class="btn-secondary text-[11px] px-2 py-1 js-check-all-role" data-role-id="{{ $role->id }}">Pilih Semua</button>
                                <button type="button" class="btn-secondary text-[11px] px-2 py-1 js-clear-all-role" data-role-id="{{ $role->id }}">Kosongkan</button>
                            </div>
                        </div>
                    </td>
                    @foreach($permissions as $permission)
                        <td class="text-center">
                            <input
                                type="checkbox"
                                class="h-4 w-4 accent-slate-900 js-role-permission"
                                data-role-id="{{ $role->id }}"
                                name="matrix[{{ $role->id }}][{{ $permission->id }}]"
                                value="1"
                                @checked(in_array($permission->id, $activePermissionIds))
                            >
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <button class="btn-primary" type="submit">Simpan Hak Akses Menu</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkAllButtons = document.querySelectorAll('.js-check-all-role');
    const clearAllButtons = document.querySelectorAll('.js-clear-all-role');

    const setRolePermissions = (roleId, checked) => {
        const checkboxes = document.querySelectorAll(`.js-role-permission[data-role-id="${roleId}"]`);
        checkboxes.forEach((checkbox) => {
            checkbox.checked = checked;
        });
    };

    checkAllButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setRolePermissions(button.dataset.roleId, true);
        });
    });

    clearAllButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setRolePermissions(button.dataset.roleId, false);
        });
    });
});
</script>
@endsection
