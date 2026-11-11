<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;

final class RequestAbilities
{
    /** @return array<string, bool> */
    public static function for(User $user, ServiceRequest $serviceRequest): array
    {
        $status = $serviceRequest->status;

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
