<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'setting' => WebsiteSetting::first(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:160'],
            'tagline' => ['nullable', 'string', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:32'],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'whatsapp_number' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string'],
            'instagram_url' => ['nullable', 'url'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'theme_primary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_secondary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'home_banner' => ['nullable', 'image', 'max:8192'],
            'logo_cropped' => ['nullable', 'string'],
            'home_banner_cropped' => ['nullable', 'string'],
        ]);

        $setting = WebsiteSetting::firstOrCreate([], ['site_name' => 'MUA Studio']);

        foreach (['logo', 'home_banner'] as $fileField) {
            $croppedField = $fileField . '_cropped';
            if (! empty($data[$croppedField])) {
                if ($setting->{$fileField}) {
                    Storage::disk('public')->delete($setting->{$fileField});
                }

                try {
                    $data[$fileField] = $this->storeCroppedImage($data[$croppedField], 'settings');
                } catch (\InvalidArgumentException $e) {
                    return back()->withErrors(['image_crop' => $e->getMessage()])->withInput();
                }
            } elseif ($request->hasFile($fileField)) {
                if ($setting->{$fileField}) {
                    Storage::disk('public')->delete($setting->{$fileField});
                }

                $data[$fileField] = $request->file($fileField)->store('settings', 'public');
            }

            unset($data[$croppedField]);
        }

        if (! empty($data['logo'])) {
            $data['favicon'] = $data['logo'];
        }

        $data['meta_title'] = $data['site_name'];

        $setting->update($data);

        ActivityLogger::log('setting', 'update', $setting, ['site_name' => $setting->site_name]);

        return back()->with('success', 'Pengaturan website berhasil disimpan.');
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme_primary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_secondary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $setting = WebsiteSetting::firstOrCreate([], ['site_name' => 'MUA Studio']);
        $setting->update($data);

        ActivityLogger::log('setting', 'update', $setting, [
            'theme_primary' => $setting->theme_primary,
            'theme_secondary' => $setting->theme_secondary,
        ]);

        return back()->with('success', 'Warna website berhasil disimpan ke database.');
    }

    protected function storeCroppedImage(string $base64Image, string $directory): string
    {
        if (! preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            throw new \InvalidArgumentException('Format gambar crop tidak valid.');
        }

        $extension = strtolower($matches[1]);
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'png';

        $imageData = base64_decode(substr($base64Image, strpos($base64Image, ',') + 1));
        if ($imageData === false) {
            throw new \InvalidArgumentException('Data crop gambar tidak dapat diproses.');
        }

        $filename = $directory . '/' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . $extension;
        Storage::disk('public')->put($filename, $imageData);

        return $filename;
    }
}
