<?php

declare(strict_types=1);

use App\Enums\ActivityEvent;
use App\Enums\ManagerDecision;
use App\Enums\RequestNotificationType;
use App\Enums\RequestStatus;
use App\Exceptions\WorkflowException;
use App\Jobs\SendRequestNotificationsJob;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflowService;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake();
});

it('creates a draft and activity log', function () {
    $actor = User::factory()->create();
    $request = app(RequestWorkflowService::class)->create($actor, requestData());

    expect($request->status)->toBe(RequestStatus::Draft)
        ->and($request->requester_id)->toBe($actor->id)
        ->and($request->activityLogs)->toHaveCount(1)
        ->and($request->activityLogs->first()->event)->toBe(ActivityEvent::Created);
});

it('creates and submits a request', function () {
    $actor = User::factory()->reportsTo(User::factory()->manager()->create())->create();
    $request = app(RequestWorkflowService::class)->create($actor, requestData(), true);

    expect($request->status)->toBe(RequestStatus::Submitted)
        ->and($request->submitted_at)->not->toBeNull()
        ->and($request->activityLogs)->toHaveCount(2);
});

it('does not create when submitting without a manager', function () {
    $actor = User::factory()->create();

    expect(fn () => app(RequestWorkflowService::class)->create($actor, requestData(), true))
        ->toThrow(WorkflowException::class);
    expect(ServiceRequest::query()->count())->toBe(0);
});

it('updates an editable request and records changes', function () {
    $actor = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $actor->id]);
    $updated = app(RequestWorkflowService::class)->update($request, $actor, ['title' => 'Changed title']);

    expect($updated->title)->toBe('Changed title')
        ->and($updated->activityLogs->last()->event)->toBe(ActivityEvent::Updated)
        ->and($updated->activityLogs->last()->metadata['changes']['title'])->toMatchArray(['from' => $request->getRawOriginal('title'), 'to' => 'Changed title']);
});

it('does not log identical updates', function () {
    $actor = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $actor->id]);
    app(RequestWorkflowService::class)->update($request, $actor, ['title' => $request->title]);

    expect($request->activityLogs()->count())->toBe(0);
});

it('rejects edits outside editable statuses', function (RequestStatus $status) {
    $actor = User::factory()->create();
    $request = ServiceRequest::factory()->inStatus($status)->create(['requester_id' => $actor->id]);

    expect(fn () => app(RequestWorkflowService::class)->update($request, $actor, ['title' => 'Nope']))
        ->toThrow(WorkflowException::class);
})->with([
    RequestStatus::Submitted,
    RequestStatus::Approved,
    RequestStatus::Completed,
    RequestStatus::Rejected,
    RequestStatus::Cancelled,
]);

it('submits a draft and queues notification', function () {
    $manager = User::factory()->manager()->create();
    $actor = User::factory()->reportsTo($manager)->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $actor->id]);

    app(RequestWorkflowService::class)->submit($request, $actor, 'Please review');

    expect($request->fresh()->status)->toBe(RequestStatus::Submitted)
        ->and($request->fresh()->activityLogs->last()->comment)->toBe('Please review');
    Queue::assertPushed(SendRequestNotificationsJob::class, fn (SendRequestNotificationsJob $job): bool => $job->type === RequestNotificationType::Submitted && $job->actorId === $actor->id);
});

it('submits a returned request', function () {
    $manager = User::factory()->manager()->create();
    $actor = User::factory()->reportsTo($manager)->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Returned)->create(['requester_id' => $actor->id]);

    expect(app(RequestWorkflowService::class)->submit($request, $actor)->status)->toBe(RequestStatus::Submitted);
});

it('rejects invalid submissions', function (RequestStatus $status) {
    $actor = User::factory()->create();
    $request = ServiceRequest::factory()->inStatus($status)->create(['requester_id' => $actor->id]);

    expect(fn () => app(RequestWorkflowService::class)->submit($request, $actor))
        ->toThrow(WorkflowException::class);
})->with([RequestStatus::Submitted, RequestStatus::Approved, RequestStatus::Completed, RequestStatus::Rejected, RequestStatus::Cancelled]);

it('requires a manager to submit', function () {
    $actor = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $actor->id]);

    expect(fn () => app(RequestWorkflowService::class)->submit($request, $actor))
        ->toThrow(WorkflowException::class);
});

