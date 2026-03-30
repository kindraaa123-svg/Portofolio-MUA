<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use App\Models\PortfolioImage;
use App\Models\RecycleBin;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();

        $portfolios = Portfolio::with('category')
            ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.portfolio.index', compact('portfolios', 'search'));
    }

    public function create()
    {
        return view('admin.portfolio.form', [
            'portfolio' => new Portfolio(),
            'categories' => PortfolioCategory::orderBy('name')->get(),
            'action' => route('admin.portfolios.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePortfolio($request);

        DB::transaction(function () use ($request, $data): void {
            $portfolio = Portfolio::create([
                ...$data,
                'slug' => Str::slug($data['title']) . '-' . Str::lower(Str::random(5)),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'cover_image' => $request->hasFile('cover_image')
                    ? $request->file('cover_image')->store('portfolios/covers', 'public')
                    : null,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    PortfolioImage::create([
                        'portfolio_id' => $portfolio->id,
                        'image_path' => $image->store('portfolios/gallery', 'public'),
                        'sort_order' => $index,
                    ]);
                }
            }

            ActivityLogger::log('portfolio', 'create', $portfolio, ['title' => $portfolio->title]);
        });

        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio berhasil ditambahkan.');
    }

    public function edit(Portfolio $portfolio)
    {
        return view('admin.portfolio.form', [
            'portfolio' => $portfolio->load('images'),
            'categories' => PortfolioCategory::orderBy('name')->get(),
            'action' => route('admin.portfolios.update', $portfolio),
        ]);
    }

    public function update(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $data = $this->validatePortfolio($request);

        DB::transaction(function () use ($request, $data, $portfolio): void {
            if ($request->hasFile('cover_image')) {
                if ($portfolio->cover_image) {
                    Storage::disk('public')->delete($portfolio->cover_image);
                }
                $data['cover_image'] = $request->file('cover_image')->store('portfolios/covers', 'public');
            }

            $portfolio->update([...$data, 'updated_by' => auth()->id()]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    PortfolioImage::create([
                        'portfolio_id' => $portfolio->id,
                        'image_path' => $image->store('portfolios/gallery', 'public'),
                        'sort_order' => $index,
                    ]);
                }
            }

            ActivityLogger::log('portfolio', 'update', $portfolio, ['title' => $portfolio->title]);
        });

        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio berhasil diperbarui.');
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        RecycleBin::create([
            'module' => 'portfolio',
            'model_type' => Portfolio::class,
            'model_id' => $portfolio->id,
            'payload' => $portfolio->toArray(),
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        $portfolio->delete();

        ActivityLogger::log('portfolio', 'delete', $portfolio, ['title' => $portfolio->title]);

        return back()->with('success', 'Portfolio dipindahkan ke recycle bin.');
    }

    protected function validatePortfolio(Request $request): array
    {
        return $request->validate([
            'portfolio_category_id' => ['nullable', 'exists:portfolio_categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'client_name' => ['nullable', 'string', 'max:120'],
            'work_date' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'images.*' => ['nullable', 'image', 'max:4096'],
        ]);
    }
}
