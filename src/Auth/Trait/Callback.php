<?php

declare(strict_types=1);

namespace App\Auth\Trait;

use App\Auth\CallbackDeps;
use App\Auth\Roles;
use App\Infrastructure\Persistence\User\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LogLevel;
use Yiisoft\Input\Http\Attribute\Parameter\Query;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Security\Random;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\AuthClient\Widget\AuthChoice;

/**
 * OAuth2 provider callbacks. On first login for a given provider identity, a
 * new User is created, assigned a default role, and logged straight in — no
 * separate "click to confirm/activate" step, since the provider has already
 * verified control of the account. Contrast with local signup
 * (SignupController), which is otherwise identical minus the provider hop.
 */
trait Callback
{
    /**
     * Purpose: Once Facebook redirects to this callback, the user is logged
     * in, or a new user is created and logged in directly.
     * @param ServerRequestInterface $request
     * @param CallbackDeps $d
     * @param string $_language
     * @return ResponseInterface
     */
    public function callbackFacebook(
        ServerRequestInterface $request,
        CallbackDeps $d,
        #[RouteArgument('_language')]
        string $_language,
    ): ResponseInterface {
        /** @var array<string, string> $query */
        $query       = $request->getQueryParams();
        $code        = $query['code']         ?? null;
        $state       = $query['state']        ?? null;
        $error       = $query['error']        ?? null;
        $errorCode   = $query['error_code']   ?? null;
        $errorReason = $query['error_reason'] ?? null;
        if ($code === null || $state === null) {
// e.g. User presses cancel button: callbackFacebook?error=access_denied&error_code=200&error_description=Permissions+error&error_reason=user_denied&state=
            return (($errorCode == 200) && ($error == 'access_denied') && ($errorReason == 'user_denied'))
                ? $this->redirectToUserCancelledOauth2()
                : $this->redirectToOauth2AuthError(
                    $d->translator->translate('oauth2.missing.authentication.code.'
                            . 'or.state.parameter'));
        }

        $this->blockInvalidState('facebook', $state);
        $facebook = (AuthChoice::widget())->getClient('facebook');
        $facebookId = 0;
        $facebookLogin = '';
        $userArray = [];
        $response = null;

        if (strlen($code) == 0) {
// If we don't have an authorization code then get one
// and use the protected function oauth2->generateAuthState to generate state param
// which has a session id built into it
            $authorizationUrl = $facebook->buildAuthUrl($request, []);
            $response = $this->webService->getRedirectResponse($authorizationUrl);
        } elseif ($code == 401) {
            $response = $this->redirectToOauth2CallbackResultUnAuthorised();
        } elseif (strlen($state) == 0) {
            // State is invalid, possible cross-site request forgery.
            $response = $this->redirectToOauth2AuthError(
                $d->translator->translate('oauth2.missing.state.parameter.'
                        . 'possible.csrf.attack'));
        } else {
            /** @psalm-var \Yiisoft\Yii\AuthClient\Client\Facebook $facebook */
            $oAuthTokenType = $facebook->fetchAccessToken($request, $code, []);
            $userArray = $facebook->getCurrentUserJsonArray($oAuthTokenType);
            /**
             * @var int $userArray['id']
             */
            $facebookId = $userArray['id'] ?? 0;
            /**
             * @var string $userArray['name']
             */
            $facebookLogin = strtolower($userArray['name'] ?? '');
            if ($facebookId <= 0 || strlen($facebookLogin) == 0) {
                $this->authService->logout();
                $response = $this->redirectToMain();
            }
        }
        if ($response !== null) {
            return $response;
        }
        // the id will be removed in the logout button
        $login = 'facebook' . (string) $facebookId . $facebookLogin;
        /**
         * @var string $userArray['email']
         */
        $email = $userArray['email'] ?? 'noemail' . $login . '@facebook.com';
        $password = Random::string(32);
        return $this->oauthRegisterAndProceed('facebook', $login, $email, $password, $d);
    }

