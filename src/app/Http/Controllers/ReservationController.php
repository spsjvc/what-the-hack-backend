<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Http\Requests\StoreReservationRequest;
use App\Services\WebsocketGateway\Websocket;

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

        $reservation = Reservation::create($payload);
        $room = $reservation->seat->room;
        $room->load(['seats.user', 'reservations']);

        \Websocket::sendToRoom($room->id, Websocket::EVENT_ROOMS_UPDATED, $room);
        return $reservation;
    }
}
