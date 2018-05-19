<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seat;
use App\Models\Reservation;
use App\Models\Room;

class ReservationController extends Controller
{
    public function getAvailableSeats(Request $request)
    {
        $startDate = $request->get('time_start');
        $endDate = $request->get('time_end');
        $roomId = $request->get('room_id');

        $seats = Seat::leftJoin('reservations', function($join) use ($roomId, $startDate, $endDate) {
            $join->on('seats.id', '=', 'reservations.seat_id');
        })
        ->where(function ($query) use ($roomId, $startDate, $endDate) {
            $query->where('seats.room_id', $roomId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereNotBetween('time_start', [ $startDate, $endDate ])
                        ->whereNotBetween('time_end', [ $startDate, $endDate ]);
            })
            ->orWhere(function ($query) {
                $query->whereNull('time_start')
                        ->whereNull('time_end');
            });
        })
        ->get(['seats.*']);

        return $seats;
    }
}
