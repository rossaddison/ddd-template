<?php

declare(strict_types=1);

namespace App\Domain\Telegram;

use InvalidArgumentException;

final class InvalidBotCredentialsException extends InvalidArgumentException
{
}
