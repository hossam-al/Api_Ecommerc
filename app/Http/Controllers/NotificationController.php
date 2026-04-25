<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    public function sellerIndex(Request $request)
    {
        return $this->respond($this->notificationService->sellerIndex($request, $request->user()));
    }

    public function adminIndex(Request $request)
    {
        return $this->respond($this->notificationService->adminIndex($request, $request->user()));
    }

    public function markAsRead(string $id, Request $request)
    {
        return $this->respond($this->notificationService->markAsRead($id, $request->user()));
    }

    public function markAllAsRead(Request $request)
    {
        return $this->respond($this->notificationService->markAllAsRead($request->user()));
    }

    public function destroy(string $id, Request $request)
    {
        return $this->respond($this->notificationService->delete($id, $request->user()));
    }

    public function destroyAll(Request $request)
    {
        return $this->respond($this->notificationService->deleteAll($request->user()));
    }
}