it('cancels draft submitted and returned requests', function (RequestStatus $status) {
    $actor = User::factory()->create();
    $request = ServiceRequest::factory()->inStatus($status)->create(['requester_id' => $actor->id]);

    app(RequestWorkflowService::class)->cancel($request, $actor);

    expect($request->fresh()->status)->toBe(RequestStatus::Cancelled);
})->with([RequestStatus::Draft, RequestStatus::Submitted, RequestStatus::Returned]);

it('rejects cancellation of active or final requests', function (RequestStatus $status) {
    $actor = User::factory()->create();
    $request = ServiceRequest::factory()->inStatus($status)->create(['requester_id' => $actor->id]);

    expect(fn () => app(RequestWorkflowService::class)->cancel($request, $actor))
        ->toThrow(WorkflowException::class);
})->with([RequestStatus::Approved, RequestStatus::InProgress, RequestStatus::Completed, RequestStatus::Rejected]);

it('approves a submitted request', function () {
    [$request, $manager] = submittedRequest();

    app(RequestWorkflowService::class)->decide($request, $manager, ManagerDecision::Approve);

    expect($request->fresh()->status)->toBe(RequestStatus::Approved);
    Queue::assertPushed(SendRequestNotificationsJob::class, fn (SendRequestNotificationsJob $job): bool => $job->type === RequestNotificationType::Approved);
});

it('rejects and returns with comments', function (ManagerDecision $decision, RequestStatus $status) {
    [$request, $manager] = submittedRequest();

    app(RequestWorkflowService::class)->decide($request, $manager, $decision, 'Needs changes');

    expect($request->fresh()->status)->toBe($status)
        ->and($request->fresh()->activityLogs->last()->comment)->toBe('Needs changes');
})->with([
    [ManagerDecision::Reject, RequestStatus::Rejected],
    [ManagerDecision::SendBack, RequestStatus::Returned],
]);

it('requires comments for reject and return only', function (ManagerDecision $decision) {
    [$request, $manager] = submittedRequest();

    expect(fn () => app(RequestWorkflowService::class)->decide($request, $manager, $decision))
        ->toThrow(WorkflowException::class);
})->with([ManagerDecision::Reject, ManagerDecision::SendBack]);

it('allows approve without a comment', function () {
    [$request, $manager] = submittedRequest();

    expect(app(RequestWorkflowService::class)->decide($request, $manager, ManagerDecision::Approve)->status)
        ->toBe(RequestStatus::Approved);
});

it('rejects decisions on non-submitted requests', function (RequestStatus $status) {
    $manager = User::factory()->manager()->create();
    $request = ServiceRequest::factory()->inStatus($status)->create();

    expect(fn () => app(RequestWorkflowService::class)->decide($request, $manager, ManagerDecision::Approve))
        ->toThrow(WorkflowException::class);
})->with([RequestStatus::Draft, RequestStatus::Approved, RequestStatus::Completed, RequestStatus::Rejected]);

it('rolls back failed rules and dispatches no job', function () {
    $actor = User::factory()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $actor->id]);

    expect(fn () => app(RequestWorkflowService::class)->decide($request, $actor, ManagerDecision::Reject))
        ->toThrow(WorkflowException::class);
    Queue::assertNothingPushed();
    expect($request->fresh()->status)->toBe(RequestStatus::Submitted);
});

it('records the full workflow chain', function () {
    $manager = User::factory()->manager()->create();
    $actor = User::factory()->reportsTo($manager)->create();
    $service = app(RequestWorkflowService::class);
    $request = $service->create($actor, requestData());
    $service->submit($request, $actor);
    $service->decide($request, $manager, ManagerDecision::SendBack, 'Revise');
    $service->submit($request->fresh(), $actor);
    $service->decide($request->fresh(), $manager, ManagerDecision::Approve);

    expect($request->fresh()->activityLogs)->toHaveCount(5);
});

function requestData(): array
{
    return [
        'title' => 'Access request',
        'description' => 'Needs access.',
        'category' => 'access',
        'priority' => 'medium',
    ];
}

function submittedRequest(): array
{
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->reportsTo($manager)->create();

    return [ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $employee->id]), $manager];
}
