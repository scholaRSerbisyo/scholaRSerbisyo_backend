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
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct() {
        $this->userModel = new User();
        $this->adminModel = new Admin();
    }

    public function showAdmins() {
        try {
            return Admin::with('user')->get();
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()],500);
        }
    }

    public function showScholars() {
        try {
            return Scholar::with('user', 'school', 'baranggay')->get();
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
            if ($request['role_id'] == 1) {
                return response(['message'=> 'role is not for Scholars'],404);
            }

            Scholar::create($request->all());

            return response(['message' => 'scholar created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message'=> $th->getMessage()], 500);
        }
    }

    public function createAdminAccount(AdminStoreRequest $request) {
        try {
            if ($request['role_id'] == 2) {
                return response(['message'=> 'role is not for Admin'],404);
            }

            Admin::create($request->all());
            return response(['message' => 'admin created successfully'], 201);
        } catch (\Throwable $th) {
            return response(['message'=> $th->getMessage()], 500);
        }
    }

    public function loginAdminAccount(LoginRequest $request) {
        try {
            $credentials = $request->only(['email', 'password']);
            
            $admin = Admin::with('user')->whereHas('user', function ($query) use ($credentials) {
                $query->where('email', $credentials['email']);
            })->first();
        
            if (!$admin) {
                return response()->json(['message' => "Account doesn't exist"], 404);
            }
        
            if ($admin->user->role_id !== 1) {
                return response()->json(['message' => 'Only admins can access this!'], 403);
            }
        
            if (!Hash::check($credentials['password'], $admin->user->password)) {
                return response()->json(['message' => "Incorrect password"], 401);
            }
        
            $token = $admin->user->createToken('Personal Access Token')->plainTextToken;
        
            return response()->json([
                'token' => $token,
                'role' => $admin->admin_type_id
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'An error occurred during login.', 'error' => $th->getMessage()], 500);
        }
    }
    

    public function loginAccount(LoginRequest $request) {
        try {
            $credentials = $request->only(['email', 'password']);
            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                return response(['message' => "Account doesn't exist"], 404);
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                return response(['message' => "Incorrect password"], 401);
            }

            if ($user->role_id === 1) {
                return response(['message' => 'Scholars can only access this!'], 400);
            }

            $token = $user->createToken('Personal Access Token')->plainTextToken;

            return response(['token' => $token, 'role' => $user->role_id], 200);
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

    public function showCurrentAdmin(Request $request)
    {
        return response()->json($request->user()->load('admin'), 200);
    }
}
