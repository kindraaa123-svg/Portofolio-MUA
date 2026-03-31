<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationalHour;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OperationalHourController extends Controller
{
    public function index()
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            0 => 'Minggu',
        ];

        $hours = OperationalHour::query()->orderByRaw('FIELD(day_of_week, 1,2,3,4,5,6,0)')->get()->keyBy('day_of_week');

        $rows = collect($days)->map(function ($dayName, $dayOfWeek) use ($hours) {
            $item = $hours->get($dayOfWeek);

            return [
                'day_of_week' => $dayOfWeek,
                'day_name' => $dayName,
                'open_time' => $item?->open_time ?? '09:00',
                'close_time' => $item?->close_time ?? '17:00',
                'is_closed' => $item?->is_closed ?? false,
            ];
        });

        return view('admin.operational-hours.index', [
            'rows' => $rows,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'hours.*.day_name' => ['required', 'string', 'max:20'],
            'hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'hours.*.close_time' => ['nullable', 'date_format:H:i'],
            'hours.*.is_closed' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['hours'] as $entry) {
            $isClosed = (bool) ($entry['is_closed'] ?? false);

            if (! $isClosed) {
                if (empty($entry['open_time']) || empty($entry['close_time'])) {
                    return back()->withErrors(['hours' => "Jam buka/tutup wajib diisi untuk {$entry['day_name']}."])->withInput();
                }

                if ($entry['open_time'] >= $entry['close_time']) {
                    return back()->withErrors(['hours' => "Jam tutup harus lebih besar dari jam buka untuk {$entry['day_name']}."])->withInput();
                }
            }

            OperationalHour::updateOrCreate(
                ['day_of_week' => $entry['day_of_week']],
                [
                    'day_name' => $entry['day_name'],
                    'open_time' => $isClosed ? null : $entry['open_time'],
                    'close_time' => $isClosed ? null : $entry['close_time'],
                    'is_closed' => $isClosed,
                ]
            );
        }

        ActivityLogger::log('operational-hour', 'update', null, ['count' => count($validated['hours'])]);

        return back()->with('success', 'Jam operasional berhasil diperbarui.');
    }
}
