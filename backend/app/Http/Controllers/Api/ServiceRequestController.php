<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\ListServiceRequestsRequest;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Http\Requests\UpdateServiceRequestRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use App\Services\RequestWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/**
 * Provides CRUD endpoints for service requests.
 *
 * State-changing workflow actions are intentionally delegated to the
 * ServiceRequestWorkflowController and RequestWorkflowService.
 */
final class ServiceRequestController
{
    public function __construct(private readonly RequestWorkflowService $workflow) {}

    /** Return the authenticated user's authorized, filtered request list. */
    public function index(ListServiceRequestsRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $requests = ServiceRequest::query()
            ->visibleTo($request->user())
            ->filter($filters)
            ->with(['requester', 'assignee'])
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();

        return ServiceRequestResource::collection($requests);
    }

    /** Create a draft or immediately submit a request for review. */
    public function store(StoreServiceRequestRequest $request): JsonResponse
    {
        $created = $this->workflow->create($request->user(), $request->safe()->except('submit'), $request->boolean('submit'));

        return (new ServiceRequestResource($created->loadDetails()))->response()->setStatusCode(201);
    }

    /** Return one request after applying its view policy. */
    public function show(ServiceRequest $serviceRequest): ServiceRequestResource
    {
        Gate::authorize('view', $serviceRequest);

        return new ServiceRequestResource($serviceRequest->loadDetails());
    }

    /** Update editable request fields through the workflow service. */
    public function update(UpdateServiceRequestRequest $request, ServiceRequest $serviceRequest): ServiceRequestResource
    {
        return new ServiceRequestResource($this->workflow->update($serviceRequest, $request->user(), $request->validated())->loadDetails());
    }
}
