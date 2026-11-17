<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ManagerDecision;
use App\Enums\RequestStatus;
use App\Http\Requests\AssignServiceRequestRequest;
use App\Http\Requests\CancelServiceRequestRequest;
use App\Http\Requests\ChangeStatusRequest;
use App\Http\Requests\DecideServiceRequestRequest;
use App\Http\Requests\SubmitServiceRequestRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflowService;

/**
 * HTTP adapter for request lifecycle actions.
 *
 * This controller validates transport input and delegates business rules to
 * RequestWorkflowService so every entry point follows the same workflow.
 */
final class ServiceRequestWorkflowController
{
    public function __construct(private readonly RequestWorkflowService $workflow) {}

    /** Move an editable request into manager review. */
    public function submit(SubmitServiceRequestRequest $request, ServiceRequest $serviceRequest): ServiceRequestResource
    {
        return $this->respond($this->workflow->submit($serviceRequest, $request->user(), $request->validated('comment')));
    }

    /** Cancel a draft or submitted request owned by the current employee. */
    public function cancel(CancelServiceRequestRequest $request, ServiceRequest $serviceRequest): ServiceRequestResource
    {
        return $this->respond($this->workflow->cancel($serviceRequest, $request->user(), $request->validated('comment')));
    }

    /** Apply a manager's approve, reject, or return decision. */
    public function decision(DecideServiceRequestRequest $request, ServiceRequest $serviceRequest): ServiceRequestResource
    {
        return $this->respond($this->workflow->decide(
            $serviceRequest,
            $request->user(),
            ManagerDecision::from($request->validated('decision')),
            $request->validated('comment'),
        ));
    }

    /** Assign an approved or active request to an operations user. */
    public function assign(AssignServiceRequestRequest $request, ServiceRequest $serviceRequest): ServiceRequestResource
    {
        $assignee = User::query()->findOrFail($request->validated('assignee_id'));

        return $this->respond($this->workflow->assign($serviceRequest, $request->user(), $assignee, $request->validated('comment')));
    }

    /** Advance an assigned request to in-progress or completed. */
    public function status(ChangeStatusRequest $request, ServiceRequest $serviceRequest): ServiceRequestResource
    {
        return $this->respond($this->workflow->changeStatus(
            $serviceRequest,
            $request->user(),
            RequestStatus::from($request->validated('status')),
            $request->validated('comment'),
        ));
    }

    /** Return a fully hydrated resource after a workflow mutation. */
    private function respond(ServiceRequest $serviceRequest): ServiceRequestResource
    {
        return new ServiceRequestResource($serviceRequest->loadDetails());
    }
}