    /**
     * Purpose: Once Github redirects to this callback, the user is logged
     * in, or a new user is created and logged in directly.
     * @see https://docs.github.com/en/apps/oauth-apps/building-oauth-apps/
     *       authorizing-oauth-apps
     * @param CallbackDeps $d
     * @param string $_language
     * @param string|null $code
     * @param string|null $state
     * @return ResponseInterface
     */
    public function callbackGithub(
        ServerRequestInterface $request,
        CallbackDeps $d,
        #[RouteArgument('_language')]
        string $_language,
        #[Query('code')]
        ?string $code = null,
        #[Query('state')]
        ?string $state = null,
    ): ResponseInterface {
        if ($code == null || $state == null) {
            return $this->redirectToOauth2AuthError(
                $d->translator->translate('oauth2.missing.authentication.code.'
                        . 'or.state.parameter'));
        }

        $this->blockInvalidState('github', $state);
        $github = (AuthChoice::widget())->getClient('github');
        /** @psalm-suppress DocblockTypeContradiction $code */
        if (strlen($code) == 0 || $code == 401 || strlen($state) == 0) {
            return match(true) {
                strlen($code) == 0 => $this->webService->getRedirectResponse(
                    $github->buildAuthUrl($request, [])),
                $code == 401 => $this->redirectToOauth2CallbackResultUnAuthorised(),
                default => $this->redirectToOauth2AuthError(
                    $d->translator->translate('oauth2.missing.state.parameter'
                        . '.possible.csrf.attack')),
            };
        }
        /** @psalm-var \Yiisoft\Yii\AuthClient\Client\GitHub $github */
        $oAuthTokenType = $github->fetchAccessToken($request, $code, []);
        $userArray = $github->getCurrentUserJsonArray($oAuthTokenType);
        /**
         * @var int $userArray['id']
         */
        $githubId = $userArray['id'] ?? 0;
        if ($githubId <= 0) {
            $this->authService->logout();
        }
        // Append github in case user has used same login for other identity providers
        // the id will be removed in the logout button
        $login = 'github' . (string) $githubId . 'g';
        /**
         * @var string $userArray['email']
         */
        $email = $userArray['email'] ?? 'noemail' . $login . '@github.com';
        $password = Random::string(32);
        return $githubId <= 0
            ? $this->redirectToMain()
            : $this->oauthRegisterAndProceed('github', $login, $email, $password, $d);
    }

    /**
     * @see https://console.cloud.google.com/apis/credentials?project=YOUR_PROJECT
     */
    public function callbackGoogle(
        ServerRequestInterface $request,
        CallbackDeps $d,
        #[RouteArgument('_language')]
        string $_language,
        #[Query('code')]
        ?string $code = null,
        #[Query('state')]
        ?string $state = null,
    ): ResponseInterface {
        if ($code == null || $state == null) {
            return $this->redirectToOauth2AuthError(
                    $d->translator->translate(
                        'oauth2.missing.authentication.code.or.state.parameter'));
        }

        $this->blockInvalidState('google', $state);
        $google = (AuthChoice::widget())->getClient('google');

        /**
         * @psalm-suppress DocblockTypeContradiction $code
         * @psalm-suppress DocblockTypeContradiction $state
         */
        if (strlen($code) == 0 || $code == 401 || strlen($state) == 0) {
            return match(true) {
                strlen($code) == 0 => $this->webService->getRedirectResponse(
                    $google->buildAuthUrl($request, [])),
                $code == 401 => $this->redirectToOauth2CallbackResultUnAuthorised(),
                default => $this->redirectToOauth2AuthError(
                    $d->translator->translate(
                        'oauth2.missing.state.parameter.possible.csrf.attack')),
            };
        }

        /** @psalm-var \Yiisoft\Yii\AuthClient\Client\Google $google */
        $oAuthTokenType = $google->fetchAccessToken($request, $code, [
            'grant_type' => 'authorization_code',
        ]);
        $userArray = $google->getCurrentUserJsonArray($oAuthTokenType);
        /** @var int $userArray['id'] */
        $googleId = $userArray['id'] ?? 0;
        if ($googleId <= 0) {
            $this->authService->logout();
        }
        // the id will be removed in the logout button
        $login = 'google' . (string) $googleId;
        /** @var string $userArray['email'] */
        $email = $userArray['email'] ?? 'noemail' . $login . '@google.com';
        $password = Random::string(32);
        return $googleId <= 0
            ? $this->redirectToMain()
            : $this->oauthRegisterAndProceed('google', $login, $email, $password, $d);
    }

