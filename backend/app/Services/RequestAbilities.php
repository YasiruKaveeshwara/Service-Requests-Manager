<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;

/**
 * Builds the action map returned with each request resource.
 *
 * The frontend uses this map for presentation, but every action remains
 * protected by the corresponding policy and workflow service validation.
 */
final class RequestAbilities
{
    /** @return array<string, bool> */
    public static function for(User $user, ServiceRequest $serviceRequest): array
    {
        $status = $serviceRequest->status;

        // Keep status checks alongside policy checks so an action disappears as
        // soon as the request leaves the state in which it is meaningful.
        return [
            'update' => $user->can('update', $serviceRequest) && $status->isEditable(),
            'submit' => $user->can('submit', $serviceRequest) && $status->canTransitionTo(RequestStatus::Submitted) && $user->manager_id !== null,
            'cancel' => $user->can('cancel', $serviceRequest) && $status->canTransitionTo(RequestStatus::Cancelled),
            'decide' => $user->can('decide', $serviceRequest) && $status === RequestStatus::Submitted,
            'assign' => $user->can('assign', $serviceRequest) && in_array($status, [RequestStatus::Approved, RequestStatus::InProgress], true),
            'start' => $user->can('changeStatus', $serviceRequest) && $status === RequestStatus::Approved && $serviceRequest->assignee_id !== null,
            'complete' => $user->can('changeStatus', $serviceRequest) && $status === RequestStatus::InProgress,
            'comment' => $user->can('comment', $serviceRequest) && ! $status->isFinal(),
        ];
    }
}
