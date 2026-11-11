<?php

declare(strict_types=1);

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

function makeManager(): User
{
    return User::factory()->manager()->create();
}

function makeEmployee(?User $manager = null): User
{
    return User::factory()->employee()->reportsTo($manager ?? makeManager())->create();
}

function makeOperations(): User
{
    return User::factory()->operations()->create();
}

function makeRequest(User $requester, RequestStatus $status = RequestStatus::Draft, array $attributes = []): ServiceRequest
{
    return ServiceRequest::factory()->for($requester, 'requester')->inStatus($status)->create($attributes);
}
