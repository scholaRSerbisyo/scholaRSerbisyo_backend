<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminTypeStoreRequest;
use App\Models\AdminType;
use Illuminate\Http\Request;

class AdminTypeController extends Controller
{
    public function createAdminType(AdminTypeStoreRequest $request) {
        try {
            AdminType::create($request->all());
            return response(['message' => 'admin type created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()],500);
        }
    }
}
