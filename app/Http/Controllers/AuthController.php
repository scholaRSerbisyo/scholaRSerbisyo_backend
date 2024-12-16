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
use App\Services\CloudflareR2Service;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Support\Str;

class AuthController extends Controller
{

    protected $r2Service;
    protected $sendPushNotification;
    public function __construct(CloudflareR2Service $r2Service) {
        $this->userModel = new User();
        $this->adminModel = new Admin();
        $this->r2Service = $r2Service;
    }

    public function validateScholar(Request $request)
    {
        // Validate the request
        $request->validate([
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'scholar_id' => 'required|string|max:7'
        ]);

        // Check if a scholar exists with the given lastname, firstname, and birthdate
        $scholar = Scholar::where('lastname', $request->lastname)
                          ->where('firstname', $request->firstname)
                          ->where('scholar_id', $request->scholar_id)
                          ->whereNull('user_id')
                          ->first();

        if (!$scholar) {
            return response()->json([
                'message' => 'No unlinked scholar found with the given information.',
                'exists' => false
            ], 404);
        }

        return response()->json([
            'message' => 'Unlinked scholar found.',
            'exists' => true,
            'scholar_id' => $scholar->scholar_id
        ], 200);
    }

    public function registerScholarUser(Request $request)
    {
        // Validate the request
        $request->validate([
            'scholar_id' => 'required|exists:scholars,scholar_id',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            // Find the scholar
            $scholar = Scholar::where('scholar_id', $request->scholar_id)
                              ->whereNull('user_id')
                              ->firstOrFail();

            // Create a new user
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => 2, // Assuming 2 is the role_id for scholars
            ]);

            // Link the new user to the existing scholar
            $scholar->user_id = $user->user_id;
            $scholar->save();

            DB::commit();

            return response()->json([
                'message' => 'Scholar user registered and linked successfully.',
                'user' => $user,
                'scholar' => $scholar
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to register scholar user.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function updateProfilePicture(Request $request, string $id)
    {
        try {
            $scholar = Scholar::findOrFail($id);
            $scholarData = $request->validated();

            if ($scholar->profile_image_uuid) {
                $oldImageUuid = $scholar->profile_image_uuid;
            }

            if ($request->has('image')) {
                // Generate a new UUID for the image
                $newImageUuid = (string) Str::uuid();
                $scholarData['event_image_uuid'] = $newImageUuid;

                // Upload the new image
                $newImageUrl = $this->r2Service->uploadFileToBucket($request->input('image'), $newImageUuid);
                
                if ($newImageUrl) {
                    $scholarData['image'] = $newImageUrl;

                    // Delete the old image if it exists
                    if ($oldImageUuid) {
                        $this->r2Service->deleteFile($oldImageUuid);
                    }
                } else {
                    // If upload fails, don't update the image-related fields
                    unset($scholarData['event_image_uuid']);
                    unset($scholarData['image']);
                }
            }

            $scholar->update($scholarData);

            return response(['message' => 'Event updated successfully!', 'event' => $scholar], 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
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
    

    public function loginAccount(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $scholar = Scholar::where('user_id', $user->user_id)->first();

            if (!$scholar) {
                return response()->json([
                    'message' => 'Scholar not found for this user',
                ], 404);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'scholar_id' => $scholar->scholar_id,
                'role' => $scholar->scholar_type_id?? null,
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }
    
    public function updateScholarInfo(Request $request, $scholar_id)
    {
        try {
            $scholar = Scholar::findOrFail($scholar_id);

            $validatedData = $request->validate([
                'firstname' => 'sometimes|required|string|max:255',
                'lastname' => 'sometimes|required|string|max:255',
                'age' => 'sometimes|required|string',
                'address' => 'sometimes|required|string|max:255',
                'mobilenumber' => 'sometimes|required|string|max:20',
                'yearlevel' => 'sometimes|required|string|max:50',
                'scholar_type_id' => 'sometimes|required|exists:scholar_types,scholar_type_id',
                'school_id' => 'sometimes|required|exists:schools,school_id',
                'baranggay_id' => 'sometimes|required|exists:baranggays,baranggay_id',
            ]);

            $scholar->update($validatedData);

            return response()->json([
                'message' => 'Scholar information updated successfully',
                'scholar' => $scholar
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Scholar not found'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating scholar information', 'error' => $e->getMessage()], 500);
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
        try {
            return response()->json($request->user()->load('admin'), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function showCurrentScholar(Request $request)
    {
        try {
            return response()->json($request->user()->load('scholar'), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }
}
