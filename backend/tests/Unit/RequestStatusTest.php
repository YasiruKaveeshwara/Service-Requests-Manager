<?php

declare(strict_types=1);

use App\Enums\ManagerDecision;
use App\Enums\RequestNotificationType;
use App\Enums\RequestStatus;
use App\Exceptions\WorkflowException;

it('allows exactly the documented transitions', function () {
    $expected = [
        'draft' => ['submitted', 'cancelled'],
        'submitted' => ['approved', 'rejected', 'returned', 'cancelled'],
        'returned' => ['submitted', 'cancelled'],
        'approved' => ['in_progress'],
        'in_progress' => ['completed'],
        'rejected' => [],
        'completed' => [],
        'cancelled' => [],
    ];

    foreach (RequestStatus::cases() as $from) {
        foreach (RequestStatus::cases() as $to) {
            expect($from->canTransitionTo($to))
                ->toBe(in_array($to->value, $expected[$from->value], true), "{$from->value} to {$to->value}");
        }
    }
});

it('identifies final and editable statuses', function () {
    foreach (RequestStatus::cases() as $status) {
        expect($status->isFinal())->toBe(in_array($status, [RequestStatus::Rejected, RequestStatus::Completed, RequestStatus::Cancelled], true));
        expect($status->isEditable())->toBe(in_array($status, [RequestStatus::Draft, RequestStatus::Returned], true));
    }
});

it('defines operations-visible statuses', function () {
    expect(RequestStatus::operationsVisible())->toBe([RequestStatus::Approved, RequestStatus::InProgress, RequestStatus::Completed])
        ->and(RequestStatus::operationsVisibleValues())->toBe(['approved', 'in_progress', 'completed']);
});

it('maps manager decisions', function () {
    expect(ManagerDecision::Approve->toStatus())->toBe(RequestStatus::Approved)
        ->and(ManagerDecision::Reject->toStatus())->toBe(RequestStatus::Rejected)
        ->and(ManagerDecision::SendBack->toStatus())->toBe(RequestStatus::Returned)
        ->and(ManagerDecision::Approve->requiresComment())->toBeFalse()
        ->and(ManagerDecision::Reject->requiresComment())->toBeTrue()
        ->and(ManagerDecision::SendBack->requiresComment())->toBeTrue()
        ->and(ManagerDecision::Approve->notificationType())->toBe(RequestNotificationType::Approved);
});

it('describes invalid transitions', function () {
    expect(WorkflowException::invalidTransition(RequestStatus::Completed, RequestStatus::Draft)->getMessage())
        ->toContain('can no longer be changed')
        ->and(WorkflowException::invalidTransition(RequestStatus::Draft, RequestStatus::Approved)->getMessage())
        ->toContain('cannot move from');
});
