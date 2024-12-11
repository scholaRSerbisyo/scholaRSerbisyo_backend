<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SendPushNotification extends Controller
{
    public function sendPushNotification(Request $request)
{
    $expoPushToken = $request->input('expoPushToken');
    $title = $request->input('title');
    $body = $request->input('body');

    $response = Http::post('https://exp.host/--/api/v2/push/send', [
        'to' => $expoPushToken,
        'title' => $title,
        'body' => $body,
        'sound' => 'default',
    ]);

    return response()->json([
        'success' => $response->successful(),
        'message' => $response->json(),
    ]);
}
}
