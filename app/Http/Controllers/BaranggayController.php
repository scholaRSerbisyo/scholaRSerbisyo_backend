<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaranggayStoreRequest;
use App\Models\Baranggay;
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
}
