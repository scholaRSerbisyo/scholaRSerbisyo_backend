<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventValidationStoreRequest;
use App\Models\EventValidation;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\CloudflareR2Service;

class EventValidationController extends Controller
{
    protected $r2Service;

    public function __construct(CloudflareR2Service $r2Service) {
        $this->r2Service = $r2Service;
    }

    public function createEventValidation(EventValidationStoreRequest $request) {
        try {
            $eventData = $request->validated();
            // Set initial status as pending
            $eventData['status'] = 'pending';

            $event = EventValidation::create($eventData);

            if ($event) {
                $this->r2Service->uploadFileToBucket($request->input('image'), $request->input('event_image_uuid'));

                Log::info('Event validation created successfully', ['event_validation_id' => $event->event_validation_id]);
                return response()->json([
                    'message' => 'Event submitted for validation successfully',
                    'event' => $event
                ], 201);
            }

            throw new \Exception('Failed to create event validation');
        } catch (\Throwable $th) {
            Log::error('Error creating event validation', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to create event validation',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function acceptEvent($eventId) {
        try {
            DB::beginTransaction();

            // Only load necessary relationships and fields
            $eventValidation = EventValidation::findOrFail($eventId);

            // Check if event is already processed
            if ($eventValidation->status !== 'pending') {
                return response()->json([
                    'message' => 'Event has already been ' . $eventValidation->status
                ], 422);
            }

            $event = Event::create([
                'event_image_uuid' => $eventValidation->event_image_uuid,
                'event_name' => $eventValidation->event_name,
                'description' => $eventValidation->description,
                'date' => $eventValidation->date,
                'time_from' => $eventValidation->time_from,
                'time_to' => $eventValidation->time_to,
                'location' => $eventValidation->location,
                'status' => 'ongoing',
                'event_type_id' => $eventValidation->event_type_id,
                'school_id' => $eventValidation->school_id,
                'baranggay_id' => $eventValidation->baranggay_id
            ]);

            if (!$event) {
                throw new \Exception('Failed to create Event');
            }

            $eventValidation->status = 'accepted';
            $eventValidation->save();

            $formattedEventValidation = [
                'id' => $event->event_id,
                'admin_type_name' => $eventValidation->admin_type_name,
                'event_name' => $eventValidation->event_name,
                'description' => $eventValidation->description,
                'date' => $eventValidation->formatted_date,
                'time_from' => $eventValidation->formatted_time_from,
                'time_to' => $eventValidation->formatted_time_to,
                'location' => $eventValidation->location,
                'status' => $eventValidation->status
            ];

            DB::commit();

            Log::info('Event accepted successfully', [
                'event_validation_id' => $eventId,
                'event_id' => $event->event_id
            ]);

            return response()->json([
                'message' => 'Event accepted successfully',
                'event' => $formattedEventValidation
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error accepting event', [
                'event_validation_id' => $eventId,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to accept event',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function declineEvent($eventId) {
        try {
            DB::beginTransaction();

            $eventValidation = EventValidation::findOrFail($eventId);

            // Check if event is already processed
            if ($eventValidation->status !== 'pending') {
                return response()->json([
                    'message' => 'Event has already been ' . $eventValidation->status
                ], 422);
            }

            $eventValidation->status = 'declined';
            $eventValidation->save();

            $formattedEventValidation = [
                'id' => $eventValidation->event_validation_id,
                'admin_type_name' => $eventValidation->admin_type_name,
                'event_name' => $eventValidation->event_name,
                'description' => $eventValidation->description,
                'date' => $eventValidation->formatted_date,
                'time_from' => $eventValidation->formatted_time_from,
                'time_to' => $eventValidation->formatted_time_to,
                'location' => $eventValidation->location,
                'status' => $eventValidation->status
            ];

            DB::commit();

            Log::info('Event declined successfully', ['event_validation_id' => $eventId]);

            return response()->json([
                'message' => 'Event declined successfully',
                'event' => $formattedEventValidation
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error declining event', [
                'event_validation_id' => $eventId,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to decline event',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getAllEventValidations()
    {
        try {
            $eventValidations = EventValidation::with(['eventType'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($eventValidation) {
                    return [
                        'event_validation_id' => $eventValidation->event_validation_id,
                        'admin_id' => $eventValidation->admin_id,
                        'admin_type_name' => $eventValidation->admin_type_name,
                        'event_image_uuid' => $eventValidation->event_image_uuid,
                        'event_name' => $eventValidation->event_name,
                        'description' => $eventValidation->description,
                        'date' => $eventValidation->formatted_date,
                        'time_from' => $eventValidation->formatted_time_from,
                        'time_to' => $eventValidation->formatted_time_to,
                        'location' => $eventValidation->location,
                        'status' => $eventValidation->status,
                        'event_type' => [
                            'name' => $eventValidation->eventType->name
                        ],
                        'school_id' => $eventValidation->school_id,
                        'baranggay_id' => $eventValidation->baranggay_id,
                    ];
                });

            Log::info('Event validations fetched successfully', [
                'count' => $eventValidations->count()
            ]);

            return response()->json($eventValidations);

        } catch (\Throwable $th) {
            Log::error('Error fetching event validations', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch event validations',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
