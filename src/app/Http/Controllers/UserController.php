<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    public function getUserByToken(Request $request)
    {
        \JWTAuth::setToken($request->get('access_token'));
        $tokenPayload = \JWTAuth::getPayload();

        $user = User::find($tokenPayload['user']->id);

        $reservation = $user->reservation()->whereBetween('time_start', [ Carbon::now()->subMinutes(30), Carbon::now()->addMinutes(30) ])->first();

        return compact(['user', 'reservation']);
    }
}
