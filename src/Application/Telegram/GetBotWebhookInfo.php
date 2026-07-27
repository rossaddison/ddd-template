<?php

declare(strict_types=1);

namespace App\Application\Telegram;

use App\Domain\Telegram\BotConnectionRepositoryInterface;
use App\Domain\Telegram\TelegramGatewayInterface;

final readonly class GetBotWebhookInfo
{
    public function __construct(
        private BotConnectionRepositoryInterface $connections,
        private TelegramGatewayInterface $gateway,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        $credentials = $this->connections->get()->credentials();
        if ($credentials === null) {
            return [];
        }
        return $this->gateway->getWebhookInfo($credentials);
    }
}
