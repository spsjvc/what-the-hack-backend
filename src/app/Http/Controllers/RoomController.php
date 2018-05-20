<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Seat;
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

        $numOfSeats = $room->rows * $room->columns;
        for ($i = 1; $i <= $numOfSeats; $i++) {
            Seat::create([
                'number' => $i,
                'room_id' => $room->id
            ]);
        }
        \Websocket::sendToPublic(Websocket::EVENT_ROOMS_CREATED, $room);
        return $room;
    }

}
