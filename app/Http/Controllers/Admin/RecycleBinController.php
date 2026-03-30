<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecycleBin;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;

class RecycleBinController extends Controller
{
    public function index()
    {
        return view('admin.recycle-bin.index', [
            'items' => RecycleBin::latest('deleted_at')->paginate(15),
        ]);
    }

    public function restore(RecycleBin $item): RedirectResponse
    {
        $modelClass = $item->model_type;
        $model = $modelClass::withTrashed()->find($item->model_id);

        if ($model) {
            $model->restore();
        }

        $item->delete();

        ActivityLogger::log('recycle', 'restore', null, [
            'module' => $item->module,
            'model_type' => $item->model_type,
            'model_id' => $item->model_id,
        ]);

        return back()->with('success', 'Data berhasil direstore dari recycle bin.');
    }
}
