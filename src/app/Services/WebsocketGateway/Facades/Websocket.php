<?php

namespace App\Services\WebsocketGateway\Facades;

use Illuminate\Support\Facades\Facade;

class Websocket extends Facade {

    protected static function getFacadeAccessor() {
        return 'websocket';
    }
}
