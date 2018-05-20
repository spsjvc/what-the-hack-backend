<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('register', 'Auth\AuthController@register');
        Route::post('login', 'Auth\AuthController@login');
        Route::post('logout', 'Auth\AuthController@logout');
        Route::post('me', 'Auth\AuthController@me');
        Route::get('me', 'Auth\AuthController@me');
    });

    Route::group([], function() {
        Route::get('reservations/future-reservations', 'ReservationController@currentUserFutureReservations');
        Route::resource('rooms', 'RoomController');
        Route::resource('reservations', 'ReservationController');
        Route::post('reservations/get-available-seats', 'ReservationController@getAvailableSeats');
        Route::post('reservations/get-taken-seats', 'ReservationController@getTakenSeats');
        Route::post('users/get-user-by-token', 'UserController@getUserByToken');
        Route::post('users/enter-without-reservation', 'UserController@enterWithoutReservation');
        Route::post('users/exit-from-room', 'UserController@exitFromRoom');
        Route::post('users/pause', 'UserController@pause');
    });
});
