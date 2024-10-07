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
}
