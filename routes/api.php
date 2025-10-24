<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// register route
Route::post('/register', [AuthController::class, 'register'])
     ->middleware(['throttle:5,1', 'verify.frontend']);
//login route
Route::post('/login', [AuthController::class, 'login'])
     ->middleware(['throttle:10,1', 'verify.frontend']);
// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Logout Route
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/chat/ask', [ChatController::class, 'conversation']);
    // An example of route for an authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});


