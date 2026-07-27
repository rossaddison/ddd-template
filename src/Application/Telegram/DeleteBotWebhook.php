<?php

declare(strict_types=1);

namespace App\Application\Telegram;

use App\Domain\Telegram\BotConnectionRepositoryInterface;
use App\Domain\Telegram\TelegramGatewayInterface;

final readonly class DeleteBotWebhook
{
    public function __construct(
        private BotConnectionRepositoryInterface $connections,
        private TelegramGatewayInterface $gateway,
    ) {
    }

    public function __invoke(): bool
    {
        $credentials = $this->connections->get()->credentials();
        if ($credentials === null) {
            return false;
        }
        return $this->gateway->deleteWebhook($credentials);
    }
}
