<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreReservationRequest;
use App\Services\WebsocketGateway\Websocket;
use App\Models\Seat;
use App\Models\Reservation;
use Carbon\Carbon;
use Auth;

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
            'time_end',
            'subject'
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

        return $this->_getAvailableSeats($roomId, $startDate, $endDate);
    }

    public function getTakenSeats(Request $request)
    {
        $startDate = $request->get('time_start');
        $endDate = $request->get('time_end');
        $roomId = $request->get('room_id');
        $availableSeats = $this->_getAvailableSeats($roomId, $startDate, $endDate);
        $availableSeatIds = array_map(function ($seat) {
            return $seat->id;
        }, $availableSeats);

        $allSeatIds = Seat::where('room_id', $roomId)
                               ->pluck('id')
                               ->toArray();

        $takenSeatIds = array_values(array_diff($allSeatIds, $availableSeatIds));
        return Seat::findMany($takenSeatIds);
    }

    public function currentUserFutureReservations()
    {
        $userId = Auth::user()->id;
        $timeOffset = config('reservation.time_offset');
        $now = Carbon::now();
        $now->second = 0;

        $futureReservations = Reservation::join('seats', 'reservations.seat_id', '=', 'seats.id')
                                         ->where('reservations.user_id', $userId)
                                         ->where('reservations.time_start', '>', $now)
                                         ->orWhere('reservations.time_start', '>', $now->subMinutes($timeOffset))
                                         ->get();
        return $futureReservations;
    }

    protected function _getAvailableSeats($roomId, $startDate, $endDate) {
        $seats = Seat::where('room_id', $roomId)->get();
        $freeSeats = [];
        foreach ($seats as $seat) {
            $reservations = Reservation::where('seat_id', $seat->id)->get();
            $isOk = true;
            foreach ($reservations as $reservation) {
                if ($reservation->end_date >= $startDate || $reservation->start_date <= $endDate) {
                    $isOk = false;
                    break;
                }
            }
            if ($isOk) {
                array_push($freeSeats, $seat);
            }
        }

        return $freeSeats;
    }
}
