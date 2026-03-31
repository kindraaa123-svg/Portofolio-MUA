<?php

namespace App\Support;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteWhatsApp
{
    public function sendReservationCreated(Booking $booking): void
    {
        $booking->loadMissing('customer');

        $customer = $booking->customer;
        if (! $customer || ! $customer->phone) {
            return;
        }

        $message = implode("\n", [
            "Halo {$customer->name},",
            'Reservasi kamu sudah kami terima.',
            "Kode Booking: {$booking->booking_code}",
            'Tanggal: ' . ($booking->booking_date?->format('d-m-Y') ?? '-'),
            "Jam: {$booking->booking_time}",
            'Status: Menunggu approval admin.',
            'Kami akan kirim update lagi saat reservasi disetujui.',
        ]);

        $this->send($customer->phone, $message);
    }

    public function sendReservationApproved(Booking $booking): void
    {
        $booking->loadMissing('customer');

        $customer = $booking->customer;
        if (! $customer || ! $customer->phone) {
            return;
        }

        $message = implode("\n", [
            "Halo {$customer->name},",
            'Reservasi kamu sudah DISETUJUI.',
            "Kode Booking: {$booking->booking_code}",
            'Tanggal: ' . ($booking->booking_date?->format('d-m-Y') ?? '-'),
            "Jam: {$booking->booking_time}",
            "Status Booking: {$booking->status}",
            'Terima kasih.',
        ]);

        $this->send($customer->phone, $message);
    }

    public function send(string $phone, string $message): void
    {
        $token = config('services.fonnte.token');
        $endpoint = config('services.fonnte.endpoint', 'https://api.fonnte.com/send');

        if (! $token) {
            Log::warning('Fonnte token kosong. Pesan WhatsApp tidak dikirim.');
            return;
        }

        $targets = $this->buildTargets($phone);
        if (count($targets) === 0) {
            Log::warning('Nomor WhatsApp tidak valid untuk Fonnte.', ['phone' => $phone]);
            return;
        }

        foreach ($targets as $target) {
            try {
                $response = Http::asForm()
                    ->withHeaders(['Authorization' => $token])
                    ->timeout(20)
                    ->post($endpoint, [
                        'target' => $target,
                        'message' => $message,
                        'countryCode' => '62',
                    ]);

                $json = $response->json();
                $isSuccess = $response->successful()
                    && (! is_array($json) || (bool) ($json['status'] ?? true) === true);

                if ($isSuccess) {
                    return;
                }

                Log::warning('Fonnte merespons gagal.', [
                    'target' => $target,
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim WhatsApp via Fonnte', [
                    'target' => $target,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function buildTargets(string $phone): array
    {
        $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';
        if ($digits === '') {
            return [];
        }

        $with62 = $digits;
        $with08 = $digits;

        if (str_starts_with($digits, '0')) {
            $with62 = '62' . substr($digits, 1);
            $with08 = $digits;
        } elseif (str_starts_with($digits, '62')) {
            $with62 = $digits;
            $with08 = '0' . substr($digits, 2);
        } else {
            $with62 = '62' . $digits;
            $with08 = '0' . $digits;
        }

        $targets = array_values(array_unique(array_filter([$with62, $with08], fn ($item) => strlen($item) >= 10)));

        return $targets;
    }

    public function normalizeForStorage(string $phone): ?string
    {
        $targets = $this->buildTargets($phone);
        if (count($targets) === 0) {
            return null;
        }

        return $targets[0];
    }
}
