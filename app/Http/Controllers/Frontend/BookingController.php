<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\AvailabilitySlot;
use App\Models\BlockedSchedule;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingPayment;
use App\Models\Customer;
use App\Models\Service;
use App\Support\ActivityLogger;
use App\Support\FonnteWhatsApp;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function create()
    {
        return view('public.booking', [
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
            'addons' => Addon::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function availableTimes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        return response()->json(['times' => $this->availableTimesByDate($validated['date'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:32'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'service_id' => ['required', 'exists:services,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required'],
            'location_type' => ['required', 'in:studio,home_service'],
            'location_address' => ['required_if:location_type,home_service', 'nullable', 'string'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['exists:addons,id'],
            'notes' => ['nullable', 'string'],
            'payer_name' => ['required', 'string', 'max:150'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'transfer_at' => ['required', 'date'],
            'dp_proof' => ['required', 'image', 'max:4096'],
        ]);

        $normalizedPhone = app(FonnteWhatsApp::class)->normalizeForStorage($validated['phone']) ?? $validated['phone'];
        $validated['phone'] = $normalizedPhone;

        $service = Service::findOrFail($validated['service_id']);
        $addons = Addon::whereIn('id', $validated['addon_ids'] ?? [])->get();

        $availableTimes = $this->availableTimesByDate($validated['booking_date']);
        if (! in_array($validated['booking_time'], $availableTimes, true)) {
            return back()->withErrors(['booking_time' => 'Jam booking sudah tidak tersedia, silakan pilih jam lain.'])->withInput();
        }

        $booking = DB::transaction(function () use ($request, $validated, $service, $addons) {
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['phone']],
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                    'instagram' => $validated['instagram'] ?? null,
                ]
            );

            $subtotal = (float) $service->price;
            $addonTotal = (float) $addons->sum('price');
            $homeServiceTotal = $validated['location_type'] === 'home_service' ? (float) $service->home_service_fee : 0;
            $grandTotal = $subtotal + $addonTotal + $homeServiceTotal;
            $dpAmount = round($grandTotal * 0.5, 2);

            $booking = Booking::create([
                'booking_code' => 'BK-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'customer_id' => $customer->id,
                'booking_date' => $validated['booking_date'],
                'booking_time' => $validated['booking_time'],
                'location_type' => $validated['location_type'],
                'location_address' => $validated['location_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'addon_total' => $addonTotal,
                'home_service_total' => $homeServiceTotal,
                'grand_total' => $grandTotal,
                'dp_amount' => $dpAmount,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            BookingDetail::create([
                'booking_id' => $booking->id,
                'service_id' => $service->id,
                'type' => 'service',
                'item_name' => $service->name,
                'price' => $service->price,
                'qty' => 1,
                'line_total' => $service->price,
            ]);

            foreach ($addons as $addon) {
                BookingDetail::create([
                    'booking_id' => $booking->id,
                    'addon_id' => $addon->id,
                    'type' => 'addon',
                    'item_name' => $addon->name,
                    'price' => $addon->price,
                    'qty' => 1,
                    'line_total' => $addon->price,
                ]);
            }

            BookingPayment::create([
                'booking_id' => $booking->id,
                'payment_type' => 'dp',
                'payer_name' => $validated['payer_name'],
                'bank_name' => $validated['bank_name'] ?? null,
                'amount' => $dpAmount,
                'paid_at' => $validated['transfer_at'],
                'proof_image' => $request->file('dp_proof')->store('payments/dp', 'public'),
                'status' => 'pending',
            ]);

            ActivityLogger::log('booking', 'create-public', $booking, [
                'booking_code' => $booking->booking_code,
                'grand_total' => $grandTotal,
                'dp_amount' => $dpAmount,
            ]);

            return $booking;
        });

        try {
            if (! empty($validated['email'])) {
                Mail::raw(
                    'Reservasi Anda berhasil dicatat dengan kode: ' . $booking->booking_code . '. Booking akan dikonfirmasi setelah verifikasi DP 50%.',
                    function ($message) use ($validated): void {
                        $message->to($validated['email'])->subject('Konfirmasi Reservasi Makeup');
                    }
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Email booking notification failed', ['error' => $e->getMessage()]);
        }

        app(FonnteWhatsApp::class)->sendReservationCreated($booking);

        return back()->with('success', 'Reservasi berhasil dibuat. Booking akan dikonfirmasi setelah admin memverifikasi pembayaran DP 50%.');
    }

    protected function availableTimesByDate(string $date): array
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $blockedSchedules = BlockedSchedule::whereDate('blocked_date', $date)->get();
        if ($blockedSchedules->contains(fn ($item) => $item->is_full_day)) {
            return [];
        }

        $slots = AvailabilitySlot::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $times = [];
        foreach ($slots as $slot) {
            $isBlocked = $blockedSchedules->contains(function ($block) use ($slot) {
                if (! $block->start_time || ! $block->end_time) {
                    return false;
                }

                return $slot->start_time >= $block->start_time && $slot->start_time < $block->end_time;
            });

            if ($isBlocked) {
                continue;
            }

            $currentCount = Booking::whereDate('booking_date', $date)
                ->whereTime('booking_time', $slot->start_time)
                ->whereIn('status', ['pending', 'confirmed', 'on_process'])
                ->count();

            if ($currentCount < $slot->max_bookings) {
                $times[] = $slot->start_time;
            }
        }

        return $times;
    }
}
