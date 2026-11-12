<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

function notificationFor(User $user, array $data = []): DatabaseNotification
{
    return DatabaseNotification::query()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\RequestStatusNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => array_merge(['type' => 'submitted', 'message' => 'Submitted.', 'request_id' => 1], $data),
    ]);
}

it('lists and reads notifications', function () {
    $user = User::factory()->create();
    $notification = notificationFor($user);
    Sanctum::actingAs($user);

    $this->getJson('/api/notifications')
        ->assertOk()
        ->assertJsonPath('data.0.type', 'submitted')
        ->assertJsonPath('meta.unread_count', 1)
        ->assertJsonPath('data.0.read_at', null);
    $this->postJson("/api/notifications/{$notification->id}/read")
        ->assertOk()
        ->assertJsonPath('data.read_at', fn ($value) => $value !== null);
    $this->getJson('/api/notifications')->assertJsonPath('meta.unread_count', 0);
});

it('marks all unread notifications as read', function () {
    $user = User::factory()->create();
    notificationFor($user);
    notificationFor($user);
    Sanctum::actingAs($user);

    $this->postJson('/api/notifications/read-all')
        ->assertOk()
        ->assertJsonPath('data.marked', 2);
    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

it('does not expose another users notifications', function () {
    $owner = User::factory()->create();
    $notification = notificationFor($owner);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson("/api/notifications/{$notification->id}/read")
        ->assertNotFound()
        ->assertJsonPath('code', 'not_found');
});

it('requires authentication for notifications', function () {
    $this->getJson('/api/notifications')
        ->assertUnauthorized()
        ->assertJsonPath('code', 'unauthenticated');
});

it('walks a request from creation to completion across all roles', function () {
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->reportsTo($manager)->create();
    $operations = User::factory()->operations()->create();

    Sanctum::actingAs($employee);
    $id = $this->postJson('/api/requests', [
        'title' => 'New laptop',
        'description' => 'Mine is broken.',
        'category' => 'equipment',
        'priority' => 'high',
    ])->assertCreated()->json('data.id');
    $this->postJson("/api/requests/{$id}/submit")->assertOk();

    Sanctum::actingAs($manager);
    $this->postJson("/api/requests/{$id}/decision", ['decision' => 'approve'])->assertOk();

    Sanctum::actingAs($operations);
    $this->postJson("/api/requests/{$id}/assign", ['assignee_id' => $operations->id])->assertOk();
    $this->postJson("/api/requests/{$id}/status", ['status' => 'in_progress'])->assertOk();
    $this->postJson("/api/requests/{$id}/status", ['status' => 'completed'])->assertOk();
    $this->getJson("/api/requests/{$id}")->assertOk()->assertJsonCount(6, 'data.activity');
});