    public function callbackLinkedIn(
        ServerRequestInterface $request,
        CallbackDeps $d,
        #[RouteArgument('_language')]
        string $_language,
        #[Query('code')]
        ?string $code = null,
        #[Query('state')]
        ?string $state = null,
    ): ResponseInterface {
        if ($code == null || $state == null) {
            return $this->redirectToOauth2AuthError(
                    $d->translator->translate(
                        'oauth2.missing.authentication.code.or.state.parameter'));
        }

        $this->blockInvalidState('linkedin', $state);
        $linkedIn = (AuthChoice::widget())->getClient('linkedin');

        /**
         * @psalm-suppress DocblockTypeContradiction $code
         * @psalm-suppress DocblockTypeContradiction $state
         */
        if (strlen($code) == 0 || $code == 401 || strlen($state) == 0) {
            return match(true) {
                strlen($code) == 0 => $this->webService->getRedirectResponse(
                    $linkedIn->buildAuthUrl($request, [])),
                $code == 401 => $this->redirectToOauth2CallbackResultUnAuthorised(),
                default => $this->redirectToOauth2AuthError(
                    $d->translator->translate(
                        'oauth2.missing.state.parameter.possible.csrf.attack')),
            };
        }

        $params = [
            'grant_type' => 'authorization_code',
            'redirect_uri' => $linkedIn->getOauth2ReturnUrl(),
        ];
        /** @psalm-var \Yiisoft\Yii\AuthClient\Client\LinkedIn $linkedIn */
        $oAuthTokenType = $linkedIn->fetchAccessToken($request, $code, $params);
        $userArray = $linkedIn->getCurrentUserJsonArray($oAuthTokenType);
        /** @var string $userArray['sub'] e.g. P1c9jkRFSy — sub is returned instead of an id */
        $linkedInSub = $userArray['sub'] ?? '';
        if (strlen($linkedInSub) == 0) {
            $this->authService->logout();
        }
        /** @var string $userArray['name'] */
        $linkedInName = $userArray['name'] ?? 'unknown';
        $login = 'linkedIn' . $linkedInName;
        /** @var string $userArray['email'] */
        $email = $userArray['email'] ?? 'noemail' . $login . '@linkedin.com';
        $password = Random::string(32);
        return strlen($linkedInSub) == 0
            ? $this->redirectToMain()
            : $this->oauthRegisterAndProceed('linkedin', $login, $email, $password, $d);
    }

    public function callbackMicrosoftOnline(
        ServerRequestInterface $request,
        CallbackDeps $d,
        #[RouteArgument('_language')]
        string $_language,
        #[Query('code')]
        ?string $code = null,
        #[Query('state')]
        ?string $state = null,
        #[Query('session_state')]
        ?string $sessionState = null,
    ): ResponseInterface {
        if ($code == null || $state == null || $sessionState == null) {
            return $this->redirectToOauth2AuthError(
                    $d->translator->translate(
                        'oauth2.missing.authentication.code.or.state.parameter'));
        }

        $this->blockInvalidState('microsoftonline', $state);
        $microsoftOnline = (AuthChoice::widget())->getClient('microsoftonline');

        /**
         * @psalm-suppress DocblockTypeContradiction $code
         * @psalm-suppress DocblockTypeContradiction $state
         * @psalm-suppress DocblockTypeContradiction $sessionState
         */
        if (strlen($code) == 0 || $code == '401' || strlen($state) == 0 || strlen($sessionState) == 0) {
            return match(true) {
                strlen($code) == 0 => $this->webService->getRedirectResponse(
                    $microsoftOnline->buildAuthUrl($request, [])),
                $code == '401' => $this->redirectToOauth2CallbackResultUnAuthorised(),
                default => $this->redirectToOauth2AuthError(
                    $d->translator->translate(
                        'oauth2.missing.state.parameter.possible.csrf.attack')),
            };
        }

        /** @psalm-var \Yiisoft\Yii\AuthClient\Client\MicrosoftOnline $microsoftOnline */
        $oAuthTokenType = $microsoftOnline->fetchAccessToken($request, $code, [
            'grant_type' => 'authorization_code',
        ]);
        $userArray = $microsoftOnline->getCurrentUserJsonArray($oAuthTokenType);
        /** @var int $userArray['id'] */
        $microsoftOnlineId = $userArray['id'] ?? 0;
        if ($microsoftOnlineId <= 0) {
            $this->authService->logout();
        }
        // Append the last four digits of the Id
        $idStr = (string) $microsoftOnlineId;
        $login = 'ms' . substr($idStr, strlen($idStr) - 4, strlen($idStr));
        /** @var string $userArray['email'] */
        $email = $userArray['email'] ?? 'noemail' . $login . '@microsoftonline.com';
        $password = Random::string(32);
        return $microsoftOnlineId <= 0
            ? $this->redirectToMain()
            : $this->oauthRegisterAndProceed('microsoftonline', $login, $email, $password, $d);
    }

