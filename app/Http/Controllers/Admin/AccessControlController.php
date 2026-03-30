<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccessControlController extends Controller
{
    protected array $sidebarPermissionOrder = [
        'dashboard.view',
        'portfolio.view',
        'service.view',
        'booking.view',
        'report.view',
        'backup.view',
        'recycle.view',
        'access.view',
        'user.view',
        'activity.view',
        'setting.view',
        'testimonial.view',
        'faq.view',
    ];

    public function index()
    {
        $permissionMap = Permission::whereIn('slug', $this->sidebarPermissionOrder)
            ->get()
            ->keyBy('slug');

        $permissions = collect($this->sidebarPermissionOrder)
            ->map(fn ($slug) => $permissionMap->get($slug))
            ->filter()
            ->values();

        return view('admin.access.index', [
            'roles' => Role::with('permissions')->orderBy('name')->get(),
            'permissions' => $permissions,
            'menuLabels' => [
                'dashboard.view' => 'Dashboard',
                'portfolio.view' => 'Portfolio',
                'service.view' => 'Pricelist',
                'booking.view' => 'Reservasi',
                'report.view' => 'Laporan',
                'backup.view' => 'Backup Database',
                'recycle.view' => 'Recycle Bin',
                'access.view' => 'Hak Akses',
                'user.view' => 'User Data',
                'activity.view' => 'Activity Log',
                'setting.view' => 'Setting Website',
                'testimonial.view' => 'Testimoni',
                'faq.view' => 'FAQ',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'matrix' => ['nullable', 'array'],
        ]);

        $roles = Role::with('permissions')->get();
        $matrix = $data['matrix'] ?? [];
        $sidebarPermissionIds = Permission::whereIn('slug', $this->sidebarPermissionOrder)->pluck('id')->all();

        foreach ($roles as $role) {
            $checkedMenuPermissionIds = array_map('intval', array_keys($matrix[$role->id] ?? []));
            $nonMenuPermissionIds = $role->permissions
                ->pluck('id')
                ->reject(fn ($id) => in_array($id, $sidebarPermissionIds, true))
                ->values()
                ->all();

            $syncedPermissionIds = array_values(array_unique([...$nonMenuPermissionIds, ...$checkedMenuPermissionIds]));
            $role->permissions()->sync($syncedPermissionIds);

            ActivityLogger::log('access', 'update-sidebar-permissions', $role, [
                'role' => $role->slug,
                'sidebar_permissions' => $checkedMenuPermissionIds,
            ]);
        }

        return back()->with('success', 'Hak akses menu sidebar berhasil diperbarui.');
    }
}
