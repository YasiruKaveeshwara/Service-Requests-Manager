<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ServiceRequestController;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('requests', [ServiceRequestController::class, 'index']);
    Route::post('requests', [ServiceRequestController::class, 'store']);
    Route::get('requests/{serviceRequest}', [ServiceRequestController::class, 'show']);
    Route::put('requests/{serviceRequest}', [ServiceRequestController::class, 'update']);
});
