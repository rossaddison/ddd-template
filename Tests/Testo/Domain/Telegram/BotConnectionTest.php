<?php

declare(strict_types=1);

namespace Tests\Testo\Domain\Telegram;

use App\Domain\Telegram\BotConnection;
use App\Domain\Telegram\BotCredentials;
use App\Domain\Telegram\InvalidBotCredentialsException;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

#[Test]
#[Covers(BotConnection::class)]
final class BotConnectionTest
{
    private function validCredentials(): BotCredentials
    {
        return new BotCredentials('123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghi');
    }

    public function defaultsToUnconfiguredAndDisabled(): void
    {
        $connection = new BotConnection();

        Assert::false($connection->isConfigured());
        Assert::false($connection->isEnabled());
        Assert::null($connection->credentials());
    }

    public function enableSucceedsWhenCredentialsAreConfigured(): void
    {
        $connection = new BotConnection($this->validCredentials());

        $enabled = $connection->enable();

        Assert::true($enabled->isEnabled());
        Assert::false($connection->isEnabled());
    }

    public function enableThrowsWhenCredentialsAreNotConfigured(): void
    {
        $connection = new BotConnection();

        Expect::exception(InvalidBotCredentialsException::class);

        $connection->enable();
    }

    public function disableAlwaysSucceeds(): void
    {
        $connection = (new BotConnection($this->validCredentials()))->enable();

        $disabled = $connection->disable();

        Assert::false($disabled->isEnabled());
        Assert::true($connection->isEnabled());
    }

    public function updateCredentialsReplacesCredentialsAndKeepsEnabledState(): void
    {
        $connection = (new BotConnection($this->validCredentials()))->enable();
        $newCredentials = new BotCredentials('987654321:ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvut');

        $updated = $connection->updateCredentials($newCredentials);

        Assert::same($updated->credentials()?->token(), $newCredentials->token());
        Assert::true($updated->isEnabled());
    }
}
