<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreReservationRequest;
use App\Services\WebsocketGateway\Websocket;
use App\Models\Seat;
use App\Models\Reservation;
use Carbon\Carbon;

class ReservationController extends Controller
{

    public function index()
    {
        return Reservation::all();
    }

    public function store(StoreReservationRequest $request) {
        $payload = $request->only(
        [
            'user_id',
            'seat_id',
            'time_start',
            'time_end'
        ]);

        $payload['time_start'] = new Carbon($payload['time_start']);
        $payload['time_start']->second = 0;
        $payload['time_end'] = new Carbon($payload['time_end']);
        $payload['time_end']->second = 0;

        $reservation = Reservation::create($payload);
        $room = $reservation->seat->room;
        $room->load(['seats.user', 'reservations']);

        \Websocket::sendToRoom($room->id, Websocket::EVENT_ROOMS_UPDATED, $room);
        return $reservation;
    }

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
