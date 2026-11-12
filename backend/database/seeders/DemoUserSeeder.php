<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $maya = User::factory()->manager()->create([
            'name' => 'Maya Manager',
            'email' => 'manager@service-requests.test',
        ]);
        $marcus = User::factory()->manager()->create([
            'name' => 'Marcus Manager',
            'email' => 'manager2@service-requests.test',
        ]);

        User::factory()->employee()->reportsTo($maya)->create([
            'name' => 'Ethan Employee',
            'email' => 'employee1@service-requests.test',
        ]);
        User::factory()->employee()->reportsTo($maya)->create([
            'name' => 'Emma Employee',
            'email' => 'employee2@service-requests.test',
        ]);
        User::factory()->employee()->reportsTo($marcus)->create([
            'name' => 'Ella Employee',
            'email' => 'employee3@service-requests.test',
        ]);

        User::factory()->operations()->create([
            'name' => 'Olivia Ops',
            'email' => 'ops1@service-requests.test',
        ]);
        User::factory()->operations()->create([
            'name' => 'Oscar Ops',
            'email' => 'ops2@service-requests.test',
        ]);
    }
}
