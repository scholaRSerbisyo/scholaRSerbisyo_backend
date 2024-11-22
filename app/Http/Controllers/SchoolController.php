<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolStoreRequest;
use App\Models\School;
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
            $schools = School::withCount('events')->get();

            return response()->json($schools);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }
}
