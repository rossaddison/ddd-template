<?php

declare(strict_types=1);

namespace App\Domain\Telegram;

/**
 * The single Telegram bot connection for this app (one bot per app, no ID
 * needed). Immutable — every mutation returns a new instance.
 *
 * The enabled flag and credential validity are deliberately independent:
 * enabling requires valid credentials, but a configured bot can still be
 * explicitly disabled (opted out) without losing its credentials.
 */
final readonly class BotConnection
{
    public function __construct(
        private ?BotCredentials $credentials = null,
        private bool $enabled = false,
    ) {
    }

    public function credentials(): ?BotCredentials
    {
        return $this->credentials;
    }

    public function isConfigured(): bool
    {
        return $this->credentials !== null;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function updateCredentials(BotCredentials $credentials): self
    {
        return new self($credentials, $this->enabled);
    }

    public function enable(): self
    {
        if ($this->credentials === null) {
            throw new InvalidBotCredentialsException(
                'Cannot enable the Telegram bot connection before valid credentials are configured.'
            );
        }
        return new self($this->credentials, true);
    }

    public function disable(): self
    {
        return new self($this->credentials, false);
    }
}
