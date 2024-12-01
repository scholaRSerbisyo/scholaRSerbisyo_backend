<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventTypeStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\EventType;
use App\Services\CloudflareR2Service;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    private $r2Service;

    public function __construct(CloudflareR2Service $r2Service)
    {
        $this->r2Service = $r2Service;
    }

    public function createEvent(EventStoreRequest $request) {
        try {
            $eventData = $request->validated();
            
            // Check if an event already exists on the given date
            $existingEvent = Event::whereDate('date', $eventData['date'])->first();
            
            if ($existingEvent) {
                return response([
                    'message' => 'There is an Event that already assigned to this date',
                    'existing_event' => $existingEvent
                ], 422);
            }
    
            $eventData['status'] = $this->determineEventStatus($eventData['date']);
            $event = Event::create($eventData);
    
            if ($event) {
                $this->r2Service->uploadFileToBucket($request->input('image'), $eventData['event_image_uuid']);
                return response(['message' => 'Event created successfully', 'event' => $event], 201);
            }
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function updateEvent(EventUpdateRequest $request, string $id) {
        try {
            $event = Event::findOrFail($id);
            $eventData = $request->validated();
            $eventData['status'] = $this->determineEventStatus($eventData['date']);

            $event->update($eventData);
            
            if ($request->has('image')) {
                $this->r2Service->uploadFileToBucket($request->input('image'), $eventData['event_image_uuid']);
            }

            return response(['message' => 'Event updated successfully!', 'event' => $event], 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getAllEvents() {
        try {
            $events = Event::whereHas('eventType', function ($query) {
                $query->whereIn('name', ['CSO', 'School', 'Community']);
            })->with(['eventType', 'school', 'barangay'])->get();

            $formattedEvents = $events->map(function ($event) {
                return [
                    'event_id' => $event->event_id,
                    'event_image_uuid' => $event->event_image_uuid,
                    'event_name' => $event->event_name,
                    'description' => $event->description,
                    'date' => $event->formatted_date,
                    'time_from' => $event->formatted_time_from,
                    'time_to' => $event->formatted_time_to,
                    'location' => $event->location,
                    'status' => $event->status,
                    'admin_id' => $event->admin_id,
                    'event_type' => $event->eventType,
                    'school' => $event->school,
                    'barangay' => $event->barangay,
                    'created_at' => $event->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $event->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json($formattedEvents, 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getCSOEvents()
    {
        try {
            return response()->json(Event::whereHas('eventType', function ($query) {
                $query->where('name', 'CSO');
            })->get(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getSchoolEvents() {
        try {
            return response()->json(Event::where('event_type_id', 2)->with('school')->get(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getBarangayEvents() {
        try {
            return response()->json(Event::where('event_type_id', 3)->with('barangay')->get(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getImage(Request $request)
    {
        $imageUuid = $request->input('image_uuid');

        if (empty($imageUuid)) {
            return response()->json(['error' => 'Image UUID is required'], 400);
        }

        try {
            $url = Cache::remember("image_url_{$imageUuid}", now()->addMinutes(30), function () use ($imageUuid) {
                return $this->r2Service->getFileUrl($imageUuid);
            });

            if ($url === null) {
                return response()->json(['error' => 'Unable to generate image URL'], 500);
            }

            return response()->json(['url' => $url], 200);
        } catch (\Throwable $th) {
            Log::error('Error generating image URL: ' . $th->getMessage());
            return response()->json(['error' => 'Unable to generate image URL'], 500);
        }
    }

    public function createEventType(EventTypeStoreRequest $request) {
        try {
            $eventType = EventType::create($request->validated());
            return response(['message' => 'Event type created successfully', 'eventType' => $eventType], 201);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getEventTypes() {
        try {
            return response()->json(EventType::all(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    private function determineEventStatus($eventDate) {
        $now = now();
        $eventDate = \Carbon\Carbon::parse($eventDate);

        if ($eventDate->isToday()) {
            return 'ongoing';
        } elseif ($eventDate->isFuture()) {
            return 'upcoming';
        } else {
            return 'completed';
        }
    }
}