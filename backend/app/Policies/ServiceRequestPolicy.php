<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\ServiceRequest;
use App\Models\User;

final class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return match ($user->role) {
            UserRole::Employee => $this->isRequester($user, $serviceRequest),
            UserRole::Manager => $this->isManagerOf($user, $serviceRequest) && $serviceRequest->submitted_at !== null,
            UserRole::Operations => in_array($serviceRequest->status, RequestStatus::operationsVisible(), true),
        };
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Employee);
    }

    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasRole(UserRole::Employee) && $this->isRequester($user, $serviceRequest);
    }

    public function submit(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->update($user, $serviceRequest);
    }

    public function cancel(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->update($user, $serviceRequest);
    }

    public function decide(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasRole(UserRole::Manager) && $this->view($user, $serviceRequest);
    }

    public function assign(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasRole(UserRole::Operations) && $this->view($user, $serviceRequest);
    }

    public function changeStatus(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasRole(UserRole::Operations)
            && $this->view($user, $serviceRequest)
            && ($serviceRequest->assignee_id === null || $serviceRequest->assignee_id === $user->id);
    }

    public function comment(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->view($user, $serviceRequest);
    }

    private function isRequester(User $user, ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->requester_id === $user->id;
    }

    private function isManagerOf(User $user, ServiceRequest $serviceRequest): bool
    {
        $serviceRequest->loadMissing('requester');

        return $serviceRequest->requester->manager_id === $user->id;
    }
}
