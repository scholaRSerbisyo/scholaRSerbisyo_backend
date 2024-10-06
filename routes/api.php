<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('/user')->group(function () {
    Route::post('/createuser', [AuthController::class, 'createAccount']);
    Route::post('/login', [AuthController::class, 'loginAccount']);
});

Route::prefix('/role')->group(function () {
    Route::post('/createrole', [RoleController::class, 'createRole']);
});