    public function callbackX(
        ServerRequestInterface $request,
        CallbackDeps $d,
        #[RouteArgument('_language')]
        string $_language,
        #[Query('code')]
        ?string $code = null,
        #[Query('state')]
        ?string $state = null,
    ): ResponseInterface {
        if ($code == null || $state == null) {
            return $this->redirectToOauth2AuthError(
                $d->translator->translate('oauth2.missing.authentication.code.'
                    . 'or.state.parameter'));
        }

        $this->blockInvalidState('x', $state);
        $x = (AuthChoice::widget())->getClient('x');

        $login = '';
        $email = '';
        $password = '';
        $response = null;

        /**
         * @psalm-suppress DocblockTypeContradiction $code
         */
        if (strlen($code) == 0) {
            $codeVerifier = Random::string(128);
            $codeChallenge = strtr(rtrim(base64_encode(hash('sha256',
                    $codeVerifier, true)), '='), '+/', '-_');
            // Store code_verifier in session or other storage
            $this->session->set('code_verifier', $codeVerifier);
            $authorizationUrl = $x->buildAuthUrl(
                $request,
                [
                    'code_challenge' => $codeChallenge,
                    'code_challenge_method' => 'S256',
                ],
            );
            $response = $this->webService->getRedirectResponse($authorizationUrl);
        } elseif ($code == 401) {
            $response = $this->redirectToOauth2CallbackResultUnAuthorised();
        } elseif (strlen($state) == 0) {
            /**
             * @psalm-suppress DocblockTypeContradiction $state
             */
            $response = $this->redirectToOauth2AuthError(
                $d->translator->translate('oauth2.missing.state.parameter.'
                        . 'possible.csrf.attack'));
        } else {
            $codeVerifier = (string) $this->session->get('code_verifier');
            $params = [
                'grant_type' => 'authorization_code',
                'redirect_uri' => $x->getOauth2ReturnUrl(),
                'code_verifier' => $codeVerifier,
            ];
            /** @psalm-var \Yiisoft\Yii\AuthClient\Client\X $x */
            $oAuthTokenType = $x->fetchAccessTokenWithCodeVerifier(
                $request, $code, $params);
            $userArray = $x->getCurrentUserJsonArray($oAuthTokenType);
            /**
             * @var array $userArray['data']
             */
            $data = $userArray['data'] ?? [];
            /**
             * @var int $data['id']
             */
            $xId = $data['id'] ?? 0;
            $xLogin = (string) ($data['username'] ?? '');
            if ($xId <= 0 || strlen($xLogin) == 0) {
                $this->authService->logout();
                $response = $this->redirectToMain();
            } else {
                $login = 'twitter' . (string) $xId . $xLogin;
                /**
                 * @var string $userArray['email']
                 */
                $email = $userArray['email'] ?? 'noemail' . $login . '@x.com';
                $password = Random::string(32);
            }
        }
        if ($response !== null) {
            return $response;
        }
        return $this->oauthRegisterAndProceed('x', $login, $email, $password, $d);
    }

