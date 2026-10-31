<?php

namespace App\Services;

use App\User;
use Illuminate\Notifications\Notification;

/**
 * Class FcmChannel.
 */
class FcmChannel
{
    /**
     * @var FirebasePushService
     */
    private $firebase;

    /**
     * @param  FirebasePushService  $firebase
     */
    public function __construct(FirebasePushService $firebase)
    {
        $this->firebase = $firebase;
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

        // L'API v1 n'accepte qu'un destinataire par requête.
        $tokens = is_array($message->getTo()) ? $message->getTo() : [$message->getTo()];

        foreach ($tokens as $token) {
            $response = $this->post($message, $token);
            if ($response !== null) {
                array_push($response_array, $response);
            }
        }

        return $response_array;
    }

    /**
     * A push failure (unregistered device, network...) must not break the action
     * that triggered the notification (e.g. sending a chat message): log it and go on.
     *
     * @param  FcmMessage  $message
     * @param  string|null  $token
     * @return array|null
     */
    private function post($message, $token)
    {
        try {
            $result = $this->firebase->send($message->toV1($token), (bool) $message->isDryRun());

            if ($this->isUnregistered($result)) {
                User::where('device_token', $token)->update(['device_token' => null]);
            }

            return $result;
        } catch (\Throwable $e) {
            \Log::warning('FCM push failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Firebase signale un jeton mort (application désinstallée, jeton périmé) : l'application en renverra un
     * nouveau via set-fcm-token, inutile de le réessayer d'ici là.
     *
     * @param  array  $result
     * @return bool
     */
    private function isUnregistered(array $result)
    {
        return in_array('UNREGISTERED', data_get($result, 'response.error.details.*.errorCode', []) ?: []);
    }
}
