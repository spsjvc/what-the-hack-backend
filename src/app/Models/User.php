<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Seat;
use App\Models\Reservation;
use App\Services\WebsocketGateway\Websocket;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    const USER_MISSED_RESERVATION = 5;

    public static $EXPERIENCE_CHANGE_EVENTS = [
        self::USER_MISSED_RESERVATION
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'points'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function seat()
    {
        return $this->hasOne(Seat::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function increaseExperience($event) {
        if (!in_array($event, self::$EXPERIENCE_CHANGE_EVENTS)) {
            throw new \InvalidArgumentException("Invalid experience event name: $event");
        }

        $this->update(['points' => $this->points + $event]);
        \Websocket::sendToPublic(Websocket::EVENT_USER_EXPERIENCE_CHANGE, $this);
    }

    public function decreaseExperience($event) {
        if (!in_array($event, self::$EXPERIENCE_CHANGE_EVENTS)) {
            throw new \InvalidArgumentException("Invalid experience event name: $event");
        }

        if (($this->points - $event) >= 0) {
            $this->update(['points' => $this->points - $event]);
            \Websocket::sendToPublic(Websocket::EVENT_USER_EXPERIENCE_CHANGE, $this);
        }
    }
}
