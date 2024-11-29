<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaranggayStoreRequest;
use App\Models\Baranggay;
use App\Models\Event;
use Illuminate\Http\Request;

class BaranggayController extends Controller
{
    public function createBaranggay(BaranggayStoreRequest $request) {
        try {
            Baranggay::create($request->all());
            return response(['message' => 'baranggay created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()],500);
        }
    }

    public function getAllBaranggays() {
        try {
            $baranggaysWithEventCounts = Baranggay::withCount(['events' => function ($query) {
                $query->where('event_type_id', 3); // Assuming 3 is the ID for baranggay-based events
            }])
            ->with(['events' => function ($query) {
                $query->where('event_type_id', 3)
                      ->select('event_id', 'baranggay_id', 'event_name', 'date');
            }])
            ->get()
            ->map(function ($baranggay) {
                $baranggay->upcoming_events = $baranggay->events->where('date', '>=', now())->values();
                $baranggay->past_events = $baranggay->events->where('date', '<', now())->values();
                unset($baranggay->events);
                return $baranggay;
            });

            return response()->json($baranggaysWithEventCounts);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function getBaranggayWithEvents($id)
    {
        try {
            $baranggay = Baranggay::findOrFail($id);
            
            $events = Event::where('baranggay_id', $id)
                ->select('event_id', 'event_name', 'event_image_uuid', 'date', 'time_from', 'time_to', 'location', 'description', 'status')
                ->get();

            $upcomingEvents = $events->filter(function ($event) {
                return strtotime($event->date) >= strtotime('today');
            })->values();

            $pastEvents = $events->filter(function ($event) {
                return strtotime($event->date) < strtotime('today');
            })->values();

            return response()->json([
                'baranggay' => $baranggay,
                'events' => $events,
                'upcoming_events' => $upcomingEvents,
                'past_events' => $pastEvents
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Barangay not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the barangay data'], 500);
        }
    }
}
