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

        $logs = ActivityLog::with('user')
            ->when($module !== '', fn ($query) => $query->where('module', $module))
            ->when($action !== '', fn ($query) => $query->where('action', 'like', "%{$action}%"))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'modules' => ActivityLog::select('module')->distinct()->orderBy('module')->pluck('module'),
            'module' => $module,
            'action' => $action,
        ]);
    }
}
