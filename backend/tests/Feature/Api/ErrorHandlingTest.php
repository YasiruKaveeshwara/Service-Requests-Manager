<?php

declare(strict_types=1);

use App\Exceptions\WorkflowException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    Route::middleware('api')->prefix('api/_test')->group(function (): void {
        Route::get('auth', fn () => 'ok')->middleware('auth:sanctum');
        Route::get('validation', function (): never {
            throw ValidationException::withMessages(['title' => ['Required']]);
        });
        Route::get('workflow', function (): never {
            throw WorkflowException::because('Nope');
        });
        Route::get('server', function (): never {
            throw new RuntimeException('boom');
        });
        Route::get('authorization', function (): never {
            throw new AuthorizationException;
        });
    });
});

it('returns a healthy API response', function () {
    $this->get('/api/health')->assertOk()->assertExactJson(['data' => ['status' => 'ok']]);
});

it('returns a consistent not found error', function () {
    $response = $this->getJson('/api/nope');

    $response->assertNotFound()->assertJson([
        'code' => 'not_found',
    ])->assertJsonStructure(['message', 'code', 'errors']);
});

it('returns a method not allowed error', function () {
    $this->postJson('/api/health')->assertStatus(405)->assertJson([
        'code' => 'method_not_allowed',
    ]);
});

it('returns an unauthenticated JSON error without an accept header', function () {
    $this->get('/api/_test/auth')->assertUnauthorized()->assertJson([
        'code' => 'unauthenticated',
    ]);
});

it('returns validation errors', function () {
    $this->getJson('/api/_test/validation')->assertStatus(422)->assertJson([
        'code' => 'validation_failed',
        'errors' => ['title' => ['Required']],
    ]);
});

it('returns workflow errors', function () {
    $this->getJson('/api/_test/workflow')->assertStatus(422)->assertJson([
        'code' => 'invalid_transition',
        'message' => 'Nope',
    ]);
});

it('returns server errors', function () {
    $this->getJson('/api/_test/server')->assertStatus(500)->assertJson([
        'code' => 'server_error',
    ]);
});

it('returns authorization errors', function () {
    $this->getJson('/api/_test/authorization')->assertForbidden()->assertJson([
        'code' => 'forbidden',
    ]);
});
