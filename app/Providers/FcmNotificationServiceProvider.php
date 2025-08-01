<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use App\Services\FcmChannel;

/**
 * Class FcmNotificationServiceProvider.
 */
class FcmNotificationServiceProvider extends ServiceProvider
{
    /**
     * Register.
     */
    public function register()
    {
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('fcm', function () {
                return new FcmChannel(app(Client::class), config('services.fcm.key'));
            });
        });
    }
}
