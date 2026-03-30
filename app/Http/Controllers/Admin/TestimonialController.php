<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::latest()->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:150'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $testimonial = Testimonial::create([
            ...$data,
            'is_published' => $request->boolean('is_published', true),
        ]);

        ActivityLogger::log('testimonial', 'create', $testimonial, ['name' => $testimonial->name]);

        return back()->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        ActivityLogger::log('testimonial', 'delete', $testimonial, ['name' => $testimonial->name]);
        $testimonial->delete();

        return back()->with('success', 'Testimoni dihapus.');
    }
}
