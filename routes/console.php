<?php

use App\Services\RegistrationNotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:send-registration-notifications {userId} {type}', function (string $userId, string $type) {
    $service = app(RegistrationNotificationService::class);

    if ($type === 'welcome') {
        $service->sendWelcomeForUserId((int) $userId);
        return;
    }

    $service->sendVerificationForUserId((int) $userId);
})->purpose('Send registration emails in a detached background process');
