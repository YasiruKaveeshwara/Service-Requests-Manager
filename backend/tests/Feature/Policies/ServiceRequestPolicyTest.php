<?php

declare(strict_types=1);

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;

it('allows employees to view only their own requests', function () {
    $employee = User::factory()->create();
    $own = ServiceRequest::factory()->create(['requester_id' => $employee->id]);
    $other = ServiceRequest::factory()->create();

    expect($employee->can('view', $own))->toBeTrue()
        ->and($employee->can('view', $other))->toBeFalse();
});

it('allows managers to view submitted reports only', function () {
    $manager = User::factory()->manager()->create();
    $report = User::factory()->reportsTo($manager)->create();
    $submitted = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $report->id]);
    $draft = ServiceRequest::factory()->create(['requester_id' => $report->id]);
    $other = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create();

    expect($manager->can('view', $submitted))->toBeTrue()
        ->and($manager->can('view', $draft))->toBeFalse()
        ->and($manager->can('view', $other))->toBeFalse();
});

it('allows operations to view approved and later requests', function (RequestStatus $status, bool $allowed) {
    $operations = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->inStatus($status)->create();

    expect($operations->can('view', $request))->toBe($allowed)
        ->and($operations->can('comment', $request))->toBe($allowed);
})->with([
    [RequestStatus::Approved, true],
    [RequestStatus::InProgress, true],
    [RequestStatus::Completed, true],
    [RequestStatus::Submitted, false],
    [RequestStatus::Draft, false],
    [RequestStatus::Returned, false],
    [RequestStatus::Rejected, false],
    [RequestStatus::Cancelled, false],
]);

it('restricts create to employees', function () {
    expect(User::factory()->create()->can('create', ServiceRequest::class))->toBeTrue()
        ->and(User::factory()->manager()->create()->can('create', ServiceRequest::class))->toBeFalse()
        ->and(User::factory()->operations()->create()->can('create', ServiceRequest::class))->toBeFalse();
});

it('allows every authenticated user to view request lists', function () {
    expect(User::factory()->create()->can('viewAny', ServiceRequest::class))->toBeTrue()
        ->and(User::factory()->manager()->create()->can('viewAny', ServiceRequest::class))->toBeTrue()
        ->and(User::factory()->operations()->create()->can('viewAny', ServiceRequest::class))->toBeTrue();
});

it('restricts update submit and cancel to the requester employee', function () {
    $employee = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $employee->id]);
    $other = User::factory()->create();

    expect($employee->can('update', $request))->toBeTrue()
        ->and($employee->can('submit', $request))->toBeTrue()
        ->and($employee->can('cancel', $request))->toBeTrue()
        ->and($other->can('update', $request))->toBeFalse()
        ->and(User::factory()->manager()->create()->can('update', $request))->toBeFalse();
});

it('does not allow managers or operations to update requests', function () {
    $request = ServiceRequest::factory()->create();

    expect(User::factory()->manager()->create()->can('update', $request))->toBeFalse()
        ->and(User::factory()->operations()->create()->can('update', $request))->toBeFalse();
});

it('restricts manager decisions to a manager of the requester', function () {
    $manager = User::factory()->manager()->create();
    $report = User::factory()->reportsTo($manager)->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $report->id]);

    expect($manager->can('decide', $request))->toBeTrue()
        ->and(User::factory()->manager()->create()->can('decide', $request))->toBeFalse()
        ->and(User::factory()->create()->can('decide', $request))->toBeFalse();
});

it('does not allow managers or employees to assign requests', function () {
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->create();

    expect(User::factory()->manager()->create()->can('assign', $request))->toBeFalse()
        ->and(User::factory()->create()->can('assign', $request))->toBeFalse();
});

it('restricts operations assignments to visible requests', function () {
    $operations = User::factory()->operations()->create();
    $approved = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->create();
    $submitted = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create();

    expect($operations->can('assign', $approved))->toBeTrue()
        ->and($operations->can('assign', $submitted))->toBeFalse()
        ->and(User::factory()->create()->can('assign', $approved))->toBeFalse();
});

it('does not allow non-operations users to change status', function () {
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->create();

    expect(User::factory()->create()->can('changeStatus', $request))->toBeFalse()
        ->and(User::factory()->manager()->create()->can('changeStatus', $request))->toBeFalse();
});

it('allows requesters to comment on their own requests', function () {
    $employee = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $employee->id]);

    expect($employee->can('comment', $request))->toBeTrue();
});

it('denies comments to employees on another request', function () {
    $employee = User::factory()->create();
    $request = ServiceRequest::factory()->create();

    expect($employee->can('comment', $request))->toBeFalse();
});

it('allows status changes for an unassigned request or its assignee only', function () {
    $operations = User::factory()->operations()->create();
    $other = User::factory()->operations()->create();
    $unassigned = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->create();
    $assigned = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->assignedTo($operations)->create();

    expect($operations->can('changeStatus', $unassigned))->toBeTrue()
        ->and($operations->can('changeStatus', $assigned))->toBeTrue()
        ->and($other->can('changeStatus', $assigned))->toBeFalse();
});
