<?php

declare(strict_types=1);

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lists only requests visible to an employee', function () {
    $user = User::factory()->create();
    ServiceRequest::factory()->count(2)->create(['requester_id' => $user->id]);
    ServiceRequest::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/requests')->assertOk()->assertJsonCount(2, 'data');
});

it('lists only non-draft reports for a manager', function () {
    $manager = User::factory()->manager()->create();
    $report = User::factory()->reportsTo($manager)->create();
    ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $report->id]);
    ServiceRequest::factory()->create(['requester_id' => $report->id]);
    ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create();
    Sanctum::actingAs($manager);

    $this->getJson('/api/requests')->assertOk()->assertJsonCount(1, 'data');
});

it('lists only operations-visible statuses and filters them', function () {
    $operations = User::factory()->operations()->create();
    ServiceRequest::factory()->inStatus(RequestStatus::Approved)->create(['title' => 'Access approval']);
    ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['title' => 'Hidden request']);
    Sanctum::actingAs($operations);

    $this->getJson('/api/requests?status=approved&search=ACCESS')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Access approval');
});

it('validates list filters', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/requests?status=unknown')->assertStatus(422);
});
