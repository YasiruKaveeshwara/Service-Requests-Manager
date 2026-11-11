<?php

declare(strict_types=1);

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function requestPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Laptop access',
        'description' => 'Needs access to the finance system.',
        'category' => 'access',
        'priority' => 'high',
    ], $overrides);
}

it('creates a draft and includes request abilities', function () {
    $user = User::factory()->reportsTo(User::factory()->manager()->create())->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/requests', requestPayload());

    $response->assertCreated()
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.requester.id', $user->id)
        ->assertJsonPath('data.abilities.submit', true);
});

it('creates and submits a request', function () {
    $manager = User::factory()->manager()->create();
    $user = User::factory()->reportsTo($manager)->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/requests', requestPayload(['submit' => true]))
        ->assertCreated()
        ->assertJsonPath('data.status', 'submitted');
});

it('rejects invalid create data', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/requests', ['title' => 'Only title'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['description', 'category']);
});

it('prevents non-employees from creating requests', function () {
    Sanctum::actingAs(User::factory()->manager()->create());

    $this->postJson('/api/requests', requestPayload())->assertForbidden();
});

it('ignores protected create attributes', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/requests', requestPayload([
        'status' => 'completed',
        'requester_id' => 999,
        'assignee_id' => 1,
    ]))->assertCreated();

    $request = ServiceRequest::query()->latest('id')->firstOrFail();
    expect($request->status)->toBe(RequestStatus::Draft)
        ->and($request->requester_id)->toBe($user->id)
        ->and($request->assignee_id)->toBeNull();
});

it('shows own request details and rejects another employee', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $ownResponse = $this->postJson('/api/requests', requestPayload());
    $own = ServiceRequest::query()->findOrFail($ownResponse->json('data.id'));
    $other = ServiceRequest::factory()->create();

    $this->getJson("/api/requests/{$own->id}")
        ->assertOk()
        ->assertJsonPath('data.comments', [])
        ->assertJsonPath('data.activity.0.event', 'created');
    $this->getJson("/api/requests/{$other->id}")->assertForbidden();
});

it('returns not found for a missing request', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/requests/999999')->assertNotFound();
});

it('enforces manager and operations visibility', function () {
    $manager = User::factory()->manager()->create();
    $report = User::factory()->reportsTo($manager)->create();
    $submitted = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $report->id]);
    $draft = ServiceRequest::factory()->create(['requester_id' => $report->id]);
    Sanctum::actingAs($manager);
    $this->getJson("/api/requests/{$submitted->id}")->assertOk();
    $this->getJson("/api/requests/{$draft->id}")->assertForbidden();

    $operations = User::factory()->operations()->create();
    $approved = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->create();
    Sanctum::actingAs($operations);
    $this->getJson("/api/requests/{$approved->id}")->assertOk();
    $this->getJson("/api/requests/{$submitted->id}")->assertForbidden();
});

it('updates a draft and records the activity', function () {
    $user = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $user->id]);
    Sanctum::actingAs($user);

    $this->putJson("/api/requests/{$request->id}", requestPayload(['title' => 'Updated title']))
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated title');

    expect($request->fresh()->activityLogs->last()->event->value)->toBe('updated');
});

it('rejects updates by unauthorized users and invalid states', function () {
    $owner = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $owner->id]);
    Sanctum::actingAs(User::factory()->create());
    $this->putJson("/api/requests/{$request->id}", requestPayload())->assertForbidden();

    Sanctum::actingAs($owner);
    $submitted = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $owner->id]);
    $this->putJson("/api/requests/{$submitted->id}", requestPayload())->assertStatus(422);
});
