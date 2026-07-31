<?php

declare(strict_types=1);

namespace App\Auth\Controller;

use App\Auth\{AuthService, CallbackDeps, Form\LoginForm, Roles, Trait\Callback,
    Trait\ClassList, Trait\Oauth2, Trait\TurnstileVerification};
use App\Application\Setting\GetSetting;
use App\Domain\Setting\SettingKey;
use App\Auth\Trait\TwoFactorAuth;
use App\Service\WebControllerService;
use App\Infrastructure\Persistence\Identity\Identity;
use App\Infrastructure\Persistence\User\User;
use App\User\UserRepository;
use App\User\RecoveryCodeService;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Yiisoft\{
    DataResponse\ResponseFactory\DataResponseFactoryInterface,
    FormModel\FormHydrator, Html\Tag\Style, Http\Method,
    Rbac\Manager as Manager,
    Security\Random, Session\Flash\Flash,
    Session\SessionInterface, Translator\TranslatorInterface,
    User\Login\Cookie\CookieLogin, User\Login\Cookie\CookieLoginIdentityInterface,
    Yii\View\Renderer\WebViewRenderer,
    Yii\AuthClient\Widget\AuthChoice, Yii\RateLimiter\CounterInterface,
    Yii\RateLimiter\Storage\StorageInterface};

final class AuthController
{
    use Callback;

    use ClassList;

    use TwoFactorAuth;

    use TurnstileVerification;

    // reads .env-configured OAuth2 client credentials — see config/web/di/yii-auth-client.php
    use Oauth2;

    private AuthTfaHelper $tfaHelper;
    private AuthSecurityHelper $secHelper;

    public function __construct(
        private readonly AuthService $authService,
        private readonly RecoveryCodeService $recoveryCodeService,
        private readonly DataResponseFactoryInterface $factory,
        private readonly WebControllerService $webService,
        private WebViewRenderer $webViewRenderer,
        private readonly Manager $manager,
        private readonly SessionInterface $session,
        private readonly GetSetting $getSetting,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
        // trait variables — VKontakte's step8ObtainingUserDataArrayWithClientId()
        // and CallbackDeps consumers need the raw HTTP client/request factory
        private readonly ClientInterface $configWebDiAuthGuzzle,
        private readonly RequestFactoryInterface $requestFactory,
        CounterInterface $rateLimiter,
        StorageInterface $rateLimiterStorage,
    ) {
        $this->webViewRenderer = $webViewRenderer->withControllerName('auth');
        // use the Oauth2 trait function
        $this->initializeOauth2IdentityProviderCredentials();
        $this->tfaHelper = new AuthTfaHelper($this->getSetting, $this->recoveryCodeService);
        $this->secHelper = new AuthSecurityHelper(
            $rateLimiter, $rateLimiterStorage, $this->logger, $this->session);
    }

