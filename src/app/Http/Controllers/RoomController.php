<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Services\WebsocketGateway\Websocket;
class RoomController extends Controller
{
    public function index()
    {
        return Room::all();
    }

    public function show(Room $room)
    {
        $room->load(['seats.user', 'reservations']);
        return $room;
    }

    public function store(Request $request)
    {
        $payload = $request->only(
            [
                'name',
                'faculty',
                'rows',
                'columns'
            ]);
        $room = Room::create($payload);
        \Websocket::sendToPublic(Websocket::EVENT_ROOMS_CREATED, $room);
        return $room;
    }

}
