<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\ListActivityRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\ServiceRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ActivityLogController
{
    public function index(ListActivityRequest $request, ServiceRequest $serviceRequest): AnonymousResourceCollection
    {
        $activity = $serviceRequest->activityLogs()
            ->with('actor')
            ->when($request->validated('event'), fn ($query, $event) => $query->where('event', $event))
            ->paginate((int) ($request->validated('per_page') ?? 25));

        return ActivityLogResource::collection($activity);
    }
}
