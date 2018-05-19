<?php

namespace App\Services\WebsocketGateway;

use Illuminate\Support\ServiceProvider;

class WebsocketServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->bind('websocket', function() {
            return new Websocket();
        });
    }
}
