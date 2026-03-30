<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_workers_can_not_access_the_user_management_index(): void
    {
        Role::create(['name' => 'Worker']);

        $worker = User::factory()->create();
        $worker->assignRole('Worker');

        $response = $this->actingAs($worker)->get(route('superadmin.users.index'));

        $response->assertForbidden();
    }

    public function test_superadmin_can_access_the_user_management_index(): void
    {
        Role::create(['name' => 'SuperAdmin']);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('SuperAdmin');

        $response = $this->actingAs($superAdmin)->get(route('superadmin.users.index'));

        $response->assertOk();
    }
}
