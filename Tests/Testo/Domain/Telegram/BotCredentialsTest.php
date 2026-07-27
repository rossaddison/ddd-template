<?php

declare(strict_types=1);

namespace Tests\Testo\Domain\Telegram;

use App\Domain\Telegram\BotCredentials;
use App\Domain\Telegram\InvalidBotCredentialsException;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

#[Test]
#[Covers(BotCredentials::class)]
final class BotCredentialsTest
{
    public function acceptsAValidTokenShape(): void
    {
        $credentials = new BotCredentials('123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghi');

        Assert::same($credentials->token(), '123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghi');
        Assert::null($credentials->chatId());
        Assert::null($credentials->webhookSecret());
    }

    public function rejectsATokenMissingTheColonSeparator(): void
    {
        Expect::exception(InvalidBotCredentialsException::class);

        new BotCredentials('not-a-valid-token');
    }

    public function rejectsAnEmptyToken(): void
    {
        Expect::exception(InvalidBotCredentialsException::class);

        new BotCredentials('');
    }

    public function withChatIdReturnsANewInstanceWithTheSameToken(): void
    {
        $original = new BotCredentials('123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghi');

        $updated = $original->withChatId('42');

        Assert::same($updated->chatId(), '42');
        Assert::same($updated->token(), $original->token());
        Assert::null($original->chatId());
    }

    public function withWebhookSecretReturnsANewInstanceWithTheSameToken(): void
    {
        $original = new BotCredentials('123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghi');

        $updated = $original->withWebhookSecret('s3cr3t');

        Assert::same($updated->webhookSecret(), 's3cr3t');
        Assert::same($updated->token(), $original->token());
        Assert::null($original->webhookSecret());
    }
}
