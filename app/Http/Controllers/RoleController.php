<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleStoreRequest;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function createRole(RoleStoreRequest $request) {
        try {
            Role::create($request->all());
            return response(['message' => 'role created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message'=> $th->getMessage()],500);
        }
    }
}