    public function callbackVKontakte(
        ServerRequestInterface $request,
        CallbackDeps $d,
        #[RouteArgument('_language')]
        string $_language,
        #[Query('code')]
        ?string $code = null,
        #[Query('state')]
        ?string $state = null,
        #[Query('device_id')]
        ?string $device_id = null,
    ): ResponseInterface {
        if ($code == null || $state == null) {
            return $this->redirectToOauth2AuthError(
                $d->translator->translate('oauth2.missing.authentication.code.'
                    . 'or.state.parameter'));
        }

        $this->blockInvalidState('vkontakte', $state);

        $vkontakte = (AuthChoice::widget())->getClient('vkontakte');

        $earlyResponse = null;
        /** @psalm-suppress DocblockTypeContradiction $code */
        if (strlen($code) == 0) {
            $codeVerifier = Random::string(128);
            $codeChallenge = strtr(rtrim(base64_encode(hash('sha256',
                    $codeVerifier, true)), '='), '+/', '-_');
            $this->session->set('code_verifier', $codeVerifier);
            $authorizationUrl = $vkontakte->buildAuthUrl(
                $request,
                [
                    'code_challenge' => $codeChallenge,
                    'code_challenge_method' => 'S256',
                    'device_id' => $device_id,
                ],
            );
            $earlyResponse = $this->webService->getRedirectResponse($authorizationUrl);
        } elseif ($code == 401) {
            $earlyResponse = $this->redirectToOauth2CallbackResultUnAuthorised();
        } elseif (strlen($state) == 0) {
            /** @psalm-suppress DocblockTypeContradiction $state */
            $earlyResponse = $this->redirectToOauth2AuthError(
                $d->translator->translate('oauth2.missing.state.parameter.'
                    . 'possible.csrf.attack'));
        }
        if ($earlyResponse !== null) {
            return $earlyResponse;
        }
        $codeVerifier = (string) $this->session->get('code_verifier');
        $params = [
            'device_id' => $device_id,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $vkontakte->getOauth2ReturnUrl(),
            'code_verifier' => $codeVerifier,
        ];

        /**
         * $oAuthTokenType = e.g.    'refresh_token' => '{string}'
         *                           'access_token' => '{string}'
         *                           'id_token' => '{string}'
         *                           'token_type' => 'Bearer'
         *                           'expires_in' => 3600
         *                           'user_id' => 1023583333
         *                           'state' => '{string}'
         *                           'scope' => 'vkid.personal_info email'
         */
        $oAuthTokenType = $vkontakte->fetchAccessTokenWithCodeVerifier(
            $request, $code, $params);

        /**
         * e.g.  'user' => [
         *          'user_id' => '1023581111'
         *          'first_name' => 'Joe'
         *          'last_name' => 'Bloggs'
         *          'avatar' => 'https://..'
         *          'email' => ''
         *          'sex' => 2
         *          'verified' => false
         *          'birthday' => '09.09.1999'
         *       ]
         * @psalm-var \Yiisoft\Yii\AuthClient\Client\VKontakte $vkontakte
         */
        $userArray =
            $vkontakte->step8ObtainingUserDataArrayWithClientId(
                $oAuthTokenType, $vkontakte->getClientId(),
                    $this->configWebDiAuthGuzzle, $this->requestFactory);

        /**
         * @var array $userArray['user']
         */
        $user = $userArray['user'] ?? [];

        /**
         * @var int $user['user_id']
         */
        $id = $user['user_id'] ?? 0;
        if ($id <= 0) {
            $this->authService->logout();
        }
        /**
         * @var string $user['first_name']
         */
        $userFirstName = $user['first_name'] ?? 'unknown';
        /**
         * @var string $user['last_name']
         */
        $userLastName = $user['last_name'] ?? 'unknown';
        $userName = (strlen($userFirstName) > 0 && strlen($userLastName) > 0)
            ? $userFirstName . ' ' . $userLastName
            : 'fullname unknown';
        // Append the last four digits of the Id
        $login = '' . $userName
                . substr((string) $id, strlen((string) $id) - 4,
                        strlen((string) $id));
        /**
         * @var string $userArray['email']
         */
        $email = $userArray['email'] ?? 'noemail' . $login . '@vk.ru';
        $password = Random::string(32);
        return $id <= 0
            ? $this->redirectToMain()
            : $this->oauthRegisterAndProceed('vkontakte', $login, $email, $password, $d);
    }

    public function callbackYandex(
        ServerRequestInterface $request,
        CallbackDeps $d,
        #[RouteArgument('_language')]
        string $_language,
        #[Query('code')]
        ?string $code = null,
        #[Query('state')]
        ?string $state = null,
    ): ResponseInterface {
        if ($code == null || $state == null) {
            return $this->redirectToOauth2AuthError(
                $d->translator->translate('oauth2.missing.authentication.code.'
                        . 'or.state.parameter'));
        }

        $this->blockInvalidState('yandex', $state);
        $yandex = (AuthChoice::widget())->getClient('yandex');

        /**
         * @psalm-suppress DocblockTypeContradiction $code
         * @psalm-suppress DocblockTypeContradiction $state
         */
        if (strlen($code) == 0 || $code == 401 || strlen($state) == 0) {
            return $this->yandexCodeGuard($yandex, $request, $code, $d->translator);
        }

        $codeVerifier = (string) $this->session->get('code_verifier');
        $params = [
            'grant_type' => 'authorization_code',
            'redirect_uri' => $yandex->getOauth2ReturnUrl(),
            'code_verifier' => $codeVerifier,
        ];
        /** @psalm-var \Yiisoft\Yii\AuthClient\Client\Yandex $yandex */
        $oAuthTokenType = $yandex->fetchAccessTokenWithCodeVerifier($request, $code, $params);
        $userArray = $yandex->getCurrentUserJsonArray($oAuthTokenType);
        /** @var int $userArray['id'] */
        $id = $userArray['id'] ?? 0;
        if ($id <= 0) {
            $this->authService->logout();
        }
        /** @var string $userArray['login'] e.g. john.doe.com */
        $idStr = (string) $id;
        $login = 'yx' . $userArray['login'] . substr($idStr, strlen($idStr) - 4, strlen($idStr));
        $email = 'noemail' . $login . '@yandex.com';
        $password = Random::string(32);
        return $id <= 0
            ? $this->redirectToMain()
            : $this->oauthRegisterAndProceed('yandex', $login, $email, $password, $d);
    }

