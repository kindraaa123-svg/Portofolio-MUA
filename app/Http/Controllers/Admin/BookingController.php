<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvailabilitySlot;
use App\Models\BlockedSchedule;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\BookingStatusLog;
use App\Support\ActivityLogger;
use App\Support\FonnteWhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $search = $request->string('q')->toString();

        $bookings = Booking::with(['customer', 'details', 'payments'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'status' => $status,
            'search' => $search,
            'slots' => AvailabilitySlot::orderBy('day_of_week')->orderBy('start_time')->get(),
            'blockedSchedules' => BlockedSchedule::latest('blocked_date')->take(20)->get(),
        ]);
    }

    public function paymentValidations(Request $request)
    {
        $status = $request->string('status')->toString();
        $search = $request->string('q')->toString();

        $payments = BookingPayment::with(['booking.customer'])
            ->where('payment_type', 'dp')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where('payer_name', 'like', "%{$search}%")
                    ->orWhereHas('booking', function ($b) use ($search) {
                        $b->where('booking_code', 'like', "%{$search}%")
                            ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.payment-validations', [
            'payments' => $payments,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function storeSlot(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'max_bookings' => ['required', 'integer', 'min:1'],
        ]);

        AvailabilitySlot::create([
            ...$data,
            'is_active' => true,
        ]);

        ActivityLogger::log('booking', 'create-slot', null, $data);

        return back()->with('success', 'Slot jam berhasil ditambahkan.');
    }

    public function storeBlockedSchedule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'blocked_date' => ['required', 'date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'reason' => ['nullable', 'string', 'max:190'],
            'is_full_day' => ['nullable', 'boolean'],
        ]);

        $blocked = BlockedSchedule::create([
            ...$data,
            'is_full_day' => $request->boolean('is_full_day'),
        ]);

        ActivityLogger::log('booking', 'create-blocked-schedule', $blocked, $data);

        return back()->with('success', 'Jadwal blokir berhasil disimpan.');
    }

    public function verifyPayment(Request $request, BookingPayment $payment): RedirectResponse
    {
        if ($payment->status !== 'pending') {
            return back()->withErrors(['status' => 'Status pembayaran ini sudah final dan tidak bisa diubah lagi.']);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:verified,rejected'],
        ]);

        $payment->update([
            'status' => $validated['status'],
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $booking = $payment->booking;
        $shouldSendApprovedWhatsApp = false;

        if ($validated['status'] === 'verified' && $payment->payment_type === 'dp') {
            $oldStatus = $booking->status;
            $booking->update([
                'payment_status' => 'dp_paid',
                'status' => $booking->status === 'pending' ? 'confirmed' : $booking->status,
                'handled_by' => auth()->id(),
            ]);

            $shouldSendApprovedWhatsApp = $oldStatus !== 'confirmed' && $booking->status === 'confirmed';

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $booking->status,
                'note' => 'DP diverifikasi admin.',
                'created_by' => auth()->id(),
            ]);
        }

        if ($validated['status'] === 'rejected') {
            $booking->update([
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'handled_by' => auth()->id(),
            ]);
        }

        ActivityLogger::log('booking', 'verify-payment', $booking, [
            'payment_id' => $payment->id,
            'status' => $payment->status,
        ]);

        if ($shouldSendApprovedWhatsApp) {
            app(FonnteWhatsApp::class)->sendReservationApproved($booking);
        }

        return back()->with('success', 'Verifikasi pembayaran berhasil disimpan.');
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,on_process,completed,cancelled'],
            'payment_status' => ['required', 'in:unpaid,dp_paid,paid'],
            'dp_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'confirmed' && $booking->payment_status === 'unpaid' && $validated['payment_status'] === 'unpaid') {
            return back()->withErrors(['status' => 'Booking tidak bisa dikonfirmasi sebelum DP diverifikasi.']);
        }

        $oldStatus = $booking->status;

        $booking->update([
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'],
            'dp_amount' => $validated['dp_amount'] ?? $booking->dp_amount,
            'handled_by' => auth()->id(),
        ]);

        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'old_status' => $oldStatus,
            'new_status' => $booking->status,
            'note' => $validated['note'] ?? null,
            'created_by' => auth()->id(),
        ]);

        ActivityLogger::log('booking', 'update-status', $booking, [
            'old_status' => $oldStatus,
            'new_status' => $booking->status,
            'payment_status' => $booking->payment_status,
        ]);

        if ($oldStatus !== 'confirmed' && $booking->status === 'confirmed') {
            app(FonnteWhatsApp::class)->sendReservationApproved($booking);
        }

        return back()->with('success', 'Status reservasi diperbarui.');
    }

    public function export()
    {
        $fileName = 'bookings-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        ActivityLogger::log('booking', 'export', null, ['file_name' => $fileName]);

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Kode', 'Nama', 'Tanggal', 'Jam', 'Status', 'Pembayaran', 'Total']);

            Booking::with('customer')->chunk(200, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->booking_code,
                        $row->customer?->name,
                        $row->booking_date?->format('Y-m-d'),
                        $row->booking_time,
                        $row->status,
                        $row->payment_status,
                        $row->grand_total,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
