<?php

declare(strict_types=1);

namespace App\Application\Setting;

use App\Domain\Setting\Setting;
use App\Domain\Setting\SettingKey;
use App\Domain\Setting\SettingRepositoryInterface;

final readonly class UpdateSetting
{
    public function __construct(private SettingRepositoryInterface $settings)
    {
    }

    public function __invoke(SettingKey $key, string $value): void
    {
        $existing = $this->settings->find($key);
        $setting = $existing?->withValue($value) ?? new Setting($key, $value);
        $this->settings->save($setting);
    }
}
