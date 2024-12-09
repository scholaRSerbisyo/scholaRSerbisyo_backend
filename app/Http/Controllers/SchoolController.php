<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolStoreRequest;
use App\Models\School;
use App\Models\Event;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    
    public function createSchool(SchoolStoreRequest $request) {
        try {
            School::create($request->all());
            return response(['message' => 'school created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message'=> $th->getMessage()], 500);
        }
    }

    public function getAllSchools() {
        try {
            $schoolsWithEventCounts = School::withCount(['events' => function ($query) {
                $query->where('event_type_id', 2); // Assuming 2 is the ID for school-based events
            }])
            ->with(['events' => function ($query) {
                $query->where('event_type_id', 2)
                      ->select('event_id', 'school_id', 'event_name', 'date');
            }])
            ->get()
            ->map(function ($school) {
                $school->upcoming_events = $school->events->where('date', '>=', now())->values();
                $school->past_events = $school->events->where('date', '<', now())->values();
                unset($school->events);
                return $school;
            });
    
            return response()->json($schoolsWithEventCounts);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function getSchoolWithEvents($id)
    {
        try {
            $school = School::findOrFail($id);
            
            $events = Event::where('school_id', $id)
                ->select('event_id', 'event_name', 'event_image_uuid', 'date', 'time_from', 'time_to', 'location', 'description', 'status')
                ->get();

            $upcomingEvents = $events->filter(function ($event) {
                return strtotime($event->date) >= strtotime('today');
            })->values();

            $pastEvents = $events->filter(function ($event) {
                return strtotime($event->date) < strtotime('today');
            })->values();

            return response()->json([
                'school' => $school,
                'events' => $events,
                'upcoming_events' => $upcomingEvents,
                'past_events' => $pastEvents
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'School not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the school data'], 500);
        }
    }
}
