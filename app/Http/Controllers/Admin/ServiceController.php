<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\RecycleBin;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();

        return view('admin.services.index', [
            'services' => Service::with('category')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'addons' => Addon::latest()->paginate(10, ['*'], 'addons_page'),
            'search' => $search,
            'categories' => ServiceCategory::orderBy('name')->get(),
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_category_id' => ['nullable', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'home_service_fee' => ['nullable', 'numeric', 'min:0'],
            'is_home_service_available' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service = Service::create([
            ...$data,
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(4)),
            'is_home_service_available' => $request->boolean('is_home_service_available'),
            'home_service_fee' => $data['home_service_fee'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        ActivityLogger::log('service', 'create', $service, ['name' => $service->name]);

        return back()->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function updateService(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'service_category_id' => ['nullable', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'home_service_fee' => ['nullable', 'numeric', 'min:0'],
            'is_home_service_available' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service->update([
            ...$data,
            'is_home_service_available' => $request->boolean('is_home_service_available'),
            'home_service_fee' => $data['home_service_fee'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => auth()->id(),
        ]);

        ActivityLogger::log('service', 'update', $service, ['name' => $service->name]);

        return back()->with('success', 'Layanan berhasil diperbarui.');
    }

    public function storeAddon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $addon = Addon::create([
            ...$data,
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(4)),
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log('addon', 'create', $addon, ['name' => $addon->name]);

        return back()->with('success', 'Add-on berhasil ditambahkan.');
    }

    public function updateAddon(Request $request, Addon $addon): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $addon->update([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log('addon', 'update', $addon, ['name' => $addon->name]);

        return back()->with('success', 'Add-on berhasil diperbarui.');
    }

    public function destroyService(Service $service): RedirectResponse
    {
        RecycleBin::create([
            'module' => 'service',
            'model_type' => Service::class,
            'model_id' => $service->id,
            'payload' => $service->toArray(),
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        $service->delete();

        ActivityLogger::log('service', 'delete', $service, ['name' => $service->name]);

        return back()->with('success', 'Layanan dipindahkan ke recycle bin.');
    }

    public function destroyAddon(Addon $addon): RedirectResponse
    {
        RecycleBin::create([
            'module' => 'addon',
            'model_type' => Addon::class,
            'model_id' => $addon->id,
            'payload' => $addon->toArray(),
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        $addon->delete();

        ActivityLogger::log('addon', 'delete', $addon, ['name' => $addon->name]);

        return back()->with('success', 'Add-on dipindahkan ke recycle bin.');
    }
}
