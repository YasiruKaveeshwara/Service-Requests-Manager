<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\ServiceRequestWorkflowController;
use App\Http\Controllers\Api\UserController;
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
    Route::prefix('requests/{serviceRequest}')->controller(ServiceRequestWorkflowController::class)->group(function (): void {
        Route::post('submit', 'submit');
        Route::post('cancel', 'cancel');
        Route::post('decision', 'decision');
        Route::post('assign', 'assign');
        Route::post('status', 'status');
    });
    Route::post('requests/{serviceRequest}/comments', [CommentController::class, 'store']);
    Route::get('requests/{serviceRequest}/activity', [ActivityLogController::class, 'index']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'read']);
});
