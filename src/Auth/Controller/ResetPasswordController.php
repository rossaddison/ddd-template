<?php

declare(strict_types=1);

namespace App\Auth\Controller;

use App\Auth\Form\ResetPasswordForm;
use App\Auth\IdentityRepository as idR;
use App\Auth\TokenRepository as tR;
use App\Auth\Trait\TurnstileVerification;
use App\Application\Setting\GetSetting;
use App\Domain\Setting\SettingKey;
use App\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Method;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Security\TokenMask;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class ResetPasswordController
{
    use TurnstileVerification;

    public const string REQUEST_PASSWORD_RESET_TOKEN = 'request-password-reset';

    public function __construct(
        private readonly WebControllerService $webService,
        private WebViewRenderer $webViewRenderer,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
        private readonly AuthSecurityHelper $secHelper,
        private readonly GetSetting $getSetting,
    ) {
        // withControllerName returns a new instance so reassignment is needed
        $this->webViewRenderer = $webViewRenderer->withControllerName(
            'resetpassword');
    }

    /**
     * Note: After the user has clicked on their inbox link, the returned
     *  masked token will be unmasked. Only once the
     *  32bit random string has been compared with the unmasked
     *  request-password-reset token, is the resetPasswordForm presented
     *  to the user
     */
    public function resetpassword(
        #[RouteArgument('token')]
        string $maskedToken,
        FormHydrator $formHydrator,
        ServerRequestInterface $request,
        ResetPasswordForm $resetPasswordForm,
        idR $idR,
        tR $tR,
    ): Response {
        $unMaskedToken = TokenMask::remove($maskedToken);
        $pos = strrpos($unMaskedToken, '_');
        $timestamp = $pos !== false ? substr($unMaskedToken, $pos + 1) : '0';
        $tokenRand = $pos !== false ? substr($unMaskedToken, 0, -(strlen($timestamp) + 1)) : '';
        $identity = ($pos !== false && (int) $timestamp + 3600 >= time())
            ? $tR->findIdentityByToken($tokenRand, self::REQUEST_PASSWORD_RESET_TOKEN)
            : null;
        $user = $identity?->getUser();
        $identityId = $identity !== null ? (int) $identity->getId() : 0;

        if ($pos === false
            || (int) $timestamp + 3600 < time()
            || $identity === null
            || $user === null
            || $identityId <= 0
        ) {
            return $this->failedReset();
        }

        $turnstileSiteKey = ($this->getSetting)(new SettingKey('turnstile_site_key'))?->value() ?? '';

        if ($request->getMethod() === Method::POST) {
            $ip    = $this->secHelper->getClientIpAddress($request);
            $body  = (array) $request->getParsedBody();
            $token = (string) ($body['cf-turnstile-response'] ?? '');
            if (!$this->secHelper->checkRateLimit(hash('sha256', 'reset_ctrl' . $ip))
                || !$this->verifyTurnstile($token, $ip)
            ) {
                return $this->failedReset();
            }
        }

        $successRedirect = null;
        if ($formHydrator->populateFromPostAndValidate($resetPasswordForm, $request)) {
            // 1.) setPassword in User
            $user->setPassword($resetPasswordForm->getNewPassword());
            // 2.) nullify PasswordResetToken (retain Token:type so it can be reissued)
            $tokenRecord = $tR->findTokenByIdentityIdAndType($identityId, self::REQUEST_PASSWORD_RESET_TOKEN);
            if (null !== $tokenRecord) {
                $tokenRecord->setToken('');
                $tR->save($tokenRecord);
                // 3.) generateAuthKey in Identity
                $identity->generateAuthKey();
                $idR->save($identity);
            }
            $successRedirect = $this->webService->getRedirectResponse('site/resetpasswordsuccess');
        }
        return $successRedirect ?? $this->webViewRenderer->render(
            'resetpassword',
            ['formModel' => $resetPasswordForm, 'token' => $maskedToken, 'turnstileSiteKey' => $turnstileSiteKey],
        );
    }

    private function failedReset(): Response
    {
        $this->logger->error($this->translator->translate('password.reset.failed'));
        return $this->webService->getRedirectResponse('site/resetpasswordfailed');
    }
}
