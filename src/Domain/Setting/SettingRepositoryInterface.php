<?php

declare(strict_types=1);

namespace App\Domain\Setting;

interface SettingRepositoryInterface
{
    public function find(SettingKey $key): ?Setting;

    public function save(Setting $setting): void;

    /**
     * @return iterable<Setting>
     */
    public function all(): iterable;
}
