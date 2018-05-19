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
    });

    Route::group([], function() {
        Route::resource('rooms', 'RoomController');
        Route::resource('reservations', 'ReservationController');
        Route::post('reservations/get-available-seats', 'ReservationController@getAvailableSeats');
    });
});
