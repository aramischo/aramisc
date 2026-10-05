<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Notifications\Notification;

/**
 * Class FcmChannel.
 */
class FcmChannel
{
    /**
     * @const The API URL for Firebase
     */
    const API_URI = 'https://fcm.googleapis.com/fcm/send';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param  Client  $client
     */
    public function __construct(Client $client, $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    /**
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        /** @var FcmMessage $message */
        $message = $notification->toFcm($notifiable);

        if (is_null($message->getTo()) && is_null($message->getCondition())) {
            if (! $to = $notifiable->routeNotificationFor('fcm', $notification)) {
                return;
            }

            $message->to($to);
        }

        $response_array = [];

        if (is_array($message->getTo())) {
            $chunks = array_chunk($message->getTo(), 1000);

            foreach ($chunks as $chunk) {
                $message->to($chunk);

                $response = $this->post($message);
                if ($response !== null) {
                    array_push($response_array, $response);
                }
            }
        } else {
            $response = $this->post($message);
            if ($response !== null) {
                array_push($response_array, $response);
            }
        }

        return $response_array;
    }

    /**
     * A push failure (legacy FCM endpoint shut down, unregistered device, network...) must not
     * break the action that triggered the notification (e.g. sending a chat message): log it and go on.
     *
     * @param  FcmMessage  $message
     * @return array|null
     */
    private function post($message)
    {
        try {
            $response = $this->client->post(self::API_URI, [
                'headers' => [
                    'Authorization' => 'key='.$this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'body' => $message->formatData(),
            ]);

            return \GuzzleHttp\json_decode($response->getBody(), true);
        } catch (\Throwable $e) {
            \Log::warning('FCM push failed: '.$e->getMessage());

            return null;
        }
    }
}
