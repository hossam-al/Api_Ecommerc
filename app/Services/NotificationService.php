<?php

namespace App\Services;

use App\Http\Resources\SellerNotificationResource;
use App\Models\User;
use App\Support\ApiResponseBuilder;
use Illuminate\Http\Request;

class NotificationService
{
    public function sellerIndex(Request $request, ?User $seller): array
    {
        if (!$this->isSeller($seller)) {
            return ApiResponseBuilder::error('Only Seller accounts can view seller notifications', 403);
        }

        return $this->buildNotificationIndexResponse($request, $seller, 'Seller notifications retrieved successfully');
    }

    public function adminIndex(Request $request, ?User $admin): array
    {
        if (!$this->isAdmin($admin)) {
            return ApiResponseBuilder::error('Only Admin accounts can view admin notifications', 403);
        }

        return $this->buildNotificationIndexResponse($request, $admin, 'Admin notifications retrieved successfully');
    }

    public function markAsRead(string $notificationId, ?User $seller): array
    {
        if (!$this->canAccessNotifications($seller)) {
            return ApiResponseBuilder::error('Only Seller or Admin accounts can update notifications', 403);
        }

        $notification = $seller->notifications()
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return ApiResponseBuilder::error('Notification not found', 404);
        }

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return ApiResponseBuilder::success(
            'Seller notification marked as read successfully',
            (new SellerNotificationResource($notification->fresh()))->resolve(),
        );
    }

    public function markAllAsRead(?User $seller): array
    {
        if (!$this->canAccessNotifications($seller)) {
            return ApiResponseBuilder::error('Only Seller or Admin accounts can update notifications', 403);
        }

        $unreadNotifications = $seller->unreadNotifications;
        $markedCount = $unreadNotifications->count();

        if ($markedCount > 0) {
            $unreadNotifications->markAsRead();
        }

        return ApiResponseBuilder::success(
            'Seller notifications marked as read successfully',
            [
                'marked_count' => $markedCount,
                'unread_count' => 0,
            ],
        );
    }

    public function delete(string $notificationId, ?User $user): array
    {
        if (!$this->canAccessNotifications($user)) {
            return ApiResponseBuilder::error('Only Seller or Admin accounts can delete notifications', 403);
        }

        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return ApiResponseBuilder::error('Notification not found', 404);
        }

        $notification->delete();

        return ApiResponseBuilder::success(
            'Notification deleted successfully',
            [
                'deleted_id' => $notificationId,
                'summary' => [
                    'unread_count' => $user->unreadNotifications()->count(),
                    'total_count' => (int) $user->notifications()->count(),
                ],
            ],
        );
    }

    public function deleteAll(?User $user): array
    {
        if (!$this->canAccessNotifications($user)) {
            return ApiResponseBuilder::error('Only Seller or Admin accounts can delete notifications', 403);
        }

        $notificationsQuery = $user->notifications();
        $deletedCount = (clone $notificationsQuery)->count();

        if ($deletedCount > 0) {
            $notificationsQuery->delete();
        }

        return ApiResponseBuilder::success(
            'Notifications deleted successfully',
            [
                'deleted_count' => $deletedCount,
                'summary' => [
                    'unread_count' => 0,
                    'total_count' => 0,
                ],
            ],
        );
    }

    protected function isSeller(?User $user): bool
    {
        return $user && (int) $user->role_id === 2;
    }

    protected function isAdmin(?User $user): bool
    {
        return $user && (int) $user->role_id === 1;
    }

    protected function canAccessNotifications(?User $user): bool
    {
        return $this->isSeller($user) || $this->isAdmin($user);
    }

    protected function buildNotificationIndexResponse(Request $request, User $user, string $message): array
    {
        $query = $user->notifications()->latest();

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $perPage = $this->resolvePerPage($request);
        $notifications = $query->paginate($perPage);
        $unreadCount = $user->unreadNotifications()->count();

        return ApiResponseBuilder::success(
            $message,
            SellerNotificationResource::collection($notifications)->response()->getData(true),
            200,
            [
                'summary' => [
                    'unread_count' => $unreadCount,
                    'total_count' => (int) $user->notifications()->count(),
                ],
                'filters' => [
                    'unread_only' => $request->boolean('unread_only'),
                    'per_page' => $perPage,
                ],
            ],
        );
    }

    protected function resolvePerPage(Request $request): int
    {
        return max(1, min((int) $request->integer('per_page', 10), 20));
    }
}
