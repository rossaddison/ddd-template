<?php

declare(strict_types=1);

namespace App\Auth\Trait;

use App\Application\Setting\GetSetting;
use App\Domain\Setting\SettingKey;
use Yiisoft\Yii\AuthClient\Widget\AuthChoice;

/**
 * @property-read GetSetting $getSetting
 */
trait Oauth2
{
    private function initializeOauth2IdentityProviderCredentials(): void
    {
        /**
         * Related logic: see config/common/params.php
         * No need to instantiate AuthChoice. It has been dependency injected
         * and generates a button on the auth/login view
         * at config/web/di/yii-auth-client.php
         *
         * Related logic: see https://entra.microsoft.com/#view/
         *  Microsoft_AAD_IAM/TenantOverview.ReactView
         * Rebuild the authUrl and tokenUrl to include the tenant
         *  (default: 'common') which can be
         * 'common', 'organisation', 'consumers', or ID. ID is used here.
         * The tenant can be acquired from Microsoft Entra Admin Centre ...
         *  Identity Overview ... Tenant
         * and is inserted into the root's .env file.
         */

        /** @psalm-var \Yiisoft\Yii\AuthClient\Client\MicrosoftOnline $microsoftOnline */
        $microsoftOnline = (AuthChoice::widget())->getClient('microsoftonline');

        $authUrl =
            $microsoftOnline->getAuthUrlWithTenantInserted(
                    $microsoftOnline->getTenant());

        $microsoftOnline->setAuthUrl($authUrl);

        $tokenUrl =
            $microsoftOnline->getTokenUrlWithTenantInserted(
                    $microsoftOnline->getTenant());
        $microsoftOnline->setTokenUrl($tokenUrl);
    }

    // IdentityProviderList
    private function idpList(string $codeChallenge): array
    {
        $noButton = fn (string $key): bool =>
            ($this->getSetting)(new SettingKey($key))?->value() === '1';
        return [
            'facebook' => [
                'noflag' => $noButton('no_facebook_continue_button'),
                'params' => [],
                'buttonName' =>
                 $this->translator->translate('continue.with.facebook'),
            ],
            'github' => [
                'noflag' => $noButton('no_github_continue_button'),
                'params' => [],
                'buttonName' =>
                 $this->translator->translate('continue.with.github'),
            ],
            'google' => [
                'noflag' => $noButton('no_google_continue_button'),
                'params' => [],
                'buttonName' =>
                 $this->translator->translate('continue.with.google'),
            ],
            'linkedin' => [
                'noflag' => $noButton('no_linkedin_continue_button'),
                'params' => [],
                'buttonName' =>
                 $this->translator->translate('continue.with.linkedin'),
            ],
            'microsoftonline' => [
                'noflag' => $noButton('no_microsoftonline_continue_button'),
                'params' => [],
                'buttonName' =>
                 $this->translator->translate('continue.with.microsoftonline'),
            ],
            'vkontakte' => [
                'noflag' => $noButton('no_vkontakte_continue_button'),
                'params' => [
                    'code_challenge' => $codeChallenge,
                    'code_challenge_method' => 'S256',
                ],
                'buttonName' =>
                 $this->translator->translate('continue.with.vkontakte'),
            ],
            'x' => [
                'noflag' => $noButton('no_x_continue_button'),
                'params' => [
                    'code_challenge' => $codeChallenge,
                    'code_challenge_method' => 'S256',
                ],
                'buttonName' =>
                 $this->translator->translate('continue.with.x'),
            ],
            'yandex' => [
                'noflag' => $noButton('no_yandex_continue_button'),
                'params' => [
                    'code_challenge' => $codeChallenge,
                    'code_challenge_method' => 'S256',
                ],
                'buttonName' =>
                 $this->translator->translate('continue.with.yandex'),
            ],
        ];
    }
}
