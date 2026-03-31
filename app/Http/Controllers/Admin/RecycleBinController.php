<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\RecycleBin;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RecycleBinController extends Controller
{
    public function index(Request $request)
    {
        $module = $request->string('module')->toString();
        $keyword = $request->string('q')->toString();
        $availableModules = ['service', 'addon', 'portfolio'];

        return view('admin.recycle-bin.index', [
            'items' => RecycleBin::query()
                ->with('deletedBy')
                ->whereIn('module', $availableModules)
                ->when($module !== '', fn ($query) => $query->where('module', $module))
                ->when($keyword !== '', fn ($query) => $query->where(function ($inner) use ($keyword) {
                    $inner->where('module', 'like', "%{$keyword}%")
                        ->orWhere('model_type', 'like', "%{$keyword}%")
                        ->orWhere('payload', 'like', "%{$keyword}%");
                }))
                ->latest('deleted_at')
                ->paginate(20)
                ->withQueryString(),
            'module' => $module,
            'keyword' => $keyword,
            'moduleOptions' => $availableModules,
        ]);
    }

    public function restore(RecycleBin $item): RedirectResponse
    {
        $modelClass = $item->model_type;
        $model = class_exists($modelClass) && method_exists($modelClass, 'withTrashed')
            ? $modelClass::withTrashed()->find($item->model_id)
            : null;

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

    public function destroy(RecycleBin $item): RedirectResponse
    {
        $modelClass = $item->model_type;

        try {
            $model = class_exists($modelClass) && method_exists($modelClass, 'withTrashed')
                ? $modelClass::withTrashed()->find($item->model_id)
                : null;

            if ($model instanceof Portfolio) {
                $images = $model->images()->withTrashed()->get();
                $paths = collect([$model->cover_image])
                    ->merge($images->pluck('image_path'))
                    ->filter()
                    ->unique()
                    ->values();

                if ($paths->isNotEmpty()) {
                    Storage::disk('public')->delete($paths->all());
                }

                foreach ($images as $image) {
                    $image->forceDelete();
                }

                $model->forceDelete();
            } elseif ($model && method_exists($model, 'forceDelete')) {
                $model->forceDelete();
            } elseif ($model) {
                $model->delete();
            } else {
                $payload = (array) ($item->payload ?? []);
                $paths = collect([$payload['cover_image'] ?? null])
                    ->merge((array) ($payload['image_paths'] ?? []))
                    ->filter()
                    ->unique()
                    ->values();

                if ($paths->isNotEmpty()) {
                    Storage::disk('public')->delete($paths->all());
                }
            }

            $item->delete();

            ActivityLogger::log('recycle', 'delete-permanent', null, [
                'module' => $item->module,
                'model_type' => $item->model_type,
                'model_id' => $item->model_id,
            ]);

            return back()->with('success', 'Data berhasil dihapus permanen.');
        } catch (Throwable $e) {
            return back()->withErrors(['recycle' => 'Gagal hapus permanen: ' . $e->getMessage()]);
        }
    }
}
