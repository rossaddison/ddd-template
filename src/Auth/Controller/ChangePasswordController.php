<?php

declare(strict_types=1);

namespace App\Auth\Controller;

use App\Auth\AuthService;
use App\Auth\IdentityRepository;
use App\Auth\Form\ChangePasswordForm;
use App\Auth\Trait\TurnstileVerification;
use App\Application\Setting\GetSetting;
use App\Domain\Setting\SettingKey;
use App\Service\WebControllerService;
use App\Shell\Traits\FlashMessage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Method;
use Yiisoft\Session\Flash\Flash;
use Yiisoft\Translator\TranslatorInterface as Translator;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class ChangePasswordController
{
    use TurnstileVerification;
    use FlashMessage;

    public function __construct(
        private readonly Flash $flash,
        private readonly Translator $translator,
        private readonly CurrentUser $currentUser,
        private readonly WebControllerService $webService,
        private readonly AuthSecurityHelper $secHelper,
        private readonly GetSetting $getSetting,
        private WebViewRenderer $webViewRenderer,
    ) {
        // withControllerName returns a new instance so reassignment is needed
        $this->webViewRenderer = $webViewRenderer->withControllerName(
            'changepassword');
    }

    public function change(
        AuthService $authService,
        IdentityRepository $identityRepository,
        ServerRequestInterface $request,
        FormHydrator $formHydrator,
        ChangePasswordForm $changePasswordForm,
    ): ResponseInterface {
        $identityId = $authService->isGuest() ? null : $this->currentUser->getIdentity()->getId();
        $identity   = $identityId !== null ? $identityRepository->findIdentity($identityId) : null;
        if ($identity === null) {
            return $this->redirectToMain();
        }
/**
 * Identity and User are in a HasOne relationship so no null value
 * Get the username or emailaddress of the current user
 * Related logic: see src\User\User function getLogin()
 */
        $login = $identity->getUser()?->getLogin();
        $successRedirect = null;

        if ($request->getMethod() === Method::POST) {
            $ip    = $this->secHelper->getClientIpAddress($request);
            $body  = (array) $request->getParsedBody();
            $token = (string) ($body['cf-turnstile-response'] ?? '');
            if (!$this->secHelper->checkRateLimit(hash('sha256', 'change_ctrl' . $ip))
                || !$this->verifyTurnstile($token, $ip)
            ) {
                return $this->redirectToMain();
            }
        }

        if ($request->getMethod() === Method::POST
            && $formHydrator->populate($changePasswordForm, $request->getParsedBody())
            && $changePasswordForm->change()
        ) {
// Identity implements CookieLoginIdentityInterface: ensure the regeneration of
//  the cookie auth key by means of $authService->logout();
// Related logic: see vendor\yiisoft\user\src\Login\Cookie\
//  CookieLoginIdentityInterface
// Specific note: "Make sure to invalidate earlier issued keys when you implement
//  force user logout,
// PASSWORD CHANGE and other scenarios, that require forceful access revocation
//  for old sessions.
// The authService logout function will regenerate the auth key here =>
//  overwriting any auth key
            $authService->logout();
            $this->flashMessage('success', $this->translator->translate('validator.password.change'));
            $successRedirect = $this->redirectToMain();
        }
        $errors = $changePasswordForm->isValidated()
            ? $changePasswordForm->getValidationResult()->getErrorMessagesIndexedByProperty()
            : [];

        $turnstileSiteKey = ($this->getSetting)(new SettingKey('turnstile_site_key'))?->value() ?? '';

        return $successRedirect ?? $this->webViewRenderer->render('change', [
            'formModel' => $changePasswordForm,
            'login' => $login,
            'errors' => $errors,
            'turnstileSiteKey' => $turnstileSiteKey,
        ]);
    } // change

    private function redirectToMain(): ResponseInterface
    {
        return $this->webService->getRedirectResponse('site/index');
    }
}
