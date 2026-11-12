<?php

declare(strict_types=1);

use App\Enums\RequestCategory;
use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Schema;

it('creates requests with enum attributes and a requester', function () {
    $request = ServiceRequest::factory()->create();

    expect($request->status)->toBe(RequestStatus::Draft)
        ->and($request->priority)->toBeInstanceOf(RequestPriority::class)
        ->and($request->category)->toBeInstanceOf(RequestCategory::class)
        ->and($request->requester)->not->toBeNull();
});

it('sets timestamps for request statuses', function () {
    $submitted = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create();
    $completed = ServiceRequest::factory()->inStatus(RequestStatus::Completed)->create();
    $draft = ServiceRequest::factory()->inStatus(RequestStatus::Draft)->create();

    expect($submitted->submitted_at)->not->toBeNull()
        ->and($completed->completed_at)->not->toBeNull()
        ->and($draft->submitted_at)->toBeNull();
});

it('protects workflow attributes from mass assignment', function () {
    $request = new ServiceRequest([
        'title' => 'Test request',
        'description' => 'Description',
        'category' => RequestCategory::It,
        'priority' => RequestPriority::High,
        'status' => RequestStatus::Completed,
        'requester_id' => 99,
    ]);

    expect($request->status)->toBe(RequestStatus::Draft)
        ->and($request->requester_id)->toBeNull();
});

it('returns comments and activity logs in id order', function () {
    $request = ServiceRequest::factory()->create();
    Comment::factory()->create(['service_request_id' => $request->id]);
    Comment::factory()->create(['service_request_id' => $request->id]);
    ActivityLog::factory()->create(['service_request_id' => $request->id]);
    ActivityLog::factory()->create(['service_request_id' => $request->id]);

    expect($request->comments->pluck('id')->values()->all())->toBe($request->comments->pluck('id')->sort()->values()->all())
        ->and($request->activityLogs->pluck('id')->values()->all())->toBe($request->activityLogs->pluck('id')->sort()->values()->all());
});

it('cascades related records when a request is deleted', function () {
    $request = ServiceRequest::factory()->create();
    Comment::factory()->create(['service_request_id' => $request->id]);
    ActivityLog::factory()->create(['service_request_id' => $request->id]);

    $request->delete();

    expect(Comment::query()->count())->toBe(0)
        ->and(ActivityLog::query()->count())->toBe(0);
});

it('creates the required persistence schema', function () {
    expect(Schema::hasColumns('service_requests', ['title', 'description', 'category', 'priority', 'status', 'requester_id', 'assignee_id', 'submitted_at', 'completed_at']))->toBeTrue()
        ->and(Schema::hasColumns('comments', ['service_request_id', 'user_id', 'body']))->toBeTrue()
        ->and(Schema::hasColumns('activity_logs', ['service_request_id', 'actor_id', 'event', 'from_status', 'to_status', 'comment', 'metadata', 'created_at']))->toBeTrue();

    $indexes = collect(Schema::getIndexes('service_requests'));

    expect($indexes->contains(fn (array $index): bool => $index['columns'] === ['status']))->toBeTrue();
});
