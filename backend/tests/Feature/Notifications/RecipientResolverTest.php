<?php

declare(strict_types=1);

use App\Enums\RequestNotificationType;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\NotificationRecipientResolver;

function notificationSetup(): array
{
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->reportsTo($manager)->create();
    $ops1 = User::factory()->operations()->create();
    $ops2 = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $employee->id]);

    return [$manager, $employee, $ops1, $ops2, $request];
}

it('resolves the manager for submitted requests', function () {
    [$manager, $employee, $ops1, $ops2, $request] = notificationSetup();

    expect(app(NotificationRecipientResolver::class)->resolve($request, RequestNotificationType::Submitted, $employee)->pluck('id')->all())
        ->toBe([$manager->id]);
});

it('resolves the manager for submitted cancellations', function () {
    [$manager, $employee, $ops1, $ops2, $request] = notificationSetup();
    $request->forceFill(['submitted_at' => now()])->save();

    expect(app(NotificationRecipientResolver::class)->resolve($request, RequestNotificationType::Cancelled, $employee)->pluck('id')->all())
        ->toBe([$manager->id]);
});

it('does not notify the manager for draft cancellations', function () {
    [$manager, $employee, $ops1, $ops2, $request] = notificationSetup();

    expect(app(NotificationRecipientResolver::class)->resolve($request, RequestNotificationType::Cancelled, $employee))->toBeEmpty();
});

it('resolves the requester and operations users for approvals', function () {
    [$manager, $employee, $ops1, $ops2, $request] = notificationSetup();

    expect(app(NotificationRecipientResolver::class)->resolve($request, RequestNotificationType::Approved, $manager)->pluck('id')->all())
        ->toBe([$employee->id, $ops1->id, $ops2->id]);
});

it('resolves the requester for terminal and return events', function (RequestNotificationType $type) {
    [$manager, $employee, $ops1, $ops2, $request] = notificationSetup();

    expect(app(NotificationRecipientResolver::class)->resolve($request, $type, $manager)->pluck('id')->all())
        ->toBe([$employee->id]);
})->with([
    RequestNotificationType::Rejected,
    RequestNotificationType::Returned,
    RequestNotificationType::Started,
    RequestNotificationType::Completed,
]);

it('resolves the assignee and requester for assignments', function () {
    [$manager, $employee, $ops1, $ops2, $request] = notificationSetup();
    $request->forceFill(['assignee_id' => $ops1->id])->save();

    expect(app(NotificationRecipientResolver::class)->resolve($request, RequestNotificationType::Assigned, $ops2)->pluck('id')->all())
        ->toBe([$ops1->id, $employee->id]);
});

it('excludes the assignee from assignment notifications', function () {
    [$manager, $employee, $ops1, $ops2, $request] = notificationSetup();
    $request->forceFill(['assignee_id' => $ops1->id])->save();

    expect(app(NotificationRecipientResolver::class)->resolve($request, RequestNotificationType::Assigned, $ops1)->pluck('id')->all())
        ->toBe([$employee->id]);
});
