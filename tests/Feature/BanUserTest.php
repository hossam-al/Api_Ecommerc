<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BanUserTest extends TestCase
{
    use RefreshDatabase;

    protected function createRole(string $name, string $slug): Role
    {
        return Role::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $name . ' role',
        ]);
    }

    public function test_admin_can_ban_user(): void
    {
        $adminRole = $this->createRole('Admin', 'admin');
        $customerRole = $this->createRole('Customer', 'customer');

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'phone' => '1000000001',
        ]);

        $targetUser = User::factory()->create([
            'role_id' => $customerRole->id,
            'phone' => '1000000002',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/users/' . $targetUser->id . '/ban');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'User banned successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'is_banned' => true,
        ]);
    }

    public function test_regular_user_cannot_ban_user(): void
    {
        $this->createRole('Super Admin', 'super-admin');
        $customerRole = $this->createRole('Customer', 'customer');

        $regularUser = User::factory()->create([
            'role_id' => $customerRole->id,
            'phone' => '1000000003',
        ]);

        $targetUser = User::factory()->create([
            'role_id' => $customerRole->id,
            'phone' => '1000000004',
        ]);

        Sanctum::actingAs($regularUser);

        $response = $this->postJson('/api/v1/users/' . $targetUser->id . '/ban');

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'Unauthorized. Super Admin only.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'is_banned' => false,
        ]);
    }

    public function test_banned_user_cannot_access_authenticated_routes(): void
    {
        $customerRole = $this->createRole('Customer', 'customer');

        $bannedUser = User::factory()->create([
            'role_id' => $customerRole->id,
            'phone' => '1000000005',
            'is_banned' => true,
        ]);

        Sanctum::actingAs($bannedUser);

        $response = $this->getJson('/api/user');

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'Your account has been banned.',
            ]);
    }
}
