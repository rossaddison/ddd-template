<?php

declare(strict_types=1);

namespace App\Application\Setting;

use App\Domain\Setting\Setting;
use App\Domain\Setting\SettingKey;
use App\Domain\Setting\SettingRepositoryInterface;

final readonly class GetSetting
{
    public function __construct(private SettingRepositoryInterface $settings)
    {
    }

    public function __invoke(SettingKey $key): ?Setting
    {
        return $this->settings->find($key);
    }
}
