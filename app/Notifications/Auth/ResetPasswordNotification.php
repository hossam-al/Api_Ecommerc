<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]);
        $expireMinutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We received a request to reset your password.')
            ->action('Reset Password', $resetUrl)
            ->line('This reset link will expire in ' . $expireMinutes . ' minutes.')
            ->line('If you did not request a password reset, you can safely ignore this email.');
    }
}
