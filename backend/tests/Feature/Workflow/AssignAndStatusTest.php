<?php

declare(strict_types=1);

use App\Enums\ActivityEvent;
use App\Enums\ManagerDecision;
use App\Enums\RequestStatus;
use App\Exceptions\WorkflowException;
use App\Jobs\SendRequestNotificationsJob;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflowService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake();
});

it('assigns and reassigns operations owners', function () {
    $actor = User::factory()->manager()->create();
    $ops1 = User::factory()->operations()->create();
    $ops2 = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->create();
    $service = app(RequestWorkflowService::class);

    $service->assign($request, $actor, $ops1, 'Initial owner');
    $service->assign($request, $actor, $ops2, 'New owner');

    $logs = $request->fresh()->activityLogs;
    expect($request->fresh()->assignee_id)->toBe($ops2->id)
        ->and($logs->last()->event)->toBe(ActivityEvent::Reassigned)
        ->and($logs->last()->metadata)->toMatchArray(['from_assignee_id' => $ops1->id, 'to_assignee_id' => $ops2->id]);
    Queue::assertPushed(SendRequestNotificationsJob::class, 2);
});

it('rejects invalid assignments', function (RequestStatus $status) {
    $request = ServiceRequest::factory()->inStatus($status)->create();
    $assignee = User::factory()->operations()->create();

    expect(fn () => app(RequestWorkflowService::class)->assign($request, User::factory()->manager()->create(), $assignee))
        ->toThrow(WorkflowException::class);
})->with([
    RequestStatus::Draft,
    RequestStatus::Submitted,
    RequestStatus::Returned,
    RequestStatus::Completed,
    RequestStatus::Rejected,
    RequestStatus::Cancelled,
]);

it('rejects non-operations and duplicate assignees', function () {
    $actor = User::factory()->manager()->create();
    $employee = User::factory()->create();
    $ops = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->assignedTo($ops)->create();
    $service = app(RequestWorkflowService::class);

    expect(fn () => $service->assign($request, $actor, $employee))->toThrow(WorkflowException::class)
        ->and(fn () => $service->assign($request, $actor, $ops))->toThrow(WorkflowException::class);
});

it('starts and completes work for the assignee', function () {
    $ops = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->assignedTo($ops)->create();
    $service = app(RequestWorkflowService::class);

    $service->changeStatus($request, $ops, RequestStatus::InProgress);
    $service->changeStatus($request->fresh(), $ops, RequestStatus::Completed);

    expect($request->fresh()->status)->toBe(RequestStatus::Completed)
        ->and($request->fresh()->completed_at)->not->toBeNull();
    Queue::assertPushed(SendRequestNotificationsJob::class, 2);
});

it('rejects invalid or unauthorized status changes', function () {
    $ops = User::factory()->operations()->create();
    $other = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->assignedTo($ops)->create();
    $service = app(RequestWorkflowService::class);

    expect(fn () => $service->changeStatus($request, $other, RequestStatus::InProgress))
        ->toThrow(AuthorizationException::class)
        ->and(fn () => $service->changeStatus($request, $ops, RequestStatus::Completed))
        ->toThrow(WorkflowException::class)
        ->and(fn () => $service->changeStatus($request, $ops, RequestStatus::Approved))
        ->toThrow(WorkflowException::class);
});

it('requires an assignee before changing status', function () {
    $ops = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->create();

    expect(fn () => app(RequestWorkflowService::class)->changeStatus($request, $ops, RequestStatus::InProgress))
        ->toThrow(WorkflowException::class);
});

it('runs the complete workflow lifecycle', function () {
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->reportsTo($manager)->create();
    $ops = User::factory()->operations()->create();
    $service = app(RequestWorkflowService::class);
    $request = $service->create($employee, ['title' => 'Access', 'description' => 'Needs access.', 'category' => 'access', 'priority' => 'medium']);

    $service->submit($request, $employee);
    $service->decide($request->fresh(), $manager, ManagerDecision::Approve);
    $service->assign($request->fresh(), $manager, $ops);
    $service->changeStatus($request->fresh(), $ops, RequestStatus::InProgress);
    $service->changeStatus($request->fresh(), $ops, RequestStatus::Completed);

    expect($request->fresh()->status)->toBe(RequestStatus::Completed)
        ->and($request->fresh()->activityLogs->pluck('event')->all())->toBe([
            ActivityEvent::Created,
            ActivityEvent::Transitioned,
            ActivityEvent::Transitioned,
            ActivityEvent::Assigned,
            ActivityEvent::Transitioned,
            ActivityEvent::Transitioned,
        ]);
    Queue::assertPushed(SendRequestNotificationsJob::class, 5);
});
