<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\Testimonial;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'bookings' => Booking::count(),
            'pendingBookings' => Booking::where('status', 'pending')->count(),
            'portfolios' => Portfolio::count(),
            'services' => Service::count(),
            'testimonials' => Testimonial::count(),
            'revenue' => Booking::where('status', 'completed')->sum('grand_total'),
        ];

        return view('admin.dashboard.index', [
            'stats' => $stats,
            'latestBookings' => Booking::with('customer')->latest()->take(8)->get(),
        ]);
    }
}
