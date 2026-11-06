<?php

namespace App\Http\Controllers\api\v2\PushNotification;

use App\ApiBaseMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PushNotificationController extends Controller
{
    public function setFcmToken(Request $request)
    {
        // Toujours le compte connecté : l'id reçu permettrait de capter les notifications d'un autre utilisateur.
        $request->user()->assignDeviceToken($request->token);
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = '';
            return ApiBaseMethod::sendResponse($data, 'Token Updated');
        }
    }
}
