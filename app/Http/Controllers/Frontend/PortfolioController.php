<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $activeCategory = $request->string('category')->toString();

        $portfolios = Portfolio::with(['category', 'images'])
            ->where('is_published', true)
            ->when($activeCategory, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $activeCategory)))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('public.portfolio', [
            'portfolios' => $portfolios,
            'categories' => PortfolioCategory::orderBy('sort_order')->get(),
            'activeCategory' => $activeCategory,
        ]);
    }

    public function show(string $slug)
    {
        $portfolio = Portfolio::with(['category', 'images'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('public.portfolio-detail', [
            'portfolio' => $portfolio,
            'relatedPortfolios' => Portfolio::where('id', '!=', $portfolio->id)
                ->where('portfolio_category_id', $portfolio->portfolio_category_id)
                ->where('is_published', true)
                ->take(4)
                ->get(),
        ]);
    }
}
