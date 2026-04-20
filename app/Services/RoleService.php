<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Support\ApiResponseBuilder;
use Illuminate\Support\Str;

class RoleService
{
    public function index(?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Unauthorized', 403);
        }

        $roles = Role::all();

        return ApiResponseBuilder::success('Roles retrieved successfully', $roles, 200, [
            'roles' => $roles,
        ]);
    }

    public function store(array $validated, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Unauthorized', 403);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return ApiResponseBuilder::success('Role created successfully', $role, 201, [
            'success' => true,
            'role' => $role,
        ]);
    }

    public function show(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Unauthorized', 403);
        }

        $role = Role::find($id);

        if (!$role) {
            return ApiResponseBuilder::error('Role not found', 404);
        }

        $users = User::where('role_id', $role->id)->get(['id', 'name', 'email', 'is_banned']);

        return ApiResponseBuilder::success('Role retrieved successfully', [
            'role' => $role,
            'users' => $users,
        ], 200, [
            'role' => $role,
            'users' => $users,
        ]);
    }

    public function update(int|string $id, array $validated, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Unauthorized', 403);
        }

        $role = Role::find($id);

        if (!$role) {
            return ApiResponseBuilder::error('Role not found', 404);
        }

        if ($role->slug === 'admin' && array_key_exists('name', $validated)) {
            return ApiResponseBuilder::error('Cannot modify the Admin role name', 422, [
                'error' => 'Cannot modify the Admin role name',
            ]);
        }

        $role->update([
            'name' => $validated['name'] ?? $role->name,
            'description' => $validated['description'] ?? $role->description,
            'slug' => array_key_exists('name', $validated) ? Str::slug($validated['name']) : $role->slug,
        ]);

        return ApiResponseBuilder::success('Role updated successfully', $role, 200, [
            'success' => true,
            'role' => $role,
        ]);
    }

    public function destroy(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Unauthorized', 403);
        }

        $role = Role::find($id);

        if (!$role) {
            return ApiResponseBuilder::error('Role not found', 404);
        }

        if (in_array($role->slug, ['admin', 'seller', 'customer'], true)) {
            return ApiResponseBuilder::error('Cannot delete system roles', 422, [
                'error' => 'Cannot delete system roles',
            ]);
        }

        $usersCount = User::where('role_id', $role->id)->count();

        if ($usersCount > 0) {
            return ApiResponseBuilder::error('Cannot delete role with assigned users', 422, [
                'error' => 'Cannot delete role with assigned users',
                'users_count' => $usersCount,
            ]);
        }

        $role->delete();

        return ApiResponseBuilder::success('Role deleted successfully', null, 200, [
            'success' => true,
        ]);
    }

    public function assignRole(array $validated, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Unauthorized', 403);
        }

        $targetUser = User::find($validated['user_id']);
        $role = Role::find($validated['role_id']);

        if (!$targetUser) {
            return ApiResponseBuilder::error('User not found', 404);
        }

        if (!$role) {
            return ApiResponseBuilder::error('Role not found', 404);
        }
        $targetUser->update([
            'role_id' => $role->id,
            'seller_status' => $role->slug === 'seller' ? 'pending_review' : 'approved',
        ]);

        return ApiResponseBuilder::success(
            "Role {$role->name} assigned to {$targetUser->name} successfully",
            null,
            200,
            ['success' => true],
        );
    }

    protected function isAdmin(?User $user): bool
    {
        return $user && (int) $user->role_id === 1;
    }
}
