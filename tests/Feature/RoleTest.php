<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار: المستخدم العادي لا يمكنه الوصول إلى صفحة الأدوار
     */
    public function test_regular_user_cannot_access_roles(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(403);
    }

    /**
     * اختبار: Super Admin يمكنه الوصول إلى صفحة الأدوار
     */
    public function test_super_admin_can_access_roles(): void
    {
        // إنشاء دور Super Admin
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Has all permissions'
        ]);

        // إنشاء مستخدم وتعيينه كـ Super Admin
        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id
        ]);

        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(200)
            ->assertJsonStructure(['roles']);
    }

    /**
     * اختبار: Super Admin يمكنه إنشاء دور جديد
     */
    public function test_super_admin_can_create_new_role(): void
    {
        // إنشاء دور Super Admin
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Has all permissions'
        ]);

        // إنشاء مستخدم وتعيينه كـ Super Admin
        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id
        ]);

        Sanctum::actingAs($superAdmin);

        $response = $this->postJson('/api/v1/roles', [
            'name' => 'Editor',
            'description' => 'Can edit content'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'role' => [
                    'name' => 'Editor',
                    'slug' => 'editor'
                ]
            ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Editor',
            'slug' => 'editor'
        ]);
    }

    /**
     * اختبار: Super Admin يمكنه تعيين دور لمستخدم
     */
    public function test_super_admin_can_assign_role_to_user(): void
    {
        // إنشاء دور Super Admin
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Has all permissions'
        ]);

        // إنشاء دور Admin
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Can manage own content'
        ]);

        // إنشاء مستخدم وتعيينه كـ Super Admin
        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id
        ]);

        // إنشاء مستخدم عادي
        $regularUser = User::factory()->create();

        Sanctum::actingAs($superAdmin);

        $response = $this->postJson('/api/v1/roles/assign', [
            'user_id' => $regularUser->id,
            'role_id' => $adminRole->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $regularUser->id,
            'role_id' => $adminRole->id
        ]);
    }
}
