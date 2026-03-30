<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Faq;
use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use App\Models\ServiceCategory;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        return view('public.home', [
            'featuredPortfolios' => Portfolio::with(['category', 'images'])->where('is_published', true)->latest()->take(6)->get(),
            'serviceCategories' => ServiceCategory::with('services')->orderBy('sort_order')->get(),
            'addons' => Addon::where('is_active', true)->orderBy('name')->get(),
            'testimonials' => Testimonial::where('is_published', true)->latest()->take(6)->get(),
            'faqs' => Faq::where('is_published', true)->orderBy('sort_order')->take(6)->get(),
            'portfolioCategories' => PortfolioCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function about()
    {
        return view('public.about');
    }

    public function contact()
    {
        return view('public.contact');
    }
}
