<?php

declare(strict_types=1);

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestAbilities;

it('calculates employee draft abilities', function () {
    $employee = User::factory()->reportsTo(User::factory()->manager()->create())->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $employee->id]);

    expect(RequestAbilities::for($employee, $request))->toBe([
        'update' => true,
        'submit' => true,
        'cancel' => true,
        'decide' => false,
        'assign' => false,
        'start' => false,
        'complete' => false,
        'comment' => true,
    ]);
});

it('does not allow submission without a manager', function () {
    $employee = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $employee->id]);

    expect(RequestAbilities::for($employee, $request)['submit'])->toBeFalse();
});

it('calculates manager submitted abilities', function () {
    $manager = User::factory()->manager()->create();
    $report = User::factory()->reportsTo($manager)->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $report->id]);

    expect(RequestAbilities::for($manager, $request))->toBe([
        'update' => false,
        'submit' => false,
        'cancel' => false,
        'decide' => true,
        'assign' => false,
        'start' => false,
        'complete' => false,
        'comment' => true,
    ]);
});

it('calculates operations approved abilities', function () {
    $operations = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->assignedTo($operations)->create();

    expect(RequestAbilities::for($operations, $request))->toBe([
        'update' => false,
        'submit' => false,
        'cancel' => false,
        'decide' => false,
        'assign' => true,
        'start' => true,
        'complete' => false,
        'comment' => true,
    ]);
});

it('calculates operations in progress abilities', function () {
    $operations = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::InProgress)->assignedTo($operations)->create();

    expect(RequestAbilities::for($operations, $request))->toBe([
        'update' => false,
        'submit' => false,
        'cancel' => false,
        'decide' => false,
        'assign' => true,
        'start' => false,
        'complete' => true,
        'comment' => true,
    ]);
});

it('does not allow comments on completed requests', function () {
    $operations = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Completed)->create();

    expect(RequestAbilities::for($operations, $request)['comment'])->toBeFalse();
});
