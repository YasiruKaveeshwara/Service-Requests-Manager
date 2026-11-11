<?php

declare(strict_types=1);

use App\Enums\ActivityEvent;
use App\Enums\RequestStatus;
use App\Exceptions\WorkflowException;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\CommentService;

it('adds a comment and activity log', function () {
    $actor = User::factory()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create();

    $comment = app(CommentService::class)->add($request, $actor, 'Please review this.');

    expect($comment->body)->toBe('Please review this.')
        ->and($comment->user_id)->toBe($actor->id)
        ->and($request->fresh()->activityLogs->last()->event)->toBe(ActivityEvent::Commented);
});

it('allows comments on open requests', function (RequestStatus $status) {
    $request = ServiceRequest::factory()->inStatus($status)->create();

    expect(fn() => app(CommentService::class)->add($request, User::factory()->create(), 'Update'))
        ->not->toThrow(WorkflowException::class);
})->with([RequestStatus::Draft, RequestStatus::Submitted, RequestStatus::Approved]);

it('rejects comments on final requests', function (RequestStatus $status) {
    $request = ServiceRequest::factory()->inStatus($status)->create();

    expect(fn() => app(CommentService::class)->add($request, User::factory()->create(), 'Update'))
        ->toThrow(WorkflowException::class);
})->with([RequestStatus::Completed, RequestStatus::Rejected, RequestStatus::Cancelled]);
