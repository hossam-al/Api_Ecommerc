<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تم تأكيد الحساب')
            ->greeting('مرحبًا ' . $notifiable->name . '،')
            ->line('لقد تم التأكد من الحساب بنجاح.')
            ->line('يمكنك الآن تسجيل الدخول واستخدام المنصة.');
    }
}
