<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        return view('admin.faqs.index', [
            'faqs' => Faq::orderBy('sort_order')->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $faq = Faq::create([
            ...$data,
            'is_published' => $request->boolean('is_published', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        ActivityLogger::log('faq', 'create', $faq, ['question' => $faq->question]);

        return back()->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        ActivityLogger::log('faq', 'delete', $faq, ['question' => $faq->question]);
        $faq->delete();

        return back()->with('success', 'FAQ dihapus.');
    }
}
