<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

class RoomController extends Controller
{
    public function index()
    {
        return Room::all();
    }

    public function show(Room $room)
    {
        $room->load('seats.user');
        return $room;
    }

}
