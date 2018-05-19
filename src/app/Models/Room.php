<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seat;
use App\Models\Reservation;

class Room extends Model
{
    protected $fillable = [ 'name', 'faculty', 'rows', 'columns' ];

    protected $appends = [ 'layout' ];

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function reservations()
    {
        return $this->hasManyThrough(Reservation::class, Seat::class);
    }

    public function getLayoutAttribute()
    {
        $seats = $this->seats->map(function ($item, $key) {
            return $item->user ? 1 : 0;
        });

        $layout = array_chunk($seats->toArray(), $this->rows);

        return $layout;
    }
}
