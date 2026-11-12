<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RequestCategory;
use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(RequestCategory::cases()),
            'priority' => fake()->randomElement(RequestPriority::cases()),
            'status' => RequestStatus::Draft,
            'requester_id' => User::factory(),
            'assignee_id' => null,
            'submitted_at' => null,
            'completed_at' => null,
        ];
    }

    public function inStatus(RequestStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'submitted_at' => $status === RequestStatus::Draft ? null : now()->subDays(2),
            'completed_at' => $status === RequestStatus::Completed ? now() : null,
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => ['assignee_id' => $user->id]);
    }
}
