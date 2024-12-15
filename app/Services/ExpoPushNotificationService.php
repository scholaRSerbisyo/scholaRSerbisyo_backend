<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ExpoPushNotificationService
{
    protected $client;
    protected $expoApiUrl = 'https://exp.host/--/api/v2/push/send';

    public function __construct()
    {
        $this->client = new Client();
    }

    public function sendNotification(array $tokens, array $notificationData): array
    {
        $messages = array_map(function ($token) use ($notificationData) {
            return [
                'to' => $token,
                'sound' => 'default',
                'title' => $notificationData['title'],
                'body' => $notificationData['body'],
                'data' => $notificationData['data'],
            ];
        }, $tokens);

        Log::info('Preparing to send push notifications', ['count' => count($messages)]);

        try {
            Log::info('Sending request to Expo API', ['url' => $this->expoApiUrl]);
            
            $response = $this->client->post($this->expoApiUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $messages,
            ]);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            
            Log::info('Received response from Expo API', ['statusCode' => $statusCode, 'body' => $body]);

            $result = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error decoding JSON response', [
                    'error' => json_last_error_msg(),
                    'raw_body' => $body
                ]);
                return [
                    'error' => 'Error decoding JSON response: ' . json_last_error_msg(),
                    'raw_response' => $body
                ];
            }

            Log::info('Push notification sent successfully', ['result' => $result]);
            return $result;
        } catch (GuzzleException $e) {
            Log::error('Error sending push notification', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'Error sending push notification: ' . $e->getMessage()];
        }
    }
}

