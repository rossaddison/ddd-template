<?php

declare(strict_types=1);

use App\Domain\Setting\SettingRepositoryInterface;
use App\Domain\Telegram\BotConnectionRepositoryInterface;
use App\Domain\Telegram\TelegramGatewayInterface;
use App\Infrastructure\Persistence\Setting\CycleSettingRepository;
use App\Infrastructure\Persistence\Telegram\CycleSettingBackedBotConnectionRepository;
use App\Infrastructure\Telegram\PhptgTelegramGateway;

return [
    SettingRepositoryInterface::class => CycleSettingRepository::class,
    BotConnectionRepositoryInterface::class => CycleSettingBackedBotConnectionRepository::class,
    TelegramGatewayInterface::class => PhptgTelegramGateway::class,
];
