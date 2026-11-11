<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivityEvent;
use App\Enums\ManagerDecision;
use App\Enums\RequestNotificationType;
use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Exceptions\WorkflowException;
use App\Jobs\SendRequestNotificationsJob;
use App\Models\ServiceRequest;
use App\Models\User;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class RequestWorkflowService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data, bool $submit = false): ServiceRequest
    {
        if ($submit) {
            $this->assertHasManager($actor);
        }

        $serviceRequest = DB::transaction(function () use ($actor, $data): ServiceRequest {
            $serviceRequest = new ServiceRequest(Arr::whereNotNull($data));
            $serviceRequest->status = RequestStatus::Draft;
            $serviceRequest->requester_id = $actor->id;
            $serviceRequest->save();

            $this->activity->log($serviceRequest, $actor, ActivityEvent::Created, to: RequestStatus::Draft);

            return $serviceRequest;
        });

        return $submit ? $this->submit($serviceRequest, $actor) : $serviceRequest;
    }

    /** @param array<string, mixed> $data */
    public function update(ServiceRequest $serviceRequest, User $actor, array $data): ServiceRequest
    {
        return $this->run($serviceRequest, $actor, function (ServiceRequest $locked) use ($actor, $data): null {
            if (! $locked->status->isEditable()) {
                throw WorkflowException::because('Only requests in draft or returned status can be edited.');
            }

            $locked->fill(Arr::whereNotNull($data));
            $changes = $this->changes($locked);

            if ($changes !== []) {
                $locked->save();
                $this->activity->log($locked, $actor, ActivityEvent::Updated, metadata: ['changes' => $changes]);
            }

            return null;
        });
    }

    public function submit(ServiceRequest $serviceRequest, User $actor, ?string $comment = null): ServiceRequest
    {
        return $this->run($serviceRequest, $actor, function (ServiceRequest $locked) use ($actor, $comment): RequestNotificationType {
            $this->assertTransition($locked, RequestStatus::Submitted);
            $this->assertHasManager($actor);
            $this->moveTo($locked, $actor, RequestStatus::Submitted, $comment, ['submitted_at' => now()]);

            return RequestNotificationType::Submitted;
        });
    }

    public function cancel(ServiceRequest $serviceRequest, User $actor, ?string $comment = null): ServiceRequest
    {
        return $this->run($serviceRequest, $actor, function (ServiceRequest $locked) use ($actor, $comment): RequestNotificationType {
            $this->assertTransition($locked, RequestStatus::Cancelled);
            $this->moveTo($locked, $actor, RequestStatus::Cancelled, $comment);

            return RequestNotificationType::Cancelled;
        });
    }

    public function decide(ServiceRequest $serviceRequest, User $actor, ManagerDecision $decision, ?string $comment = null): ServiceRequest
    {
        return $this->run($serviceRequest, $actor, function (ServiceRequest $locked) use ($actor, $decision, $comment): RequestNotificationType {
            $target = $decision->toStatus();
            $this->assertTransition($locked, $target);

            if ($decision->requiresComment() && blank($comment)) {
                throw WorkflowException::because('A comment is required to reject or return a request.');
            }

            $this->moveTo($locked, $actor, $target, $comment);

            return $decision->notificationType();
        });
    }

    public function assign(ServiceRequest $serviceRequest, User $actor, User $assignee, ?string $comment = null): ServiceRequest
    {
        return $this->run($serviceRequest, $actor, function (ServiceRequest $locked) use ($actor, $assignee, $comment): RequestNotificationType {
            if (! in_array($locked->status, [RequestStatus::Approved, RequestStatus::InProgress], true)) {
                throw WorkflowException::because('An owner can only be assigned while the request is approved or in progress.');
            }

            if ($assignee->role !== UserRole::Operations) {
                throw WorkflowException::because('The assignee must be an operations user.');
            }

            if ($locked->assignee_id === $assignee->id) {
                throw WorkflowException::because('The request is already assigned to this user.');
            }

            $previousId = $locked->assignee_id;
            $locked->forceFill(['assignee_id' => $assignee->id])->save();

            $this->activity->log(
                $locked,
                $actor,
                $previousId === null ? ActivityEvent::Assigned : ActivityEvent::Reassigned,
                comment: $comment,
                metadata: ['from_assignee_id' => $previousId, 'to_assignee_id' => $assignee->id, 'to_assignee_name' => $assignee->name],
            );

            return RequestNotificationType::Assigned;
        });
    }

    public function changeStatus(ServiceRequest $serviceRequest, User $actor, RequestStatus $target, ?string $comment = null): ServiceRequest
    {
        return $this->run($serviceRequest, $actor, function (ServiceRequest $locked) use ($actor, $target, $comment): RequestNotificationType {
            if (! in_array($target, [RequestStatus::InProgress, RequestStatus::Completed], true)) {
                throw WorkflowException::because('Status can only be changed to in progress or completed.');
            }

            $this->assertTransition($locked, $target);

            if ($locked->assignee_id === null) {
                throw WorkflowException::because('Assign an owner before starting or completing work.');
            }

            if ($locked->assignee_id !== $actor->id) {
                throw new AuthorizationException('Only the assignee can change the status of this request.');
            }

            $completed = $target === RequestStatus::Completed;
            $this->moveTo($locked, $actor, $target, $comment, $completed ? ['completed_at' => now()] : []);

            return $completed ? RequestNotificationType::Completed : RequestNotificationType::Started;
        });
    }

    /**
     * @param  Closure(ServiceRequest): (RequestNotificationType|null)  $callback
     */
    private function run(ServiceRequest $serviceRequest, User $actor, Closure $callback): ServiceRequest
    {
        [$locked, $notification] = DB::transaction(function () use ($serviceRequest, $callback): array {
            $locked = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->getKey());

            return [$locked, $callback($locked)];
        });

        if ($notification instanceof RequestNotificationType) {
            SendRequestNotificationsJob::dispatch($locked, $notification, $actor->id);
        }

        return $locked->refresh();
    }

    private function assertTransition(ServiceRequest $serviceRequest, RequestStatus $target): void
    {
        if (! $serviceRequest->status->canTransitionTo($target)) {
            throw WorkflowException::invalidTransition($serviceRequest->status, $target);
        }
    }

    private function assertHasManager(User $actor): void
    {
        if ($actor->manager_id === null) {
            throw WorkflowException::because('You have no manager assigned, so the request cannot be submitted for review.');
        }
    }

    /** @param array<string, mixed> $attributes */
    private function moveTo(ServiceRequest $locked, User $actor, RequestStatus $target, ?string $comment, array $attributes = []): void
    {
        $from = $locked->status;
        $locked->forceFill(['status' => $target, ...$attributes])->save();

        $this->activity->log($locked, $actor, ActivityEvent::Transitioned, $from, $target, $comment);
    }

    /** @return array<string, array{from: mixed, to: mixed}> */
    private function changes(ServiceRequest $model): array
    {
        $changes = [];

        foreach ($model->getDirty() as $field => $new) {
            $changes[$field] = ['from' => $model->getRawOriginal($field), 'to' => $new];
        }

        return $changes;
    }
}
