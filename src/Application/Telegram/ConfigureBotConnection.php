<?php

declare(strict_types=1);

namespace App\Application\Telegram;

use App\Domain\Telegram\BotConnection;
use App\Domain\Telegram\BotConnectionRepositoryInterface;
use App\Domain\Telegram\BotCredentials;

final readonly class ConfigureBotConnection
{
    public function __construct(private BotConnectionRepositoryInterface $connections)
    {
    }

    public function __invoke(string $token, ?string $chatId, ?string $webhookSecret): BotConnection
    {
        $credentials = new BotCredentials($token, $chatId, $webhookSecret);
        $connection = $this->connections->get()->updateCredentials($credentials);
        $this->connections->save($connection);
        return $connection;
    }
}