    /**
     * Related logic: see AuthChoice function authRoutedButtons()
     */
    public function authclient(
        ServerRequestInterface $request,
        AuthChoice $authChoice,
    ): ResponseInterface {
        $query = $request->getQueryParams();
        $clientName = (string) $query['authclient'];
        $client = $authChoice->getClient($clientName);
        $codeVerifier = Random::string(128);
        $this->session->set('code_verifier', $codeVerifier);
        $rTrim = rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '=');
        $codeChallenge = strtr($rTrim, '+/', '-_');
        $selectedIdentityProviders = $this->idpList($codeChallenge);
        $clientParams = isset($selectedIdentityProviders[$clientName])
            ? (array) ((array) $selectedIdentityProviders[$clientName])['params']
            : ['code_challenge' => $codeChallenge, 'code_challenge_method' => 'S256'];
        $clientAuthUrl = $client->buildAuthUrl($request, $clientParams);
        return $this->factory
                    ->createResponse(null, 302)
                    ->withHeader('Location', $clientAuthUrl);
    }

    public function callback(
        ServerRequestInterface $request,
        UserRepository $uR,
        string $_language,
    ): ResponseInterface {
        $qp           = $request->getQueryParams();
        $authclient   = $this->getStringQueryParam($qp, 'authclient');
        $code         = $this->getStringQueryParam($qp, 'code');
        $state        = $this->getStringQueryParam($qp, 'state');
        $sessionState = $this->getStringQueryParam($qp, 'session_state');
        $deviceId     = $this->getStringQueryParam($qp, 'device_id');

        if ($authclient === null) {
            throw new \InvalidArgumentException("Missing or invalid 'authclient'"
                    . " query parameter.");
        }

        $d = new CallbackDeps($this->translator, $uR);

        return match ($authclient) {
            'facebook' => $this->callbackFacebook($request, $d, $_language),
            'github' => $this->callbackGithub($request, $d, $_language, $code, $state),
            'google' => $this->callbackGoogle($request, $d, $_language, $code, $state),
            'linkedin' => $this->callbackLinkedIn($request, $d, $_language, $code, $state),
            'microsoftonline' => $this->callbackMicrosoftOnline($request, $d, $_language,
                    $code, $state, (string) $sessionState),
            'x' => $this->callbackX($request, $d, $_language, $code, $state),
            'vkontakte' => $this->callbackVKontakte($request, $d, $_language, $code,
                    $state, (string) $deviceId),
            'yandex' => $this->callbackYandex($request, $d, $_language, $code, $state),
            default => throw new \InvalidArgumentException(
                    "Unsupported 'authclient' value: {$authclient}"),
        };
    }

    public function login(
        ServerRequestInterface $request,
        TranslatorInterface $translator,
        FormHydrator $formHydrator,
        CookieLogin $cookieLogin,
    ): ResponseInterface {
        if (!$this->authService->isGuest()) {
            return $this->redirectToMain();
        }
        $loginForm = new LoginForm($this->authService, $translator);

        if ($request->getMethod() === Method::POST) {
            $ip    = $this->secHelper->getClientIpAddress($request);
            $body  = (array) $request->getParsedBody();
            $token = (string) ($body['cf-turnstile-response'] ?? '');
            /** @var array{login?: string} $loginBody */
            $loginBody = (array) ($body['Login'] ?? []);
            $submittedLogin = $loginBody['login'] ?? '';
            if (!$this->secHelper->checkRateLimit(hash('sha256', 'login_ctrl' . $ip))
                || !$this->secHelper->checkAccountRateLimit($submittedLogin)
                || !$this->verifyTurnstile($token, $ip)
            ) {
                return $this->webService->getRedirectResponse('auth/login');
            }
        }

        $response = null;
        if ($formHydrator->populateFromPostAndValidate($loginForm, $request)) {
            $response = $this->resolveLoginResponse($loginForm, $cookieLogin);
            if ($response === null) {
                $this->logout();
            }
        }

        $codeVerifier = Random::string(128);
        $this->session->set('code_verifier', $codeVerifier);
        $codeChallenge = strtr(rtrim(base64_encode(hash('sha256',
                $codeVerifier, true)), '='), '+/', '-_');
        $errors = $loginForm->isValidated()
            ? $loginForm->getValidationResult()->getErrorMessagesIndexedByProperty()
            : [];
        return $response ?? $this->webViewRenderer->render(
            'login',
            [
                'class' => $this->classList(),
                'formModel' => $loginForm,
                'errors' => $errors,
                //Fade-out CSS for TFA badge
                'styleTagFadeOut' =>  new Style()->content(
                    '.fade-out { opacity: 1; transition: opacity 40s ease-in; }'
                        . ' .fade-out.hidden { opacity: 0; }'),
                'request' => $request,
                'idpList' => $this->idpList(
                    $codeChallenge),
                'turnstileSiteKey' => ($this->getSetting)(new SettingKey('turnstile_site_key'))?->value() ?? '',
                'tfaEnabled' => ($this->getSetting)(new SettingKey('enable_tfa'))?->value() === '1',
                'tfaWithDisabling' => ($this->getSetting)(new SettingKey('enable_tfa_with_disabling'))?->value() === '1',
            ],
        );
    }

    /**
     * Validates the 'authState' session variable against the 'state' returned by
     * an identity provider. Ensures state integrity and prevents CSRF attacks.
     */
    protected function blockInvalidState(string $idP, string $state): void
    {
        // Early return if state is empty
        if ($state === '') {
            $param = "Invalid or empty OAuth2 state parameter from provider:";
            $this->logger->log(LogLevel::ALERT, $param . " {$idP}");
            exit(1);
        }

        // Sanitize state parameter to prevent injection attacks
        $sanitizedState = preg_replace('/[^a-zA-Z0-9\-_]/', '', $state);
        $chars = "State parameter contains invalid characters from provider:";
        if ($sanitizedState === '') {
            $this->logger->log(LogLevel::ALERT, $chars . " {$idP}");
            exit(1);
        }

        $authChoice = AuthChoice::widget();

        try {
            // raises an exception if the idP is not found
            $client = $authChoice->getClient($idP);
            /**
             * @var string|null $sessionState
             */
            $sessionState = $client->getSessionAuthState();

            if ($sessionState === null) {
                $this->logger->log(LogLevel::ALERT,
                    "Session Auth state is null for provider: {$idP}");
                exit(1);
            }

            // Use constant-time comparison to prevent timing attacks
            if (!$sessionState || !hash_equals($sessionState, $state)) {
                // State is invalid, possible cross-site request forgery.
                // Exit with an error code.
                $this->logger->log(LogLevel::ALERT,
                        "CSRF attack attempt detected for provider: {$idP}");
                exit(1);
            }
        } catch (\Exception $e) {
            // Log exception details for debugging
            $this->logger->log(LogLevel::ALERT,
            "Exception validating OAuth2 state for provider: {$idP}. Error: "
                . $e->getMessage());
            exit(1);
        }
    }

    /** @psalm-suppress PossiblyUnusedReturnValue */
    public function logout(): ResponseInterface
    {
        $identity = $this->authService->getIdentity();
        // getId() returns the identity table's own row id, not the user's —
        // see resolveLoginResponse() for why that matters here.
        $userId = $identity instanceof Identity ? $identity->getUserId() : null;
        $tfaEnabled = ($this->getSetting)(new SettingKey('enable_tfa'))?->value();
        $withDisabling = ($this->getSetting)(new SettingKey('enable_tfa_with_disabling'))?->value();
        // if enable_tfa_with_disabling setting has changed during login of admin
        // make sure this is reflected in the user setting.
        if ($withDisabling === '1' && $tfaEnabled === '1') {
            $this->clearTfaOnLogout(null !== $userId ? (string) $userId : null, $this->userRepository);
            $this->session->remove('verified_2fa_user_id');
        }
        // prevent session fixation
        $this->session->regenerateId();
        // Current — only clears data, keeps session alive
        $this->session->clear();
        $this->authService->logout();
        return $this->redirectToMain();
    }

    public function regenerateCodes(): ResponseInterface
    {
        $this->session->set('regenerate_codes', true);
        return $this->webService->getRedirectResponse('auth/verifyLogin');
    }

    private function redirectToMain(): ResponseInterface
    {
        return $this->webService->getRedirectResponse('site/index',
                ['_language' => 'en']);
    }

    private function redirectToShellIndex(): ResponseInterface
    {
        return $this->webService->getRedirectResponse('shell/setting/index');
    }

    protected function redirectToAccountDisabled(): ResponseInterface
    {
        return $this->webService->getRedirectResponse('site/accountdisabled',
                ['_language' => 'en']);
    }

    /**
     * OAuth2 providers enforce their own MFA before issuing an authorization
     * code, so no additional TOTP challenge is applied here — just the same
     * active-account gate local login goes through.
     */
    private function tfaCheckBeforeRedirects(): ResponseInterface
    {
        $identity = $this->authService->getIdentity();
        // getId() returns the identity table's own row id, not the user's —
        // see resolveLoginResponse() for why that matters here.
        $userId = $identity instanceof Identity ? $identity->getUserId() : null;
        if (null === $userId) {
            return $this->redirectToMain();
        }
        $user = $this->userRepository->findById($userId);
        if (!$user->isActive()) {
            $this->authService->logout();
            return $this->redirectToAccountDisabled();
        }
        $this->session->regenerateId();
        $this->session->set('tfa_verified', true);
        return $this->redirectToShellIndex();
    }

    private function isAdminUser(string $userId): bool
    {
        $userRoles = $this->manager->getRolesByUserId($userId);
        $isAdminUser = false;
        foreach ($userRoles as $role) {
            if ($role->getName() === Roles::ADMIN) {
                $isAdminUser = true;
                break;
            }
        }
        return $isAdminUser;
    }

    private function getStringQueryParam(array $qp, string $key): ?string
    {
        return (isset($qp[$key]) && is_string($qp[$key]) && $qp[$key] !== '')
            ? $qp[$key]
            : null;
    }

    private function resolveLoginResponse(
        LoginForm $loginForm,
        CookieLogin $cookieLogin,
    ): ?ResponseInterface {
        $identity = $this->authService->getIdentity();
        // getId() returns the identity table's own row id, not the user's —
        // those two frequently diverge (see docs/IDENTITY_VS_USER_ID_AUTH_FIX.md),
        // which silently broke every lookup below for any account where they
        // don't coincidentally match.
        $userId = $identity instanceof Identity ? $identity->getUserId() : null;
        $user = null !== $userId ? $this->userRepository->findById($userId) : null;
        if (null === $userId || null === $user) {
            return null;
        }
        $userIdString = (string) $userId;
        // 2FA is mandatory for admins regardless of the global enable_tfa
        // setting — an admin without TOTP set up is routed into setup by
        // handleTfaPath() below, same as any other 2FA-required user.
        $tfaEnabled = ($this->getSetting)(new SettingKey('enable_tfa'))?->value();
        if ($tfaEnabled === '1' || $this->isAdminUser($userIdString)) {
            return $this->handleTfaPath($userIdString, $user);
        }
        return $this->handleNonTfaPath($userIdString, $user, $cookieLogin, $loginForm);
    }

    private function handleTfaPath(string $userId, User $user): ResponseInterface
    {
        $this->session->set('tfa_verified', false);
        $enabled = $user->is2FAEnabled();
        if (!$enabled) {
            $this->session->set('pending_2fa_user_id', $userId);
            return $this->webService->getRedirectResponse('auth/showSetup');
        }
        $this->session->set('verified_2fa_user_id', $userId);
        return $this->webService->getRedirectResponse('auth/verifyLogin');
    }

    private function handleNonTfaPath(
        string $userId,
        User $user,
        CookieLogin $cookieLogin,
        LoginForm $loginForm,
    ): ResponseInterface {
        $this->session->set('tfa_verified', true);
        if (!$user->isActive()) {
            $this->authService->logout();
            return $this->redirectToAccountDisabled();
        }
        $this->session->regenerateId();
        $identity = $this->authService->getIdentity();
        return ($identity instanceof CookieLoginIdentityInterface
                && $loginForm->getPropertyValue('rememberMe'))
            ? $cookieLogin->addCookie($identity, $this->redirectToShellIndex())
            : $this->redirectToShellIndex();
    }
}
