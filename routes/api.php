<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');;
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');;
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:30,1');

Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->middleware('can:manage-users');
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('tasks/stats', [TaskController::class, 'stats']);
    Route::post('tasks/bulk', [TaskController::class, 'bulkStore']);
    Route::apiResource('tasks', TaskController::class);
});
