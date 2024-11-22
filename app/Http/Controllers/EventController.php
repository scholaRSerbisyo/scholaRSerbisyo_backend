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
            $save = Event::create($request->only([
                'event_image_uuid',
                'event_name',
                'description',
                'date',
                'time_from',
                'time_to',
                'location',
                'status',
                'admin',
                'event_type_id',
                'event_type',
            ]));

            // Retrieve the Base64 image string directly
            $imageData = $request->input('image');
            $uuid = $request->input('event_image_uuid');

            if ($save) {
                $this->r2Service->uploadFileToBucket($imageData, $uuid);
    
                return response(['message' => 'event created succesfully'], 201);
            }
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function updateEvent(EventUpdateRequest $request, string $id) {
        try {
            $updatesave = Event::find($id);

            $imageData = $request->input('image');
            $uuid = $request->input('event_image_uuid');

            if ($updatesave) {
                $new = $updatesave->update($request->validated());
                
                if ($new) {
                    $this->r2Service->uploadFileToBucket($imageData, $uuid);

                    return response(['message' => 'Event updated successfully!'], 201);
                }
            } else {
                return response(['messsage' => 'Event not found'], 404);
            }
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getAllEvents() {
        try {
            return response()->json(Event::all(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getCSOEvents() {
        try {
            return response()->json(Event::where('event_type_id', 1)->get(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getSchoolEvents() {
        try {
            return response()->json(Event::where('event_type_id', 2)->get(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getImage(Request $request, CloudflareR2Service $r2Service)
    {
        $imageUuid = $request->input('image_uuid');

        if (empty($imageUuid)) {
            return response()->json(['error' => 'Image UUID is required'], 400);
        }

        // Optional: Add authorization check here
        // if (!auth()->user()->canAccessImage($imageUuid)) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        try {
            // Try to get the URL from cache first
            $url = Cache::remember("image_url_{$imageUuid}", now()->addMinutes(30), function () use ($r2Service, $imageUuid) {
                return $r2Service->getFileUrl($imageUuid);
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
            EventType::create($request->all());
            return response(['message' => 'event type created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()],500);
        }
    }

    public function show() {
        try {
            return EventType::all();
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()],500);
        }
    }
}
