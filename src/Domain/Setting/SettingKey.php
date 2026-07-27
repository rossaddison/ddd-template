<?php

declare(strict_types=1);

namespace App\Domain\Setting;

use InvalidArgumentException;

/**
 * A setting's identifying key. Mirrors the `setting_key` column's
 * `string(100)` constraint as a domain invariant rather than leaving it to
 * be discovered as a DB error at save time.
 */
final readonly class SettingKey
{
    private const int MAX_LENGTH = 100;

    private string $value;

    public function __construct(string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Setting key must not be empty.');
        }
        if (strlen($value) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Setting key must not exceed %d characters.', self::MAX_LENGTH)
            );
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
