<?php

declare(strict_types=1);

namespace App\Domain\Telegram;

interface BotConnectionRepositoryInterface
{
    public function get(): BotConnection;

    public function save(BotConnection $connection): void;
}
