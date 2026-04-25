<?php

use App\Services\RegistrationNotificationService;
use App\Services\AppSettingsService;
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

Artisan::command('settings:set {key} {value}', function (string $key, string $value) {
    app(AppSettingsService::class)->set($key, $value);

    $this->info("Setting [{$key}] was saved.");
})->purpose('Store an application setting in the database');

Artisan::command('settings:forget {key}', function (string $key) {
    app(AppSettingsService::class)->forget($key);

    $this->info("Setting [{$key}] was removed.");
})->purpose('Remove an application setting from the database');

Artisan::command('settings:list', function () {
    $service = app(AppSettingsService::class);
    $settings = $service->maskedSettings();

    $this->table(
        ['Key', 'Value'],
        collect($service->allowedKeys())->map(fn (string $key): array => [
            $key,
            $settings[$key] ?? '(env/default)',
        ])->all(),
    );
})->purpose('List configurable application settings');
