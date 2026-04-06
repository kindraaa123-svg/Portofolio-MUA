<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $module = $request->string('module')->toString();
        $action = $request->string('action')->toString();
        $user = $request->user();
        $isSuperadmin = (bool) ($user?->hasRole('superadmin'));

        $baseQuery = ActivityLog::query()->with('user.role');

        if (! $isSuperadmin) {
            $baseQuery->where(function ($query) {
                $query->whereDoesntHave('user.role', fn ($roleQuery) => $roleQuery->where('slug', 'superadmin'))
                    ->where(function ($levelQuery) {
                        $levelQuery->whereNull('user_level')
                            ->orWhere(function ($textQuery) {
                                $textQuery->whereRaw('LOWER(user_level) NOT LIKE ?', ['%superadmin%'])
                                    ->whereRaw('LOWER(user_level) NOT LIKE ?', ['%super admin%']);
                            });
                    });
            });
        }

        $logs = (clone $baseQuery)
            ->when($module !== '', fn ($query) => $query->where('module', $module))
            ->when($action !== '', fn ($query) => $query->where('action', 'like', "%{$action}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'modules' => (clone $baseQuery)->select('module')->distinct()->orderBy('module')->pluck('module'),
            'module' => $module,
            'action' => $action,
            'isSuperadmin' => $isSuperadmin,
        ]);
    }
}
