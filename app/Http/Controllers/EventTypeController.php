<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventTypeStoreRequest;
use App\Models\EventType;
use Illuminate\Http\Request;

class EventTypeController extends Controller
{
    public function createEventType(EventTypeStoreRequest $request) {
        try {
            EventType::create($request->all());
            return response(['message' => 'event type created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()],500);
        }
    }
}
