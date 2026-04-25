<?php

namespace App\Services;

use App\Models\User;
use App\Support\ApiResponseBuilder;

class UserService
{
    public function listUsers(array $filters, ?User $currentUser): array
    {
        if (!$this->canManageUsers($currentUser)) {
            return ApiResponseBuilder::error('Only Admin accounts can view users.', 403);
        }

        $perPage = max(1, min((int) ($filters['per_page'] ?? 10), 50));

        $query = User::with('role:id,name,slug')
            ->select([
                'id', 'name', 'email', 'phone', 'image_url', 'role_id', 'is_banned', 'created_at',
            ])->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role_id'])) {
            $query->where('role_id', (int) $filters['role_id']);
        }

        if (array_key_exists('is_banned', $filters) && $filters['is_banned'] !== null) {
            $query->where('is_banned', filter_var($filters['is_banned'], FILTER_VALIDATE_BOOLEAN));
        }

        return ApiResponseBuilder::success('Users retrieved successfully', $query->paginate($perPage));
    }

    public function banUser(int|string $id, ?User $currentUser): array
    {
        if (!$this->canManageUsers($currentUser)) {
            return ApiResponseBuilder::error('Only admin users can ban accounts.', 403);
        }

        $user = User::find($id);

        if (!$user) {
            return ApiResponseBuilder::error('User not found', 404);
        }

        if ((int) $currentUser->id === (int) $user->id) {
            return ApiResponseBuilder::error('You cannot ban your own account.', 422);
        }

        if ($user->is_banned) {
            return ApiResponseBuilder::success('User is already banned.', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_banned' => true,
            ]);
        }

        $user->update([
            'is_banned' => true,
            'seller_status' => 'banned',
        ]);
        $user->tokens()->delete();

        return ApiResponseBuilder::success('User banned successfully', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_banned' => $user->is_banned,
        ]);
    }

    public function unbanUser(int|string $id, ?User $currentUser): array
    {
        if (!$this->canManageUsers($currentUser)) {
            return ApiResponseBuilder::error('Only admin users can unban accounts.', 403);
        }

        $user = User::find($id);

        if (!$user) {
            return ApiResponseBuilder::error('User not found', 404);
        }

        if (!$user->is_banned) {
            return ApiResponseBuilder::success('User is already active.', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_banned' => false,
            ]);
        }

        $user->update([
            'is_banned' => false,
            'seller_status' => 'approved',
        ]);

        return ApiResponseBuilder::success('User unbanned successfully', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_banned' => $user->is_banned,
        ]);
    }

    protected function canManageUsers(?User $user): bool
    {
        return $user && (int) $user->role_id === 1;
    }
}