    /**
     * OAuth2 providers (Google, GitHub, Microsoft, LinkedIn, Facebook etc.)
     * enforce their own MFA before issuing an authorization code. By the time
     * any callback fires, the user has already passed the provider's own
     * security checks. Applying an additional TOTP challenge is therefore
     * redundant and is skipped entirely for all OAuth2 logins.
     * TFA is only applied to the local username/password login path.
     */
    private function yandexCodeGuard(
        object $yandex,
        ServerRequestInterface $request,
        string $code,
        TranslatorInterface $translator,
    ): ResponseInterface {
        if (strlen($code) == 0) {
            /** @psalm-var \Yiisoft\Yii\AuthClient\Client\Yandex $yandex */
            $codeVerifier = Random::string(128);
            $codeChallenge = strtr(
                rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '='),
                '+/', '-_'
            );
            $this->session->set('code_verifier', $codeVerifier);
            return $this->webService->getRedirectResponse(
                $yandex->buildAuthUrl($request, [
                    'code_challenge' => $codeChallenge,
                    'code_challenge_method' => 'S256',
                ])
            );
        }
        return $code == 401
            ? $this->redirectToOauth2CallbackResultUnAuthorised()
            : $this->redirectToOauth2AuthError(
                $translator->translate(
                    'oauth2.missing.state.parameter.possible.csrf.attack'));
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
        $this->logger->log(
            LogLevel::INFO,
            'RBAC: assigned role=' . $role . ' to userId=' . $userId,
        );
        return true;
    }

    /**
     * An existing user with this provider-derived login logs straight in
     * (subject to the normal 2FA gate); a new one is registered and logged
     * in immediately — the identity provider has already verified control
     * of the account, so there's no separate email-click activation step
     * here (contrast with local signup's email verification).
     */
    private function oauthRegisterAndProceed(
        string $provider,
        string $login,
        string $email,
        string $password,
        CallbackDeps $d,
    ): ResponseInterface {
        if ($this->authService->oauthLogin($login)) {
            return $this->tfaCheckBeforeRedirects();
        }
        return $this->registerNewOauthUser($login, $email, $password, $d);
    }

    private function registerNewOauthUser(
        string $login,
        string $email,
        string $password,
        CallbackDeps $d,
    ): ResponseInterface {
        $oauthUser = new User($login, $email, $password);
        $d->uR->save($oauthUser);
        $userId = $oauthUser->reqId();
        if ($userId <= 0) {
            $this->authService->logout();
            return $this->redirectToMain();
        }
        $role = $d->uR->repoCount() == 1 ? Roles::ADMIN : Roles::USER;
        if (!$this->assignRoleAndVerify($userId, $role)) {
            return $this->redirectToMain();
        }
        return $this->authService->oauthLogin($login)
            ? $this->tfaCheckBeforeRedirects()
            : $this->redirectToMain();
    }

    private function redirectToOauth2AuthError(string $message): ResponseInterface
    {
        return $this->webService->getRedirectResponse('site/oauth2autherror', [
            'message' => $message,
        ]);
    }

    private function redirectToUserCancelledOauth2(): ResponseInterface
    {
        return $this->webService->getRedirectResponse('site/usercancelledoauth2',
                ['_language' => 'en']);
    }

    private function redirectToOauth2CallbackResultUnAuthorised(): ResponseInterface
    {
        return $this->webService->getRedirectResponse(
            'site/oauth2callbackresultunauthorised', ['_language' => 'en']);
    }
}
