<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScholarTypeStoreRequest;
use App\Models\ScholarType;
use Illuminate\Http\Request;

class ScholarTypeController extends Controller
{
    public function createScholarType(ScholarTypeStoreRequest $request) {
        try {
            ScholarType::create($request->all());
            return response(['message' => 'scholar type created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()],500);
        }
    }
}
