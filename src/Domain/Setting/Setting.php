<?php

declare(strict_types=1);

namespace App\Domain\Setting;

/**
 * A single key/value setting. Immutable — changing the value produces a new
 * instance via withValue(), rather than exposing a mutable setter, so
 * Application-layer code cannot silently mutate a Setting held elsewhere.
 */
final readonly class Setting
{
    public function __construct(
        private SettingKey $key,
        private string $value,
    ) {
    }

    public function key(): SettingKey
    {
        return $this->key;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function withValue(string $value): self
    {
        return new self($this->key, $value);
    }
}
