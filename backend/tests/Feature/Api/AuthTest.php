<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

it('logs in with valid credentials', function () {
    $user = User::factory()->manager()->create(['password' => 'password']);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.role', 'manager')
        ->assertJsonStructure(['data' => ['token', 'token_type', 'user']])
        ->assertJsonMissingPath('data.user.password');
});

it('rejects an incorrect password', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertUnauthorized()
        ->assertJson(['code' => 'unauthenticated', 'message' => 'Invalid email or password.']);
});

it('rejects an unknown email with the same error', function () {
    $this->postJson('/api/login', ['email' => 'unknown@example.com', 'password' => 'wrong'])
        ->assertUnauthorized()
        ->assertJson(['code' => 'unauthenticated', 'message' => 'Invalid email or password.']);
});

it('validates missing login fields', function () {
    $this->postJson('/api/login')
        ->assertStatus(422)
        ->assertJson(['code' => 'validation_failed'])
        ->assertJsonStructure(['errors' => ['email', 'password']]);
});

it('returns the authenticated user from me', function () {
    $user = User::factory()->create(['password' => 'password']);
    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('rejects me without a token', function () {
    $this->get('/api/me')
        ->assertUnauthorized()
        ->assertJson(['code' => 'unauthenticated']);
});

it('deletes the current token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)->postJson('/api/logout')->assertOk();

    expect(DB::table('personal_access_tokens')->count())->toBe(0);
});

it('throttles the sixth failed login', function () {
    $user = User::factory()->create(['password' => 'password']);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertUnauthorized();
    }

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertStatus(429)
        ->assertJson(['code' => 'too_many_requests']);
});

it('expires tokens after the configured lifetime', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api')->plainTextToken;

    Carbon::setTestNow(now()->addMinutes(481));

    try {
        $this->withToken($token)->getJson('/api/me')
            ->assertUnauthorized()
            ->assertJson(['code' => 'unauthenticated']);
    } finally {
        Carbon::setTestNow();
    }
});
