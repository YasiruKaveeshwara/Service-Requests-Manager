<?php

declare(strict_types=1);

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake();
});

it('seeds the demo users and manager hierarchy', function () {
    $this->seed();

    expect(User::query()->count())->toBe(7)
        ->and(User::query()->where('role', 'employee')->count())->toBe(3)
        ->and(User::query()->where('role', 'manager')->count())->toBe(2)
        ->and(User::query()->where('role', 'operations')->count())->toBe(2)
        ->and(User::query()->where('email', 'manager@quantix.test')->firstOrFail()->directReports)->toHaveCount(2)
        ->and(User::query()->where('email', 'manager2@quantix.test')->firstOrFail()->directReports)->toHaveCount(1);
});

it('seeds eleven requests across every status with audit history', function () {
    $this->seed();

    expect(ServiceRequest::query()->count())->toBe(11)
        ->and(ServiceRequest::query()->whereHas('activityLogs')->count())->toBe(11)
        ->and(ServiceRequest::query()->where('status', RequestStatus::Completed->value)->firstOrFail()->activityLogs)->toHaveCount(6)
        ->and(ServiceRequest::query()->where('title', 'Laptop battery replacement')->firstOrFail()->comments)->toHaveCount(2);
});

it('matches the requested status distribution and supports seeded login', function () {
    $this->seed();

    expect(ServiceRequest::query()->where('status', RequestStatus::Draft->value)->count())->toBe(1)
        ->and(ServiceRequest::query()->where('status', RequestStatus::Submitted->value)->count())->toBe(3)
        ->and(ServiceRequest::query()->where('status', RequestStatus::Returned->value)->count())->toBe(1)
        ->and(ServiceRequest::query()->where('status', RequestStatus::Approved->value)->count())->toBe(2)
        ->and(ServiceRequest::query()->where('status', RequestStatus::InProgress->value)->count())->toBe(1)
        ->and(ServiceRequest::query()->where('status', RequestStatus::Completed->value)->count())->toBe(1)
        ->and(ServiceRequest::query()->where('status', RequestStatus::Rejected->value)->count())->toBe(1)
        ->and(ServiceRequest::query()->where('status', RequestStatus::Cancelled->value)->count())->toBe(1);

    $this->postJson('/api/login', ['email' => 'employee1@quantix.test', 'password' => 'password'])
        ->assertOk();
});
