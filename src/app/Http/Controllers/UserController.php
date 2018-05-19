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

        $reservation = $user->reservation()
                            ->whereBetween('time_start', [ Carbon::now()->subMinutes(30), Carbon::now()->addMinutes(30) ])
                            ->first();

        $seat = $reservation->seat;
        if(isset($reservation) && !isset($seat->user_id)) {
            $seat->update(['user_id' => $user->id]);
            \Websocket::sendToRoom($seat->room_id, Websocket::EVENT_ROOMS_UPDATED, $seat->room);
            return compact(['user', 'reservation']);
        }

        return compact(['user', 'reservation']);
    }
}
