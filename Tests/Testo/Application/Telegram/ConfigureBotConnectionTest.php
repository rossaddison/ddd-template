<?php

declare(strict_types=1);

namespace Tests\Testo\Application\Telegram;

use App\Application\Telegram\ConfigureBotConnection;
use App\Domain\Telegram\BotConnection;
use App\Domain\Telegram\BotConnectionRepositoryInterface;
use App\Domain\Telegram\InvalidBotCredentialsException;
use Mockery as m;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

#[Test]
#[Covers(ConfigureBotConnection::class)]
final class ConfigureBotConnectionTest
{
    private const string VALID_TOKEN = '123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghi';

    public function savesAConnectionUpdatedWithTheNewCredentials(): void
    {
        $repository = m::mock(BotConnectionRepositoryInterface::class);
        /** @var \Mockery\Expectation $getExpectation */
        $getExpectation = $repository->shouldReceive('get');
        $getExpectation->once()->andReturn(new BotConnection());

        $saved = null;
        /** @var \Mockery\Expectation $saveExpectation */
        $saveExpectation = $repository->shouldReceive('save');
        $saveExpectation->once()->andReturnUsing(function (BotConnection $connection) use (&$saved): void {
            $saved = $connection;
        });

        $configureBotConnection = new ConfigureBotConnection($repository);

        $result = $configureBotConnection(self::VALID_TOKEN, '42', null);

        Assert::same($result->credentials()?->token(), self::VALID_TOKEN);
        Assert::same($result->credentials()?->chatId(), '42');
        Assert::same($saved?->credentials()?->token(), self::VALID_TOKEN);
    }

    public function rejectsAnInvalidTokenBeforeTouchingTheRepository(): void
    {
        // BotCredentials validates the token in its constructor, which runs
        // before ConfigureBotConnection ever calls the repository.
        $repository = m::mock(BotConnectionRepositoryInterface::class);
        $repository->shouldNotReceive('get');
        $repository->shouldNotReceive('save');

        $configureBotConnection = new ConfigureBotConnection($repository);

        Expect::exception(InvalidBotCredentialsException::class);

        $configureBotConnection('not-a-valid-token', null, null);
    }
}
