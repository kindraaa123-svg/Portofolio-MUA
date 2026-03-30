<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['slug' => 'report.view'],
            [
                'name' => 'Report View',
                'module' => 'report',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $permissionId = DB::table('permissions')->where('slug', 'report.view')->value('id');

        if (! $permissionId) {
            return;
        }

        $roleIds = DB::table('roles')->whereIn('slug', ['superadmin', 'admin'])->pluck('id');
        foreach ($roleIds as $roleId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $roleId, 'permission_id' => $permissionId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('slug', 'report.view')->value('id');
        if ($permissionId) {
            DB::table('role_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
