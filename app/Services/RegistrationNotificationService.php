<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\Auth\WelcomeNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegistrationNotificationService
{
    public function sendVerificationForUserId(int $userId): void
    {
        $user = User::find($userId);

        if (!$user) {
            return;
        }

        $this->sendVerification($user);
    }

    public function sendWelcomeForUserId(int $userId): void
    {
        $user = User::find($userId);

        if (!$user) {
            return;
        }

        $this->sendWelcome($user);
    }

    public function sendVerification(User $user): void
    {
        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::error('Failed to send email verification notification in background process.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function sendWelcome(User $user): void
    {
        try {
            $user->notify(new WelcomeNotification());
        } catch (Throwable $exception) {
            Log::error('Failed to send welcome notification in background process.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
