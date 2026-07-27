<?php

declare(strict_types=1);

namespace App\Domain\Telegram;

/**
 * A Telegram bot's admin-configured credentials. The token shape is a real
 * domain invariant — Telegram bot tokens are always "<numeric bot id>:<
 * secret>" — so a BotCredentials instance can never exist in an invalid
 * state; there is deliberately no way to construct one with a malformed
 * token.
 *
 * chatId and webhookSecret are optional refinements: a bot can talk to
 * Telegram with just a token (e.g. to fetch its own identity), but needs a
 * chatId to send messages and a webhookSecret to validate inbound webhook
 * calls.
 */
final readonly class BotCredentials
{
    private const string TOKEN_PATTERN = '/^\d+:[A-Za-z0-9_-]{30,}$/';

    public function __construct(
        private string $token,
        private ?string $chatId = null,
        private ?string $webhookSecret = null,
    ) {
        if (preg_match(self::TOKEN_PATTERN, $token) !== 1) {
            throw new InvalidBotCredentialsException(
                'Telegram bot token does not match the expected "<bot id>:<secret>" shape.'
            );
        }
    }

    public function token(): string
    {
        return $this->token;
    }

    public function chatId(): ?string
    {
        return $this->chatId;
    }

    public function webhookSecret(): ?string
    {
        return $this->webhookSecret;
    }

    public function withChatId(?string $chatId): self
    {
        return new self($this->token, $chatId, $this->webhookSecret);
    }

    public function withWebhookSecret(?string $webhookSecret): self
    {
        return new self($this->token, $this->chatId, $webhookSecret);
    }
}
