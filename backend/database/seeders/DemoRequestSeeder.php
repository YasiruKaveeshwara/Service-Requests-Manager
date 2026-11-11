<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ManagerDecision;
use App\Enums\RequestCategory;
use App\Enums\RequestPriority;
use App\Models\User;
use App\Services\CommentService;
use App\Services\RequestWorkflowService;
use Illuminate\Database\Seeder;

final class DemoRequestSeeder extends Seeder
{
    public function run(RequestWorkflowService $workflow, CommentService $comments): void
    {
        $maya = User::query()->where('email', 'manager@quantix.test')->firstOrFail();
        $marcus = User::query()->where('email', 'manager2@quantix.test')->firstOrFail();
        $ethan = User::query()->where('email', 'employee1@quantix.test')->firstOrFail();
        $emma = User::query()->where('email', 'employee2@quantix.test')->firstOrFail();
        $ella = User::query()->where('email', 'employee3@quantix.test')->firstOrFail();
        $olivia = User::query()->where('email', 'ops1@quantix.test')->firstOrFail();
        $oscar = User::query()->where('email', 'ops2@quantix.test')->firstOrFail();

        $make = function (User $requester, string $title, RequestCategory $category, RequestPriority $priority, string $description) use ($workflow): \App\Models\ServiceRequest {
            return $workflow->create($requester, [
                'title' => $title,
                'description' => $description,
                'category' => $category->value,
                'priority' => $priority->value,
            ]);
        };

        $make($ethan, 'Standing desk converter', RequestCategory::Equipment, RequestPriority::Low, 'A converter for the existing desk.');

        $request = $workflow->create($ethan, ['title' => 'VPN access for contractor laptop', 'description' => 'VPN access is needed.', 'category' => 'access', 'priority' => 'high'], true);
        $request = $workflow->create($emma, ['title' => 'Replace broken chair in Room 4B', 'description' => 'The chair is broken.', 'category' => 'maintenance', 'priority' => 'medium'], true);
        $request = $workflow->create($emma, ['title' => 'New monitor for design work', 'description' => 'A monitor is needed.', 'category' => 'equipment', 'priority' => 'medium'], true);
        $workflow->decide($request, $maya, ManagerDecision::SendBack, 'Please add the model and budget.');

        $request = $workflow->create($ethan, ['title' => 'Install Postgres client on dev laptop', 'description' => 'Install the database client.', 'category' => 'it', 'priority' => 'medium'], true);
        $workflow->decide($request, $maya, ManagerDecision::Approve);

        $request = $workflow->create($emma, ['title' => 'Access to shared finance drive', 'description' => 'Finance drive access is needed.', 'category' => 'access', 'priority' => 'high'], true);
        $workflow->decide($request, $maya, ManagerDecision::Approve);
        $workflow->assign($request, $maya, $olivia);

        $request = $workflow->create($ethan, ['title' => 'Laptop battery replacement', 'description' => 'The laptop battery is failing.', 'category' => 'equipment', 'priority' => 'high'], true);
        $workflow->decide($request, $maya, ManagerDecision::Approve);
        $workflow->assign($request, $maya, $olivia);
        $workflow->changeStatus($request, $olivia, \App\Enums\RequestStatus::InProgress);
        $comments->add($request, $ethan, 'Any update on the ETA?');
        $comments->add($request, $olivia, 'Battery arrives Thursday.');

        $request = $workflow->create($emma, ['title' => 'Fix flickering light in kitchen', 'description' => 'The kitchen light flickers.', 'category' => 'maintenance', 'priority' => 'low'], true);
        $workflow->decide($request, $maya, ManagerDecision::Approve);
        $workflow->assign($request, $maya, $oscar);
        $workflow->changeStatus($request, $oscar, \App\Enums\RequestStatus::InProgress);
        $workflow->changeStatus($request, $oscar, \App\Enums\RequestStatus::Completed, 'Replaced the tube.');

        $request = $workflow->create($ethan, ['title' => 'Office espresso machine', 'description' => 'An espresso machine for the office.', 'category' => 'equipment', 'priority' => 'low'], true);
        $workflow->decide($request, $maya, ManagerDecision::Reject, 'Out of budget this quarter.');

        $request = $workflow->create($emma, ['title' => 'Second keyboard', 'description' => 'A second keyboard is no longer needed.', 'category' => 'equipment', 'priority' => 'low'], true);
        $workflow->cancel($request, $emma, 'No longer needed.');

        $workflow->create($ella, ['title' => 'Parking access card', 'description' => 'Parking access is needed.', 'category' => 'access', 'priority' => 'medium'], true);
    }
}
