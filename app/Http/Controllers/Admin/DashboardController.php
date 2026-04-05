<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $yesterdayStart = $now->copy()->subDay()->startOfDay();
        $yesterdayEnd = $now->copy()->subDay()->endOfDay();
        $thisMonthStart = $now->copy()->startOfMonth();
        $thisMonthEnd = $now->copy()->endOfMonth();
        $lastMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $verifiedPaymentBase = BookingPayment::query()->where('status', 'verified');

        $todayPayments = $this->withinTransactionPeriod(clone $verifiedPaymentBase, $todayStart, $todayEnd)
            ->get(['amount', 'paid_at', 'created_at']);

        $hourlyMap = [];
        foreach ($todayPayments as $payment) {
            $time = $payment->paid_at ?? $payment->created_at;
            $hour = (int) $time->format('H');
            $hourlyMap[$hour] ??= ['count' => 0, 'amount' => 0.0];
            $hourlyMap[$hour]['count']++;
            $hourlyMap[$hour]['amount'] += (float) $payment->amount;
        }

        $hourlyToday = [];
        $maxHourlyAmount = 0.0;
        for ($hour = 0; $hour < 24; $hour++) {
            $amount = (float) ($hourlyMap[$hour]['amount'] ?? 0);
            $count = (int) ($hourlyMap[$hour]['count'] ?? 0);
            $maxHourlyAmount = max($maxHourlyAmount, $amount);
            $hourlyToday[] = [
                'hour' => sprintf('%02d:00', $hour),
                'count' => $count,
                'amount' => $amount,
            ];
        }

        $transactionSummary = [
            'yesterday' => $this->buildTransactionSummary(clone $verifiedPaymentBase, $yesterdayStart, $yesterdayEnd),
            'this_month' => $this->buildTransactionSummary(clone $verifiedPaymentBase, $thisMonthStart, $thisMonthEnd),
            'last_month' => $this->buildTransactionSummary(clone $verifiedPaymentBase, $lastMonthStart, $lastMonthEnd),
            'total' => [
                'count' => (clone $verifiedPaymentBase)->count(),
                'amount' => (float) (clone $verifiedPaymentBase)->sum('amount'),
            ],
        ];

        $incomeReport = [
            'today' => $this->buildTransactionSummary(clone $verifiedPaymentBase, $todayStart, $todayEnd),
            'average_daily_this_month' => $thisMonthStart->diffInDays($now) >= 0
                ? $transactionSummary['this_month']['amount'] / max(1, $thisMonthStart->diffInDays($now) + 1)
                : 0,
        ];

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
            'hourlyToday' => $hourlyToday,
            'maxHourlyAmount' => $maxHourlyAmount,
            'transactionSummary' => $transactionSummary,
            'incomeReport' => $incomeReport,
        ]);
    }

    private function buildTransactionSummary(Builder $query, $start, $end): array
    {
        $periodQuery = $this->withinTransactionPeriod($query, $start, $end);

        return [
            'count' => (clone $periodQuery)->count(),
            'amount' => (float) (clone $periodQuery)->sum('amount'),
        ];
    }

    private function withinTransactionPeriod(Builder $query, $start, $end): Builder
    {
        return $query->where(function (Builder $inner) use ($start, $end) {
            $inner->whereBetween('paid_at', [$start, $end])
                ->orWhere(function (Builder $fallback) use ($start, $end) {
                    $fallback->whereNull('paid_at')
                        ->whereBetween('created_at', [$start, $end]);
                });
        });
    }
}
