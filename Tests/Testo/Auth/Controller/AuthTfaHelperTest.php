<?php

declare(strict_types=1);

namespace Tests\Testo\Auth\Controller;

use App\Application\Setting\GetSetting;
use App\Auth\Controller\AuthTfaHelper;
use App\User\RecoveryCodeService;
use Mockery as m;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;

#[Test]
#[Covers(AuthTfaHelper::class)]
final class AuthTfaHelperTest
{
    private function helper(): AuthTfaHelper
    {
        return new AuthTfaHelper(
            m::mock(GetSetting::class),
            m::mock(RecoveryCodeService::class),
        );
    }

    public function isValidTotpCodeAcceptsExactlySixDigits(): void
    {
        $helper = $this->helper();

        Assert::true($helper->isValidTotpCode('123456'));
        Assert::false($helper->isValidTotpCode('12345'));
        Assert::false($helper->isValidTotpCode('1234567'));
        Assert::false($helper->isValidTotpCode('12345a'));
    }

    public function isValidBackupCodeAcceptsExactlyEightAlphanumericChars(): void
    {
        $helper = $this->helper();

        Assert::true($helper->isValidBackupCode('AbC12345'));
        Assert::false($helper->isValidBackupCode('AbC1234'));
        Assert::false($helper->isValidBackupCode('AbC123456'));
        Assert::false($helper->isValidBackupCode('AbC-1234'));
    }

    public function sanitizeAndValidateCodeStripsNonAlphanumericsAndClassifiesLength(): void
    {
        $helper = $this->helper();

        Assert::same($helper->sanitizeAndValidateCode('123 456'), '123456');
        Assert::same($helper->sanitizeAndValidateCode('AbC-1234-5'), 'AbC12345');
        Assert::null($helper->sanitizeAndValidateCode(''));
        Assert::null($helper->sanitizeAndValidateCode('123'));
        Assert::null($helper->sanitizeAndValidateCode('1234567890'));
    }
}
