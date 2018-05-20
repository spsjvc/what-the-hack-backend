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

        if(isset($reservation)) {
            $seat = $reservation->seat;
            if (!isset($reservation->seat->user_id)) {
                $seat->update(['user_id' => $user->id]);
                \Websocket::sendToRoom($seat->room_id, Websocket::EVENT_ROOMS_UPDATED, $seat->room);
                return compact(['user', 'reservation']);
            }

            if ($reservation->seat->user_id == $user->id) {
                $seat->update(['user_id' => null]);
                Reservation::where('id', $reservation->id)
                           ->delete();
                \Websocket::sendToRoom($seat->room_id, Websocket::EVENT_ROOMS_UPDATED, $seat->room);
                return compact(['user', 'reservation']);
            }
        }

        $reservation = null;
        return compact(['user', 'reservation']);
    }

    public function enterWithoutReservation(Request $request)
    {
        \JWTAuth::setToken($request->get('access_token'));
        $tokenPayload = \JWTAuth::getPayload();

        $user = User::find($tokenPayload['sub']);
        $until = explode(':', $request->get('until'));
        $seatId = $request->get('seat_id');

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
            'time_end' => $timeEnd
        ]);

        \Websocket::sendToRoom($seat->room_id, Websocket::EVENT_ROOMS_UPDATED, $seat->room);

        return compact(['user', 'reservation']);
    }
}
