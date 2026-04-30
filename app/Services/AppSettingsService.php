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
        'dashboard_url' => [
            'config' => [],
            'secret' => false,
        ],
    ];

    public function apply(): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        $settings = AppSetting::query()
            ->whereIn('key', array_keys(self::SETTINGS))
            ->get()
            ->keyBy('key');

        $settings
            ->except(['mail_scheme', 'mail_encryption'])
            ->each(function (AppSetting $setting): void {
                $value = $this->readValue($setting);

                if ($value === null || $value === '') {
                    return;
                }

                foreach (self::SETTINGS[$setting->key]['config'] as $configKey) {
                    config([$configKey => $this->castValue($setting->key, $value)]);
                }
            });

        if ($settings->has('mail_scheme')) {
            $value = $this->readValue($settings->get('mail_scheme'));

            if ($value !== null && $value !== '') {
                $this->applyMailScheme($value);
            }
        }

        if ($settings->has('mail_encryption')) {
            $value = $this->readValue($settings->get('mail_encryption'));

            if ($value !== null && $value !== '') {
                $this->applyMailEncryption($value);
            }
        }
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

    public function get(string $key): ?string
    {
        $key = strtolower($key);

        if (! array_key_exists($key, self::SETTINGS) || ! $this->settingsTableExists()) {
            return null;
        }

        $setting = AppSetting::query()->where('key', $key)->first();

        return $setting ? $this->readValue($setting) : null;
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

    private function applyMailEncryption(string $value): void
    {
        $value = strtolower($value);

        if (in_array($value, ['ssl', 'smtps'], true)) {
            config([
                'mail.mailers.smtp.scheme' => 'smtps',
                'mail.mailers.smtp.encryption' => 'ssl',
                'mail.mailers.smtp.require_tls' => false,
            ]);
            return;
        }

        if ($value === 'tls') {
            config([
                'mail.mailers.smtp.scheme' => 'smtp',
                'mail.mailers.smtp.encryption' => 'tls',
                'mail.mailers.smtp.require_tls' => true,
            ]);
            return;
        }

        config([
            'mail.mailers.smtp.scheme' => 'smtp',
            'mail.mailers.smtp.encryption' => null,
            'mail.mailers.smtp.require_tls' => false,
        ]);
    }

    private function applyMailScheme(string $value): void
    {
        $value = strtolower($value);

        if ($value === 'tls') {
            $this->applyMailEncryption('tls');
            return;
        }

        if (in_array($value, ['ssl', 'smtps'], true)) {
            $this->applyMailEncryption('smtps');
            return;
        }

        config([
            'mail.mailers.smtp.scheme' => 'smtp',
        ]);
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
