<?php

declare(strict_types=1);

namespace Tests\Testo\Domain\Setting;

use App\Domain\Setting\SettingKey;
use InvalidArgumentException;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

#[Test]
#[Covers(SettingKey::class)]
final class SettingKeyTest
{
    public function acceptsAnOrdinaryKey(): void
    {
        $key = new SettingKey('shell_app_name');

        Assert::same($key->value(), 'shell_app_name');
    }

    public function rejectsAnEmptyKey(): void
    {
        Expect::exception(InvalidArgumentException::class);

        new SettingKey('');
    }

    public function rejectsAKeyLongerThanOneHundredCharacters(): void
    {
        Expect::exception(InvalidArgumentException::class);

        new SettingKey(str_repeat('a', 101));
    }

    public function acceptsAKeyExactlyOneHundredCharactersLong(): void
    {
        $key = new SettingKey(str_repeat('a', 100));

        Assert::same(strlen($key->value()), 100);
    }

    public function equalsComparesByValue(): void
    {
        $a = new SettingKey('shell_app_name');
        $b = new SettingKey('shell_app_name');
        $c = new SettingKey('shell_theme_color');

        Assert::true($a->equals($b));
        Assert::false($a->equals($c));
    }
}
