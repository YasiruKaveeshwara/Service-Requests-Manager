<?php

declare(strict_types=1);

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('creates comments and exposes them in request details', function () {
    $user = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $user->id]);
    Sanctum::actingAs($user);

    $this->postJson("/api/requests/{$request->id}/comments", ['body' => 'Please review.'])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Please review.')
        ->assertJsonPath('data.user.id', $user->id);
    $this->getJson("/api/requests/{$request->id}")
        ->assertOk()
        ->assertJsonPath('data.comments.0.body', 'Please review.')
        ->assertJsonPath('data.activity.0.event', 'commented');
});

it('allows managers and operations users to comment on visible requests', function () {
    $manager = User::factory()->manager()->create();
    $report = User::factory()->reportsTo($manager)->create();
    $submitted = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $report->id]);
    Sanctum::actingAs($manager);
    $this->postJson("/api/requests/{$submitted->id}/comments", ['body' => 'Manager note.'])->assertCreated();

    $operations = User::factory()->operations()->create();
    $approved = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->create();
    Sanctum::actingAs($operations);
    $this->postJson("/api/requests/{$approved->id}/comments", ['body' => 'Operations note.'])->assertCreated();
});

it('rejects comments on final requests', function (RequestStatus $status) {
    $owner = User::factory()->create();
    $request = ServiceRequest::factory()->inStatus($status)->create(['requester_id' => $owner->id]);
    Sanctum::actingAs($owner);

    $this->postJson("/api/requests/{$request->id}/comments", ['body' => 'Closed note.'])
        ->assertStatus(422)
        ->assertJsonPath('code', 'invalid_transition');
})->with([RequestStatus::Completed, RequestStatus::Rejected, RequestStatus::Cancelled]);

it('validates comment bodies', function () {
    $user = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $user->id]);
    Sanctum::actingAs($user);

    $this->postJson("/api/requests/{$request->id}/comments", [])->assertStatus(422);
    $this->postJson("/api/requests/{$request->id}/comments", ['body' => str_repeat('x', 2001)])->assertStatus(422);
});

it('filters and paginates activity logs', function () {
    $user = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $user->id]);
    $request->activityLogs()->createMany([
        ['actor_id' => $user->id, 'event' => 'commented', 'comment' => 'One'],
        ['actor_id' => $user->id, 'event' => 'commented', 'comment' => 'Two'],
    ]);
    Sanctum::actingAs($user);

    $this->getJson("/api/requests/{$request->id}/activity?event=commented&per_page=1")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.total', 2);
    $this->getJson("/api/requests/{$request->id}/activity?event=unknown")->assertStatus(422);
});

it('protects activity visibility and missing requests', function () {
    $owner = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $owner->id]);
    Sanctum::actingAs(User::factory()->create());

    $this->getJson("/api/requests/{$request->id}/activity")->assertForbidden();
    $this->getJson('/api/requests/999999/activity')->assertNotFound();
});

it('lists operations users by default and supports role filtering', function () {
    User::factory()->operations()->create(['name' => 'Zed']);
    User::factory()->operations()->create(['name' => 'Alice']);
    User::factory()->manager()->create();
    Sanctum::actingAs(User::factory()->operations()->create());

    $this->getJson('/api/users')->assertOk()->assertJsonCount(3, 'data');
    $this->getJson('/api/users?role=manager')->assertOk()->assertJsonCount(1, 'data');
});

it('restricts user listing to operations users', function () {
    Sanctum::actingAs(User::factory()->create());
    $this->getJson('/api/users')->assertForbidden();
    Sanctum::actingAs(User::factory()->manager()->create());
    $this->getJson('/api/users')->assertForbidden();
});
