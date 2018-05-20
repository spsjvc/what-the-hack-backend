<?php

namespace App\Services\WebsocketGateway;

use Auth;
use Deepstreamhub\DeepstreamClient;
use Carbon\Carbon;

class Websocket {

    protected $client;

    const EVENT_ROOMS_UPDATED = 'socket.room.updated';
    const EVENT_ROOMS_CREATED = 'socket.room.created';

    public static $availableEvents = [
        self::EVENT_ROOMS_UPDATED,
        self::EVENT_ROOMS_CREATED
    ];

    public function __construct() {
        $this->client = new DeepstreamClient($this->getWebsocketUrl(), []);

    }

    public function getWebsocketUrl() {
        return config('websocket.url');
    }

    protected function send($channel, $event, $payload, $skipSender = true) {
        if (!in_array($event, self::$availableEvents)) {
            throw new \InvalidArgumentException("Invalid event name: $event");
        }


        $sentBy = !$skipSender && Auth::check() ? Auth::user() : null;
        $eventData =[
            'sent_by' => $sentBy,
            'sent_at' => Carbon::now()->toISO8601String(),
            'event' => $event,
            'data' => $payload
        ];

        return $this->client->emitEvent($channel, $eventData);
    }

    public function sendToRoom($roomId, $event, $payload = [], $skipSender = true) {
        if (!is_array($roomId)) {
            return $this->send("/rooms/$roomId", $event, $payload, $skipSender);
        }

        foreach ($id as $roomId) {
            $this->send("/rooms/$id", $event, $payload, $skipSender);
        }
    }

    public function sendToPublic($event, $payload = [], $skipSender = true) {
        $this->send("/public", $event, $payload, $skipSender);
    }
}
