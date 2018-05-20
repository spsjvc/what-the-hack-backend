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

    public static $EXPERIENCE_CHANGE_EVENTS = [
        // increase
        'USER_DID_NOT_SHOW_UP' => 5,
        'USER_DID_NOT_LOGOUT_ON_TIME' => 2,
        'USER_LOGGED_OUT_EARLIER' => 3,
        // decrease
        'USER_LOGGED_IN' => 5,
        'USER_LOGGED_OUT_ON_TIME' => 5,
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
        $this->update(['points' => $this->points + $this->EXPERIENCE_CHANGE_EVENTS[$event]]);
        \Websocket::sendToPublic(Websocket::EVENT_USER_EXPERIENCE_CHANGE, ['user' => $this, 'event' => $event]);
    }

    public function decreaseExperience($event) {
        if (($this->points - $this->EXPERIENCE_CHANGE_EVENTS[$event]) >= 0) {
            $this->update(['points' => $this->points - $this->EXPERIENCE_CHANGE_EVENTS[$event]]);
            \Websocket::sendToPublic(Websocket::EVENT_USER_EXPERIENCE_CHANGE, ['user' => $this, 'event' => $event]);
        }
    }
}
