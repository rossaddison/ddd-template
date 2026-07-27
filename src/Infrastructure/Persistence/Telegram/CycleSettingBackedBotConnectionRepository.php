<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Telegram;

use App\Domain\Setting\Setting as DomainSetting;
use App\Domain\Setting\SettingKey;
use App\Domain\Setting\SettingRepositoryInterface;
use App\Domain\Telegram\BotConnection;
use App\Domain\Telegram\BotConnectionRepositoryInterface;
use App\Domain\Telegram\BotCredentials;

/**
 * Implements BotConnectionRepositoryInterface by composing calls to the
 * generic SettingRepositoryInterface — token/chatId/webhookSecret/enabled
 * are just four setting rows under the hood. Application-layer code never
 * knows this; it only sees a BotConnection aggregate. Deliberately depends
 * on the Domain-level SettingRepositoryInterface, not the Application-layer
 * UpdateSetting use-case — one Infrastructure implementation composing
 * another Domain port is the correct dependency direction.
 */
final readonly class CycleSettingBackedBotConnectionRepository implements BotConnectionRepositoryInterface
{
    private const string KEY_TOKEN = 'telegram_token';
    private const string KEY_CHAT_ID = 'telegram_chat_id';
    private const string KEY_WEBHOOK_SECRET = 'telegram_webhook_secret_token';
    private const string KEY_ENABLED = 'telegram_bot_enabled';

    public function __construct(private SettingRepositoryInterface $settings)
    {
    }

    #[\Override]
    public function get(): BotConnection
    {
        $token = $this->readValue(self::KEY_TOKEN);
        $chatId = $this->readValue(self::KEY_CHAT_ID);
        $webhookSecret = $this->readValue(self::KEY_WEBHOOK_SECRET);
        $enabled = $this->readValue(self::KEY_ENABLED) === '1';

        $credentials = $token === null ? null : new BotCredentials($token, $chatId, $webhookSecret);

        return new BotConnection($credentials, $enabled);
    }

    #[\Override]
    public function save(BotConnection $connection): void
    {
        $credentials = $connection->credentials();
        $this->writeValue(self::KEY_TOKEN, $credentials?->token() ?? '');
        $this->writeValue(self::KEY_CHAT_ID, $credentials?->chatId() ?? '');
        $this->writeValue(self::KEY_WEBHOOK_SECRET, $credentials?->webhookSecret() ?? '');
        $this->writeValue(self::KEY_ENABLED, $connection->isEnabled() ? '1' : '0');
    }

    private function readValue(string $key): ?string
    {
        $value = $this->settings->find(new SettingKey($key))?->value();
        return $value === null || $value === '' ? null : $value;
    }

    private function writeValue(string $key, string $value): void
    {
        $settingKey = new SettingKey($key);
        $existing = $this->settings->find($settingKey);
        $setting = $existing?->withValue($value) ?? new DomainSetting($settingKey, $value);
        $this->settings->save($setting);
    }
}
