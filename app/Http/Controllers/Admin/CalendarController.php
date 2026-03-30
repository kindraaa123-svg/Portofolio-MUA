<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin.calendar.index', [
            'bookings' => Booking::with('customer')->orderBy('booking_date')->orderBy('booking_time')->get(),
        ]);
    }
}
