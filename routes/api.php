<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaranggayController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScholarTypeController;
use App\Http\Controllers\AdminTypeController;
use App\Http\Controllers\EventTypeController;
use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;


Route::prefix('/user')->group(function () {
    Route::post('/createuser', [AuthController::class, 'createUserAccount']);
    Route::post('/createscholar', [AuthController::class, 'createScholarAccount']);
    Route::post('/createadmin', [AuthController::class, 'createAdminAccount']);
    Route::post('/admin/login', [AuthController::class, 'loginAdminAccount']);
    Route::post('/login', [AuthController::class, 'loginAccount']);
    Route::get('/me', [AuthController::class, 'show'])->middleware(['auth:sanctum']);
    Route::post('/logout', [AuthController::class, 'logoutAccount'])->middleware(['auth:sanctum']);
    Route::get('/users', [AuthController::class,'showAllUsers']);
    Route::get('/admins', [AuthController::class,'showAdmins']);
    Route::get('/scholars', [AuthController::class,'showScholars']);
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

Route::prefix('/admintype')->group(function () {
    Route::post('/create', [AdminTypeController::class, 'createAdminType']);
});

Route::prefix('/events')->group(function () {
    Route::post('/createeventtype', [EventTypeController::class, 'createEventType']);
});