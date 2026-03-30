@extends('layouts.admin')
@section('title', 'Hak Akses')
@section('content')
<h1 class="text-2xl font-semibold mb-6">Hak Akses (Matrix Level x Menu Sidebar)</h1>

<form method="POST" action="{{ route('admin.access.update') }}" class="card-premium bg-white overflow-x-auto">
    @csrf
    <table class="table-admin min-w-[1000px]">
        <thead>
        <tr>
            <th>Level</th>
            @foreach($permissions as $permission)
                <th class="whitespace-nowrap">{{ $menuLabels[$permission->slug] ?? $permission->slug }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($roles as $role)
            @php($activePermissionIds = $role->permissions->pluck('id')->all())
            <tr>
                <td>
                    <strong>{{ $role->name }}</strong><br>
                    <small>{{ $role->slug }}</small>
                </td>
                @foreach($permissions as $permission)
                    <td>
                        <input
                            type="checkbox"
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

    <button class="btn-primary mt-5" type="submit">Simpan Hak Akses Menu</button>
</form>
@endsection
