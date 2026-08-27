<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('api.login');

Route::middleware('auth:sanctum')->prefix('v1')->name('api.')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('api.logout');

    Route::get('/user', [AuthController::class, 'user'])
        ->name('api.user');

    Route::apiResource('projects', ProjectController::class);

    Route::apiResource('projects.tasks', TaskController::class)->except(['edit', 'update', 'destroy']);

    Route::apiResource('tasks', TaskController::class)->only(['show', 'update', 'destroy']);
});
