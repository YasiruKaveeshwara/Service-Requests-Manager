<?php

declare(strict_types=1);

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\DocsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/docs');
Route::get('/health', HealthController::class);
Route::get('/docs', [DocsController::class, 'ui']);
Route::get('/openapi.yaml', [DocsController::class, 'spec']);
