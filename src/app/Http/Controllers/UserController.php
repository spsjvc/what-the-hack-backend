<?php

namespace App\Http\Controllers;

use App\Services\WebsocketGateway\Websocket;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Seat;
use App\Models\Reservation;
use Carbon\Carbon;

class UserController extends Controller
{
    public function getUserByToken(Request $request)
    {
        \JWTAuth::setToken($request->get('access_token'));
        $tokenPayload = \JWTAuth::getPayload();

        $user = User::find($tokenPayload['sub']);
        $timeOffset = config('reservation.time_offset');

        $reservation = $user->reservations()
                            ->whereBetween('time_start', [ Carbon::now()->subMinutes($timeOffset), Carbon::now()->addMinutes($timeOffset) ])
                            ->first();

        $inside = false;
        $status = null;

        if (isset($reservation)) { // Lik ima rezervaciju
            $seat = $reservation->seat;
            $room = $seat->room;
            $room->load('reservations');
            if (!isset($reservation->seat->user_id)) { // Na njegovom mestu niko ne sedi - TEK ULAZI
                $seat->update(['user_id' => $user->id]);
                \Websocket::sendToRoom($room->id, Websocket::EVENT_ROOMS_UPDATED, $room);
                $reservation->seat->user->increaseExperience('USER_LOGGED_IN');
                return compact(['user', 'reservation', 'inside', 'status']);
            }

            if ($reservation->seat->user_id === $user->id) { // Sedi na svom mestu - HOCE NAPOLJE / NA PAUZU ILI ULAZI SA PAUZE
                $inside = true;
                $status = isset($reservation->pause_start) ? 'pause' : null; // Da li je dolazi sa pauze ili ne

                if ($status === 'pause') { // Ako dolazi sa pauze
                    $reservation->update([ 'pause_start' => null ]); // Setujem da se vratio sa pauze
                }

                return compact(['user', 'reservation', 'inside', 'status']);
            }
        }

        $reservation = null;
        return compact(['user', 'reservation', 'inside', 'status']); // Nema rezervacije - HOCE UNUTRA BEZ REZERVACIJE
    }

    public function exitFromRoom(Request $request)
    {
        \JWTAuth::setToken($request->get('access_token'));
        $tokenPayload = \JWTAuth::getPayload();

        $user = User::find($tokenPayload['sub']);
        $timeOffset = config('reservation.time_offset');

        $reservation = $user->reservations()
                            ->whereBetween('time_start', [ Carbon::now()->subMinutes($timeOffset), Carbon::now()->addMinutes($timeOffset) ])
                            ->first();

        if ($reservation) {
            $inside = true;
            $status = null;

            $seat = $reservation->seat;
            $seat->update(['user_id' => null]);
            $room = $seat->room;
            $room->load('reservations');


            $now = Carbon::now();
            $reservationEnd = $reservation->time_end;
            $totalDuration = $reservationEnd->diffInSeconds($now);
            $HALF_AN_HOUR = 60 * 30;
            if ($totalDuration > $HALF_AN_HOUR) {
                $user->decreaseExperience('USER_LOGGED_OUT_EARLIER');
            } else {
                $user->increaseExperience('USER_LOGGED_OUT_ON_TIME');
            }


            Reservation::where('id', $reservation->id)
                       ->delete();
            \Websocket::sendToRoom($room->id, Websocket::EVENT_ROOMS_UPDATED, $room);
            return compact(['user', 'reservation', 'inside', 'status']);
        }
    }

    public function pause(Request $request)
    {
        \JWTAuth::setToken($request->get('access_token'));
        $tokenPayload = \JWTAuth::getPayload();

        $user = User::find($tokenPayload['sub']);
        $timeOffset = config('reservation.time_offset');

        $reservation = $user->reservations()
                            ->whereBetween('time_start', [ Carbon::now()->subMinutes($timeOffset), Carbon::now()->addMinutes($timeOffset) ])
                            ->first();

        $reservation->update([ 'pause_start' => Carbon::now() ]);

        $inside = true;
        $status = null;

        return compact(['user', 'reservation', 'inside', 'status']);
    }

    public function enterWithoutReservation(Request $request)
    {
        \JWTAuth::setToken($request->get('access_token'));
        $tokenPayload = \JWTAuth::getPayload();

        $user = User::find($tokenPayload['sub']);
        $until = explode(':', $request->get('until'));
        $seatId = $request->get('seat_id');
        $subject = $request->get('subject');

        $timeStart = Carbon::now();
        $timeStart->second(0);

        $timeEnd = Carbon::now();
        $timeEnd->setTime($until[0], $until[1]);

        if ($timeEnd->lte($timeStart)) {
            return response()->json('Wrong time end', 422);
        }

        $seat = Seat::findOrFail($seatId);
        if ($seat->user_id) {
            return response()->json('Seat already reserved', 422);
        }

        $seat->update([ 'user_id' => $user->id ]);

        $reservation = $user->reservations()->create([
            'seat_id' => $seatId,
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
            'subject' => $subject
        ]);

        $room = $seat->room;
        $room->load('reservations');
        \Websocket::sendToRoom($seat->room_id, Websocket::EVENT_ROOMS_UPDATED, $room);
        $seat->user->increaseExperience('USER_LOGGED_IN');

        return compact(['user', 'reservation']);
    }
}
