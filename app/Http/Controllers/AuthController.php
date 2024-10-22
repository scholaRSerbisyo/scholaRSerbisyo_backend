<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminStoreRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ScholarsBySchoolRequest;
use App\Http\Requests\ScholarStoreRequest;
use App\Http\Requests\UserStoreRequest;
use App\Models\Admin;
use App\Models\Scholar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct() {
        $this->model = new User();
    }

    public function showAdmins() {
        try {
            return User::where('role_id', 1)->with('admins')->get();
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()],500);
        }
    }

    public function showScholars() {
        try {
            return User::where('role_id',2)->with('scholars')->get();
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()],500);
        }
    }

    public function showScholarsBySchool(ScholarsBySchoolRequest $request) {
        try {
            return Scholar::where('school_id', $request->all())->get();
        } catch (\Throwable $th) {
            return response(['message'=> $th->getMessage()],500);
        }
    }

    public function showAllUsers() {
        try {
            return User::all();
        } catch (\Throwable $th) {
            return response(['message'=> $th->getMessage()],500);
        }
    }

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

    public function createAdminAccount(AdminStoreRequest $request) {
        try {
            Admin::create($request->all());
            return response(['message' => 'admin created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message'=> $th->getMessage()], 500);
        }
    }

    public function loginAdminAccount(LoginRequest $request) {
        try {
            $credentials = $request->only(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return response(['message' => "account doesn't exist"], 404);
            }

            $user = $request->user();

            if ($user["role_id"] === 2) {
                return response(['message' => 'admins can only access this!'], 400);
            }

            $token = $request->user()->createToken('Personal Access Token')->plainTextToken;

            return response(['token' => $token, 'role' => $request->user()->role_id], 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function loginAccount(LoginRequest $request) {
        try {
            $credentials = $request->only(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return response(['message' => "account doesn't exist"], 404);
            }

            $user = $request->user();

            if ($user["role_id"] === 1) {
                return response(['message' => 'scholars can only access this!'], 400);
            }

            $token = $request->user()->createToken('Personal Access Token')->plainTextToken;

            return response(['token' => $token, 'role' => $request->user()->role_id], 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function logoutAccount(Request $request) {
        try {
            $request->user()->currentAccessToken()->delete();

            return response(['message' => 'Successfully logged out'], 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function show(Request $request)
    {
        return response()->json($request->user()->load('admin'), 200);
    }
}
