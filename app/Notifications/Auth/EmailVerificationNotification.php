<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class EmailVerificationNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $expireMinutes = (int) config('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->greeting('Hello ' . ($notifiable->name ?: 'there') . ',')
            ->line('Thanks for creating your account.')
            ->line('Please verify your email address to activate your account and complete setup.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in ' . $expireMinutes . ' minutes.')
            ->line('If you did not create this account, you can safely ignore this email.');
    }

    protected function verificationUrl(object $notifiable): string
    {
        if (!$notifiable instanceof MustVerifyEmail) {
            return route('dashboard.login');
        }

        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes((int) config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
