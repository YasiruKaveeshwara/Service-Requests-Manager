<?php

declare(strict_types=1);

use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/docs');
Route::get('/health', HealthController::class);
