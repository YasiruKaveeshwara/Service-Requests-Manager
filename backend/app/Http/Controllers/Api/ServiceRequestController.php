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

final class ServiceRequestController
{
    public function __construct(private readonly RequestWorkflowService $workflow) {}

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

    public function store(StoreServiceRequestRequest $request): JsonResponse
    {
        $created = $this->workflow->create($request->user(), $request->safe()->except('submit'), $request->boolean('submit'));

        return (new ServiceRequestResource($created->loadDetails()))->response()->setStatusCode(201);
    }

    public function show(ServiceRequest $serviceRequest): ServiceRequestResource
    {
        Gate::authorize('view', $serviceRequest);

        return new ServiceRequestResource($serviceRequest->loadDetails());
    }

    public function update(UpdateServiceRequestRequest $request, ServiceRequest $serviceRequest): ServiceRequestResource
    {
        return new ServiceRequestResource($this->workflow->update($serviceRequest, $request->user(), $request->validated())->loadDetails());
    }
}
