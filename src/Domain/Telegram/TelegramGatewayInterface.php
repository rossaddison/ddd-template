<?php

declare(strict_types=1);

namespace App\Domain\Telegram;

/**
 * Port to the Telegram Bot API. Stateless and credential-parameterized —
 * every call carries its own BotCredentials rather than the gateway being
 * bound to one token at container-build time, since the token is
 * admin-configured runtime data that can change.
 */
interface TelegramGatewayInterface
{
    /**
     * @return string|null The bot's username, or null if the token is
     *   rejected by Telegram.
     */
    public function getBotIdentity(BotCredentials $credentials): ?string;

    public function sendTestMessage(BotCredentials $credentials, string $text): bool;

    public function setWebhook(BotCredentials $credentials, string $webhookUrl): bool;

    public function deleteWebhook(BotCredentials $credentials): bool;

    /**
     * @return array<string, mixed> Raw webhook status as reported by
     *   Telegram (url, pending_update_count, last_error_message, ...). Left
     *   loosely typed deliberately — this is third-party diagnostic data,
     *   not a domain concept with invariants of its own.
     */
    public function getWebhookInfo(BotCredentials $credentials): array;
}
