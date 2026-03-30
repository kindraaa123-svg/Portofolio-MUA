<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        $permissionMap = [
            'booking.verify-payment' => 'booking',
            'backup.import' => 'backup',
            'access.view' => 'access',
            'access.update' => 'access',
            'activity.view' => 'activity',
            'user.update' => 'user',
            'user.delete' => 'user',
            'user.reset-password' => 'user',
        ];

        foreach ($permissionMap as $slug => $module) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => Str::headline($slug),
                    'module' => $module,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $superadminRole = DB::table('roles')->where('slug', 'superadmin')->first();
        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        $staffRole = DB::table('roles')->where('slug', 'staff')->first();

        $allPermissionIds = DB::table('permissions')->pluck('id');

        if ($superadminRole) {
            foreach ($allPermissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $superadminRole->id, 'permission_id' => $permissionId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        if ($adminRole) {
            $adminSlugs = DB::table('permissions')->whereNotIn('slug', ['access.update', 'user.delete'])->pluck('id');
            foreach ($adminSlugs as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $adminRole->id, 'permission_id' => $permissionId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        if ($staffRole) {
            $staffSlugs = DB::table('permissions')->whereIn('slug', [
                'dashboard.view',
                'booking.view',
                'booking.update',
                'booking.verify-payment',
                'activity.view',
            ])->pluck('id');

            foreach ($staffSlugs as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $staffRole->id, 'permission_id' => $permissionId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('slug', [
            'booking.verify-payment',
            'backup.import',
            'access.view',
            'access.update',
            'activity.view',
            'user.update',
            'user.delete',
            'user.reset-password',
        ])->delete();
    }
};
