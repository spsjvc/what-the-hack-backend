<?php

namespace App\Http\Controllers;

use App\Services\WebsocketGateway\Websocket;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    public function getUserByToken(Request $request)
    {
        \JWTAuth::setToken($request->get('access_token'));
        $tokenPayload = \JWTAuth::getPayload();

        $user = User::find($tokenPayload['sub']);
        $timeOffset = config('reservation.time_offset');

        $reservation = $user->reservation()
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
                $reservation->delete();
                \Websocket::sendToRoom($seat->room_id, Websocket::EVENT_ROOMS_UPDATED, $seat->room);
                return compact(['user', 'reservation']);
            }
        }

        $reservation = null;
        return compact(['user', 'reservation']);
    }
}
