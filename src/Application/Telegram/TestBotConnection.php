<?php

declare(strict_types=1);

namespace App\Application\Telegram;

use App\Domain\Telegram\BotConnectionRepositoryInterface;
use App\Domain\Telegram\TelegramGatewayInterface;

final readonly class TestBotConnection
{
    public function __construct(
        private BotConnectionRepositoryInterface $connections,
        private TelegramGatewayInterface $gateway,
    ) {
    }

    public function __invoke(string $text = 'Hello from the Telegram bot connection test.'): bool
    {
        $credentials = $this->connections->get()->credentials();
        if ($credentials === null || $credentials->chatId() === null) {
            return false;
        }
        return $this->gateway->sendTestMessage($credentials, $text);
    }
}
