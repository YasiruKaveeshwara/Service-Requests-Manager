<?php

declare(strict_types=1);

use App\Enums\RequestNotificationType;
use App\Jobs\SendRequestNotificationsJob;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestStatusNotification;
use App\Services\NotificationRecipientResolver;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

it('sends notifications to resolved recipients', function () {
    [$manager, $employee] = notificationJobSetup();
    $request = ServiceRequest::factory()->create(['requester_id' => $employee->id]);
    Notification::fake();

    (new SendRequestNotificationsJob($request, RequestNotificationType::Submitted, $employee->id))
        ->handle(app(NotificationRecipientResolver::class));

    Notification::assertSentTo($manager, RequestStatusNotification::class);
    Notification::assertNotSentTo($employee, RequestStatusNotification::class);
});

it('stores notification data without a fake', function () {
    [$manager, $employee] = notificationJobSetup();
    $request = ServiceRequest::factory()->create(['requester_id' => $employee->id]);

    (new SendRequestNotificationsJob($request, RequestNotificationType::Submitted, $employee->id))
        ->handle(app(NotificationRecipientResolver::class));

    $notification = $manager->notifications()->first();

    expect($manager->notifications()->count())->toBe(1)
        ->and($notification->data['request_id'])->toBe($request->id)
        ->and($notification->data['message'])->toContain($request->title);
});

it('ignores a deleted actor', function () {
    [$manager, $employee] = notificationJobSetup();
    $request = ServiceRequest::factory()->create(['requester_id' => $employee->id]);
    $deletedActor = User::factory()->create();
    $deletedActorId = $deletedActor->id;
    $deletedActor->delete();

    (new SendRequestNotificationsJob($request, RequestNotificationType::Submitted, $deletedActorId))
        ->handle(app(NotificationRecipientResolver::class));

    expect($manager->notifications()->count())->toBe(0);
});

it('dispatches onto the queue', function () {
    [$manager, $employee] = notificationJobSetup();
    $request = ServiceRequest::factory()->create(['requester_id' => $employee->id]);
    Queue::fake();

    SendRequestNotificationsJob::dispatch($request, RequestNotificationType::Submitted, $employee->id);

    Queue::assertPushed(SendRequestNotificationsJob::class);
});

function notificationJobSetup(): array
{
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->reportsTo($manager)->create();

    return [$manager, $employee];
}
