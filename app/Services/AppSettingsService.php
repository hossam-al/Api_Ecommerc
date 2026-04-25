<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AppSettingsService
{
    private const SETTINGS = [
        'mail_mailer' => [
            'config' => ['mail.default'],
            'secret' => false,
        ],
        'mail_host' => [
            'config' => ['mail.mailers.smtp.host'],
            'secret' => false,
        ],
        'mail_port' => [
            'config' => ['mail.mailers.smtp.port'],
            'secret' => false,
            'cast' => 'int',
        ],
        'mail_username' => [
            'config' => ['mail.mailers.smtp.username'],
            'secret' => false,
        ],
        'mail_password' => [
            'config' => ['mail.mailers.smtp.password'],
            'secret' => true,
        ],
        'mail_from_address' => [
            'config' => ['mail.from.address'],
            'secret' => false,
        ],
        'mail_from_name' => [
            'config' => ['mail.from.name'],
            'secret' => false,
        ],
        'mail_scheme' => [
            'config' => ['mail.mailers.smtp.scheme'],
            'secret' => false,
        ],
        'mail_encryption' => [
            'config' => ['mail.mailers.smtp.scheme', 'mail.mailers.smtp.encryption'],
            'secret' => false,
        ],
        'cloudinary_url' => [
            'config' => ['cloudinary.cloud_url', 'filesystems.disks.cloudinary.url'],
            'secret' => true,
        ],
    ];

    public function apply(): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        AppSetting::query()
            ->whereIn('key', array_keys(self::SETTINGS))
            ->get()
            ->each(function (AppSetting $setting): void {
                $value = $this->readValue($setting);

                if ($value === null || $value === '') {
                    return;
                }

                foreach (self::SETTINGS[$setting->key]['config'] as $configKey) {
                    config([$configKey => $this->castValue($setting->key, $value)]);
                }
            });
    }

    public function set(string $key, ?string $value): AppSetting
    {
        $key = strtolower($key);

        if (! array_key_exists($key, self::SETTINGS)) {
            throw new \InvalidArgumentException("Unsupported setting key [{$key}].");
        }

        $isSecret = (bool) self::SETTINGS[$key]['secret'];

        return AppSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $isSecret && $value !== null ? Crypt::encryptString($value) : $value,
                'is_encrypted' => $isSecret,
            ],
        );
    }

    public function forget(string $key): bool
    {
        $key = strtolower($key);

        if (! array_key_exists($key, self::SETTINGS)) {
            throw new \InvalidArgumentException("Unsupported setting key [{$key}].");
        }

        return (bool) AppSetting::query()->where('key', $key)->delete();
    }

    public function allowedKeys(): array
    {
        return array_keys(self::SETTINGS);
    }

    public function maskedSettings(): array
    {
        if (! $this->settingsTableExists()) {
            return [];
        }

        return AppSetting::query()
            ->whereIn('key', array_keys(self::SETTINGS))
            ->orderBy('key')
            ->get()
            ->mapWithKeys(function (AppSetting $setting): array {
                $value = $this->readValue($setting);

                return [
                    $setting->key => $setting->is_encrypted && $value
                        ? str_repeat('*', 8)
                        : $value,
                ];
            })
            ->all();
    }

    private function readValue(AppSetting $setting): ?string
    {
        if (! $setting->is_encrypted || $setting->value === null) {
            return $setting->value;
        }

        return Crypt::decryptString($setting->value);
    }

    private function castValue(string $key, string $value): mixed
    {
        return (self::SETTINGS[$key]['cast'] ?? null) === 'int'
            ? (int) $value
            : $value;
    }

    private function settingsTableExists(): bool
    {
        try {
            return Schema::hasTable('app_settings');
        } catch (Throwable) {
            return false;
        }
    }
}
