<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seat;

class Room extends Model
{
    protected $fillable = [ 'name', 'faculty' ];

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }
}
