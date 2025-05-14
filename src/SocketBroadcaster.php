<?php

namespace SocketIO;
use Log;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;

class SocketBroadcaster extends Broadcaster
{
    public function __construct()
    {
        Log::info('[SocketIO] Custom broadcaster constructed.');
    }

    public function auth($request)
    {
        return true;
    }

    public function validAuthenticationResponse($request, $result)
    {
        // Not used
    }

    public function broadcast(array $channels, $event, array $payload = [])
    {
        try {
            $channelName = $channels[0] instanceof \Illuminate\Broadcasting\Channel
                ? $channels[0]->name
                : $channels[0];

            $key = 'socket.io#/amt#' . $channelName;

            Log::info("[SocketIO] Key: {$key}");
            Log::info("[SocketIO] Event: {$event}");
            Log::info("[SocketIO] Payload: " . json_encode($payload));

            $emitter = new Emitter([
                'host' => env('SOCKET_REDIS_HOST'),
                'port' => env('SOCKET_REDIS_PORT'),
                'password' => env('SOCKET_REDIS_PASSWORD'),
                'key' => $key,
            ]);

            $emitter->of('/amt')->to($channelName)->emit($event, $payload);

            Log::info('[SocketIO] Successfully emitted event.');
        } catch (\Exception $e) {
            Log::error('[SocketIO] Error: ' . $e->getMessage());
        }
    }
}
