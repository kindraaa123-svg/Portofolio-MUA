<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->endOfDay();

        $bookingQuery = Booking::whereBetween('created_at', [$fromDate, $toDate]);
        $paymentQuery = BookingPayment::whereBetween('created_at', [$fromDate, $toDate]);

        ActivityLogger::log('report', 'view', null, ['from' => $from, 'to' => $to]);

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'summary' => [
                'total_bookings' => (clone $bookingQuery)->count(),
                'total_pending' => (clone $bookingQuery)->where('status', 'pending')->count(),
                'total_confirmed' => (clone $bookingQuery)->where('status', 'confirmed')->count(),
                'total_transactions' => (float) (clone $bookingQuery)->sum('grand_total'),
                'total_dp_verified' => (float) (clone $paymentQuery)->where('payment_type', 'dp')->where('status', 'verified')->sum('amount'),
                'total_dp_pending' => (float) (clone $paymentQuery)->where('payment_type', 'dp')->where('status', 'pending')->sum('amount'),
            ],
            'bookings' => Booking::with('customer')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'payments' => BookingPayment::with('booking.customer')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->latest()
                ->paginate(20, ['*'], 'payments_page')
                ->withQueryString(),
        ]);
    }
}
