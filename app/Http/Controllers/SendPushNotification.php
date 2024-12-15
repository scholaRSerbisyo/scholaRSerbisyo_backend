<?php

namespace App\Http\Controllers;

use App\Services\ExpoPushNotificationService;
use App\Models\Scholar;
use App\Models\Notification;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SendPushNotification extends Controller
{
    protected $pushNotificationService;

    public function __construct(ExpoPushNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }

    public function sendBroadcastNotification(Request $request)
    {
        Log::info('Received broadcast notification request', ['data' => $request->all()]);

        try {
            $validatedData = $request->validate([
                'event_id' => 'required|integer',
                'event_name' => 'required|string',
                'event_type_name' => 'required|string',
                'description' => 'required|string',
                'date' => 'required|date',
                'time_from' => 'required|string',
                'time_to' => 'required|string',
                'event_image_uuid' => 'required|string',
            ]);

            Log::info('Validated notification data', ['data' => $validatedData]);

            // Fetch all scholars' push tokens
            $scholarTokens = Scholar::whereNotNull('push_token')->pluck('push_token')->toArray();

            Log::info('Retrieved scholar tokens', [
                'count' => count($scholarTokens),
                'tokens' => $scholarTokens // Be cautious with logging tokens in production
            ]);

            if (empty($scholarTokens)) {
                Log::warning('No valid push tokens found for scholars');
                return response()->json(['error' => 'No valid push tokens found'], 404);
            }

            $notificationData = [
                'title' => 'New Event: ' . $validatedData['event_name'],
                'body' => $validatedData['description'],
                'data' => $validatedData,
            ];

            $result = $this->pushNotificationService->sendNotification($scholarTokens, $notificationData);

            Log::info('Push notification service result', ['result' => $result]);

            if (isset($result['error'])) {
                Log::error('Error in push notification service', [
                    'error' => $result['error'],
                    'raw_response' => $result['raw_response'] ?? null
                ]);
                throw new \Exception($result['error']);
            }

            // Create a new notification in the database
            $notification = Notification::create([
                'event_id' => $validatedData['event_id'],
                'event_name' => $validatedData['event_name'],
                'event_type_name' => $validatedData['event_type_name'],
                'description' => $validatedData['description'],
                'event_image_uuid' => $validatedData['event_image_uuid'],
            ]);

            Log::info('Broadcast notification sent successfully', ['event_id' => $validatedData['event_id'], 'notification_id' => $notification->notification_id]);
            return response()->json([
                'success' => true, 
                'message' => 'Broadcast notification sent successfully',
                'notification' => $notification
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in sendBroadcastNotification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to send broadcast notification: ' . $e->getMessage(),
                'details' => $e instanceof \JsonException ? 'Invalid JSON response from server' : null
            ], 500);
        }
    }

    public function updatePushToken(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'push_token' => 'required|string',
                'scholar_id' => 'required|integer|exists:scholars,scholar_id',
            ]);

            $scholar = Scholar::findOrFail($validatedData['scholar_id']);
            $scholar->push_token = $validatedData['push_token'];
            $scholar->save();

            Log::info('Push token updated for scholar', [
                'scholar_id' => $scholar->scholar_id,
                'push_token' => substr($validatedData['push_token'], 0, 10) . '...' // Log only part of the token for security
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push token updated successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Scholar not found when updating push token', [
                'scholar_id' => $validatedData['scholar_id'] ?? 'not provided',
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Scholar not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating push token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to update push token: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getNotifications(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            $notifications = Notification::query()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'notifications' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'last_page' => $notifications->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

