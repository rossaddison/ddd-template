<?php

declare(strict_types=1);

namespace App\Auth\Controller;

use App\Auth\AuthService;
use App\Auth\Form\SignupForm;
use App\Auth\Roles;
use App\Auth\Trait\ClassList;
use App\Auth\Trait\Oauth2;
use App\Auth\Trait\TurnstileVerification;
use App\Application\Setting\GetSetting;
use App\Domain\Setting\SettingKey;
use App\User\UserRepository as uR;
use App\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Rbac\Manager as Manager;
use Yiisoft\Security\Random;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class SignupController
{
    use ClassList;

    use Oauth2;

    use TurnstileVerification;

    private const string SIGNUP_FAILED = 'site/signupfailed';

    public function __construct(
        private readonly Manager $manager,
        private readonly WebControllerService $webService,
        private readonly SessionInterface $session,
        private WebViewRenderer $webViewRenderer,
        private readonly GetSetting $getSetting,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
    ) {
        $this->webViewRenderer = $webViewRenderer->withControllerName('signup');
        $this->initializeOauth2IdentityProviderCredentials();
    }

    public function signup(
        AuthService $authService,
        FormHydrator $formHydrator,
        ServerRequestInterface $request,
        SignupForm $signupForm,
        uR $uR,
    ): ResponseInterface {
        if (!$authService->isGuest()) {
            return $this->webService->getRedirectResponse('site/index');
        }

        if ($request->getMethod() === 'POST') {
            $body = (array) $request->getParsedBody();
            $srv = $request->getServerParams();
            $remoteIp = (string) ($srv['HTTP_CF_CONNECTING_IP'] ?? $srv['REMOTE_ADDR'] ?? '');
            if (!$this->verifyTurnstile((string) ($body['cf-turnstile-response'] ?? ''), $remoteIp)) {
                return $this->webService->getRedirectResponse(self::SIGNUP_FAILED);
            }
        }

        $redirect = null;
        if ($formHydrator->populateFromPostAndValidate($signupForm, $request)) {
            $redirect = $this->completeSignup($signupForm, $authService, $uR);
        }

        $codeVerifier = Random::string(128);
        $this->session->set('code_verifier', $codeVerifier);
        $rTrim = rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '=');
        $codeChallenge = strtr($rTrim, '+/', '-_');
        $errors = $signupForm->isValidated()
            ? $signupForm->getValidationResult()->getErrorMessagesIndexedByProperty()
            : [];
        $turnstileSiteKey = ($this->getSetting)(new SettingKey('turnstile_site_key'))?->value() ?? '';
        return $redirect ?? $this->webViewRenderer->render('signup', [
            'class' => $this->classList(),
            'formModel' => $signupForm,
            'errors' => $errors,
            'request' => $request,
            'idpList' => $this->idpList($codeChallenge),
            'turnstileSiteKey' => $turnstileSiteKey,
        ]);
    }

    /**
     * The identity provider callbacks (Trait\Callback) skip the active-account
     * gate on the assumption the provider already verified control of the
     * account; here the plain signup form already proved the same thing by
     * requiring a working password, so this path also activates and logs the
     * user in immediately — no separate email-click confirmation step.
     */
    private function completeSignup(SignupForm $signupForm, AuthService $authService, uR $uR): ResponseInterface
    {
        $user = $signupForm->signup();
        $userId = $user->reqId();
        if ($userId <= 0) {
            return $this->webService->getRedirectResponse(self::SIGNUP_FAILED);
        }
        $role = $uR->repoCount() == 1 ? Roles::ADMIN : Roles::USER;
        if (!$this->assignRoleAndVerify($userId, $role)) {
            return $this->webService->getRedirectResponse(self::SIGNUP_FAILED);
        }
        return $authService->login($user->getLogin(), $signupForm->getPassword())
            ? $this->webService->getRedirectResponse('shell/setting/index')
            : $this->webService->getRedirectResponse('site/signupsuccess');
    }

    /**
     * Assign a role to a newly signed-up user and verify the assignment
     * persisted correctly. Guards against silent failures where assign()
     * succeeds in memory but never reaches the yii_rbac_assignment DB table.
     */
    private function assignRoleAndVerify(int $userId, string $role): bool
    {
        $this->manager->revokeAll((string) $userId);
        $this->manager->assign($role, (string) $userId);

        $roles = $this->manager->getRolesByUserId((string) $userId);
        if (empty($roles)) {
            $this->logger->log(
                LogLevel::ERROR,
                'RBAC assignment failed to persist for userId: ' . (string) $userId
                    . ' role: ' . $role
                    . ' — check yii_rbac_assignment table'
            );
            return false;
        }
        return true;
    }
}
