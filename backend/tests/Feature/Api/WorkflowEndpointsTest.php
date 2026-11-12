<?php

declare(strict_types=1);

use App\Enums\ManagerDecision;
use App\Enums\RequestNotificationType;
use App\Enums\RequestStatus;
use App\Jobs\SendRequestNotificationsJob;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    Queue::fake();
});

it('submits drafts and returned requests', function (RequestStatus $status) {
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->reportsTo($manager)->create();
    $request = ServiceRequest::factory()->inStatus($status)->create(['requester_id' => $employee->id]);
    Sanctum::actingAs($employee);

    $this->postJson("/api/requests/{$request->id}/submit", ['comment' => 'Review'])
        ->assertOk()
        ->assertJsonPath('data.status', 'submitted');
})->with([RequestStatus::Draft, RequestStatus::Returned]);

it('cancels draft submitted and returned requests', function (RequestStatus $status) {
    $employee = User::factory()->reportsTo(User::factory()->manager()->create())->create();
    $request = ServiceRequest::factory()->inStatus($status)->create(['requester_id' => $employee->id]);
    Sanctum::actingAs($employee);

    $this->postJson("/api/requests/{$request->id}/cancel")->assertOk()->assertJsonPath('data.status', 'cancelled');
})->with([RequestStatus::Draft, RequestStatus::Submitted, RequestStatus::Returned]);

it('decides submitted requests', function (ManagerDecision $decision, string $comment, RequestStatus $status) {
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->reportsTo($manager)->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $employee->id]);
    Sanctum::actingAs($manager);

    $this->postJson("/api/requests/{$request->id}/decision", ['decision' => $decision->value, 'comment' => $comment])
        ->assertOk()
        ->assertJsonPath('data.status', $status->value);
})->with([
    [ManagerDecision::Approve, '', RequestStatus::Approved],
    [ManagerDecision::Reject, 'No access needed.', RequestStatus::Rejected],
    [ManagerDecision::SendBack, 'Please revise.', RequestStatus::Returned],
]);

it('assigns, starts and completes an approved request', function () {
    $operations = User::factory()->operations()->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Approved)->create();
    Sanctum::actingAs($operations);

    $this->postJson("/api/requests/{$request->id}/assign", ['assignee_id' => $operations->id])
        ->assertOk()
        ->assertJsonPath('data.assignee.id', $operations->id);
    $this->postJson("/api/requests/{$request->id}/status", ['status' => 'in_progress'])
        ->assertOk()
        ->assertJsonPath('data.status', 'in_progress');
    $this->postJson("/api/requests/{$request->id}/status", ['status' => 'completed', 'comment' => 'Delivered'])
        ->assertOk()
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.completed_at', fn ($value) => $value !== null);
});

it('rejects another employee submitting a request', function () {
    $owner = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $owner->id]);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson("/api/requests/{$request->id}/submit")
        ->assertForbidden()
        ->assertJsonPath('code', 'forbidden');
});

it('rejects managers submitting and cancelling requests', function (string $action) {
    $owner = User::factory()->create();
    $request = ServiceRequest::factory()->create(['requester_id' => $owner->id]);
    Sanctum::actingAs(User::factory()->manager()->create());

    $this->postJson("/api/requests/{$request->id}/{$action}")
        ->assertForbidden()
        ->assertJsonPath('code', 'forbidden');
})->with(['submit', 'cancel']);

it('rejects unauthorized workflow actions', function () {
    $actor = User::factory()->create();
    $request = ServiceRequest::factory()->create();
    Sanctum::actingAs($actor);

    $this->postJson("/api/requests/{$request->id}/cancel")
        ->assertForbidden()
        ->assertJsonPath('code', 'forbidden');
});

it('returns validation errors for invalid decisions and assignments', function () {
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->reportsTo($manager)->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Submitted)->create(['requester_id' => $employee->id]);
    Sanctum::actingAs($manager);

    $this->postJson("/api/requests/{$request->id}/decision", ['decision' => 'reject'])
        ->assertStatus(422)
        ->assertJsonPath('code', 'validation_failed');
    $this->postJson("/api/requests/{$request->id}/decision", ['decision' => 'unknown'])
        ->assertStatus(422);
    $this->postJson("/api/requests/{$request->id}/assign", ['assignee_id' => $manager->id])
        ->assertStatus(403);
});

it('dispatches notifications for successful workflow actions', function () {
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->reportsTo($manager)->create();
    $request = ServiceRequest::factory()->inStatus(RequestStatus::Draft)->create(['requester_id' => $employee->id]);
    Sanctum::actingAs($employee);
    $this->postJson("/api/requests/{$request->id}/submit")->assertOk();

    Queue::assertPushed(SendRequestNotificationsJob::class, fn (SendRequestNotificationsJob $job): bool => $job->type === RequestNotificationType::Submitted);
});
