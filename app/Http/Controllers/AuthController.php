<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ScholarStoreRequest;
use App\Http\Requests\UserStoreRequest;
use App\Models\Scholar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function createUserAccount(UserStoreRequest $request) {
        try {
            User::create($request->all());
            return response(['message' => 'user created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message'=> $th->getMessage()], 500);
        }
    }

    public function createScholarAccount(ScholarStoreRequest $request) {
        try {
            Scholar::create($request->all());
            return response(['message' => 'scholar created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message'=> $th->getMessage()], 500);
        }
    }

    public function loginAccount(LoginRequest $request) {
        try {
            $credentials = $request->only(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return response(['message' => "account doesn't exist"], 404);
            }

            $user = $request->user();
            $token = $request->user()->createToken('Personal Access Token')->plainTextToken;

            return response(['token' => $token, 'role' => $request->user()->role_id], 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }
}
