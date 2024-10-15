<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaranggayController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScholarTypeController;
use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;


Route::prefix('/user')->group(function () {
    Route::post('/createuser', [AuthController::class, 'createUserAccount']);
    Route::post('/createscholar', [AuthController::class, 'createScholarAccount']);
    Route::post('/login', [AuthController::class, 'loginAccount']);
    Route::post('/users', [AuthController::class,'showAllUsers'])->middleware(['auth:sanctum']);
    Route::post('/admins', [AuthController::class,'showAdmins'])->middleware(['auth:sanctum']);
    Route::post('/scholars', [AuthController::class,'showScholars'])->middleware(['auth:sanctum']);
    Route::post('/scholarsbyschool', [AuthController::class,'showScholarsBySchool'])->middleware(['auth:sanctum']);
});

Route::prefix('/role')->group(function () {
    Route::post('/createrole', [RoleController::class, 'createRole']);
});

Route::prefix('/school')->group(function () {
    Route::post('/create', [SchoolController::class, 'createSchool']);
});

Route::prefix('/baranggay')->group(function () {
    Route::post('/create', [BaranggayController::class, 'createBaranggay']);
});

Route::prefix('/scholartype')->group(function () {
    Route::post('/create', [ScholarTypeController::class, 'createScholarType']);
});