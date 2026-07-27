<?php

declare(strict_types=1);

namespace App\Infrastructure\Telegram;

use App\Domain\Telegram\BotCredentials;
use App\Domain\Telegram\TelegramGatewayInterface;
use Phptg\BotApi\FailResult;
use Phptg\BotApi\TelegramBotApi;
use Phptg\BotApi\Transport\CurlTransport;
use Psr\Log\LoggerInterface;

/**
 * Implements TelegramGatewayInterface via phptg/bot-api, covering only the
 * generic bot-administration operations (identity check, test message,
 * webhook management). Ported by reading — not modifying —
 * src/Invoice/Helpers/Telegram/TelegramHelper.php, which stays as-is for the
 * Invoice module's own invoice-sending/payment features.
 *
 * A TelegramBotApi client is built fresh per call from the passed
 * BotCredentials rather than cached on the instance, since the token is
 * admin-configured runtime data that can change between calls.
 */
final readonly class PhptgTelegramGateway implements TelegramGatewayInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[\Override]
    public function getBotIdentity(BotCredentials $credentials): ?string
    {
        $result = $this->botApi($credentials)->getMe();
        return $result instanceof FailResult ? null : $result->username;
    }

    #[\Override]
    public function sendTestMessage(BotCredentials $credentials, string $text): bool
    {
        $chatId = $credentials->chatId();
        if ($chatId === null) {
            return false;
        }
        $result = $this->botApi($credentials)->sendMessage($chatId, $text);
        return !($result instanceof FailResult);
    }

    #[\Override]
    public function setWebhook(BotCredentials $credentials, string $webhookUrl): bool
    {
        $result = $this->botApi($credentials)->setWebhook(
            $webhookUrl,
            null,
            null,
            null,
            null,
            $credentials->webhookSecret(),
        );
        return $result === true;
    }

    #[\Override]
    public function deleteWebhook(BotCredentials $credentials): bool
    {
        $result = $this->botApi($credentials)->deleteWebhook(false);
        return $result === true;
    }

    #[\Override]
    public function getWebhookInfo(BotCredentials $credentials): array
    {
        $result = $this->botApi($credentials)->getWebhookInfo();
        if ($result instanceof FailResult) {
            return [];
        }
        return [
            'url' => $result->url,
            'has_custom_certificate' => $result->hasCustomCertificate,
            'pending_update_count' => $result->pendingUpdateCount,
            'last_error_message' => $result->lastErrorMessage,
        ];
    }

    private function botApi(BotCredentials $credentials): TelegramBotApi
    {
        return new TelegramBotApi(
            $credentials->token(),
            'https://api.telegram.org',
            new CurlTransport(),
            $this->logger,
        );
    }
}
