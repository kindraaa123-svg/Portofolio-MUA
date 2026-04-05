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
use App\Models\OperationalHour;
use App\Models\Service;
use App\Models\WebsiteSetting;
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
            'service_id' => ['required', 'exists:services,id'],
        ]);

        return response()->json(['times' => $this->availableTimesByDate($validated['date'], (int) $validated['service_id'])]);
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
            'dp_proof' => ['required', 'image', 'max:4096'],
        ]);

        $normalizedPhone = app(FonnteWhatsApp::class)->normalizeForStorage($validated['phone']) ?? $validated['phone'];
        $validated['phone'] = $normalizedPhone;

        $service = Service::findOrFail($validated['service_id']);
        $addons = Addon::whereIn('id', $validated['addon_ids'] ?? [])->get();

        $availableTimes = $this->availableTimesByDate($validated['booking_date'], (int) $validated['service_id']);
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
                'bank_name' => null,
                'amount' => $dpAmount,
                'paid_at' => now(),
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
            $websiteName = WebsiteSetting::query()->value('site_name') ?: (config('app.name') ?: 'MUA Studio');

            if (! empty($validated['email'])) {
                $customerEmailBody = implode("\n", [
                    'Yth. ' . $validated['name'] . ',',
                    '',
                    'Terima kasih telah melakukan reservasi layanan makeup melalui website kami.',
                    'Reservasi Anda telah kami terima dengan detail berikut:',
                    '',
                    "Kode Booking : {$booking->booking_code}",
                    'Tanggal      : ' . ($booking->booking_date?->format('d-m-Y') ?? '-'),
                    "Jam          : {$booking->booking_time}",
                    'Total        : Rp ' . number_format((float) $booking->grand_total, 0, ',', '.'),
                    'DP (50%)     : Rp ' . number_format((float) $booking->dp_amount, 0, ',', '.'),
                    '',
                    'Status reservasi saat ini: Menunggu verifikasi pembayaran DP.',
                    'Tim kami akan menghubungi Anda kembali setelah proses verifikasi selesai.',
                    '',
                    'Hormat kami,',
                    $websiteName,
                ]);

                Mail::raw(
                    $customerEmailBody,
                    function ($message) use ($validated): void {
                        $message->to($validated['email'])->subject('Konfirmasi Reservasi Makeup');
                    }
                );
            }

            $ownerEmail = WebsiteSetting::query()->value('contact_email');
            if (! empty($ownerEmail)) {
                Mail::raw(
                    implode("\n", [
                        'Notifikasi Reservasi Baru dari Website',
                        '',
                        "Kode Booking: {$booking->booking_code}",
                        "Nama Customer: {$validated['name']}",
                        'Email Customer: ' . ($validated['email'] ?? '-'),
                        "No. WhatsApp  : {$validated['phone']}",
                        'Tanggal       : ' . ($booking->booking_date?->format('d-m-Y') ?? '-'),
                        "Jam           : {$booking->booking_time}",
                        'Total         : Rp ' . number_format((float) $booking->grand_total, 0, ',', '.'),
                        'DP (50%)      : Rp ' . number_format((float) $booking->dp_amount, 0, ',', '.'),
                        '',
                        'Silakan cek dashboard admin untuk proses verifikasi dan tindak lanjut.',
                    ]),
                    function ($message) use ($ownerEmail): void {
                        $message->to($ownerEmail)->subject('Reservasi Baru Website');
                    }
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Email booking notification failed', ['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Reservasi berhasil dibuat. Booking akan dikonfirmasi setelah admin memverifikasi pembayaran DP 50%.');
    }

    protected function availableTimesByDate(string $date, int $serviceId): array
    {
        $selectedService = Service::query()->find($serviceId);
        if (! $selectedService) {
            return [];
        }

        $selectedDurationMinutes = max(1, (int) $selectedService->duration_minutes);
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $blockedSchedules = BlockedSchedule::whereDate('blocked_date', $date)->get();
        if ($blockedSchedules->contains(fn ($item) => $item->is_full_day)) {
            return [];
        }

        $operationalHour = OperationalHour::query()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (! $operationalHour || $operationalHour->is_closed || ! $operationalHour->open_time || ! $operationalHour->close_time) {
            return [];
        }

        $slots = AvailabilitySlot::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $openAt = Carbon::parse($date . ' ' . substr((string) $operationalHour->open_time, 0, 8));
        $closeAt = Carbon::parse($date . ' ' . substr((string) $operationalHour->close_time, 0, 8));

        if ($openAt->gte($closeAt)) {
            return [];
        }

        $activeBookings = Booking::query()
            ->with([
                'details' => function ($query): void {
                    $query->where('type', 'service')
                        ->with('service:id,duration_minutes');
                },
            ])
            ->whereDate('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'on_process'])
            ->get();

        $times = [];
        for ($candidate = $openAt->copy(); $candidate->lt($closeAt); $candidate->addHour()) {
            $candidateTime = $candidate->format('H:i:s');
            $candidateEnd = $candidate->copy()->addMinutes($selectedDurationMinutes);

            if ($candidateEnd->gt($closeAt)) {
                continue;
            }

            $isBlocked = $blockedSchedules->contains(function ($block) use ($candidate, $candidateEnd, $date) {
                if (! $block->start_time || ! $block->end_time) {
                    return false;
                }

                $blockStart = Carbon::parse($date . ' ' . substr((string) $block->start_time, 0, 8));
                $blockEnd = Carbon::parse($date . ' ' . substr((string) $block->end_time, 0, 8));

                return $candidate->lt($blockEnd) && $candidateEnd->gt($blockStart);
            });

            if ($isBlocked) {
                continue;
            }

            $slotForCapacity = $slots->first(function ($slot) use ($candidateTime) {
                return $candidateTime >= $slot->start_time && $candidateTime < $slot->end_time;
            });
            $maxBookings = (int) ($slotForCapacity?->max_bookings ?? 1);

            $currentCount = $activeBookings->filter(function (Booking $booking) use ($candidate, $candidateEnd, $date) {
                $bookingStart = Carbon::parse($date . ' ' . substr((string) $booking->booking_time, 0, 8));

                $serviceDetail = $booking->details->first();
                $durationMinutes = max(1, (int) ($serviceDetail?->service?->duration_minutes ?? 60));
                $bookingEnd = $bookingStart->copy()->addMinutes($durationMinutes);

                return $candidate->lt($bookingEnd) && $candidateEnd->gt($bookingStart);
            })->count();

            if ($currentCount < $maxBookings) {
                $times[] = $candidate->format('H:i');
            }
        }

        return $times;
    }
}
