<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Room;

class Seat extends Model
{
    protected $fillable = [ 'number', 'room_id', 'is_taken' ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
