<?php

declare(strict_types=1);

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Definitions\Reference;
use Yiisoft\Factory\Factory;
use Yiisoft\Session\SessionInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\AuthClient\StateStorage\StateStorageInterface;
use Yiisoft\Yii\AuthClient\StateStorage\SessionStateStorage;
use Yiisoft\Yii\AuthClient\Client\Facebook;
use Yiisoft\Yii\AuthClient\Client\GitHub;
use Yiisoft\Yii\AuthClient\Client\Google;
use Yiisoft\Yii\AuthClient\Client\LinkedIn;
use Yiisoft\Yii\AuthClient\Client\MicrosoftOnline;
use Yiisoft\Yii\AuthClient\Client\VKontakte;
use Yiisoft\Yii\AuthClient\Client\X;
use Yiisoft\Yii\AuthClient\Client\Yandex;
use Yiisoft\Yii\AuthClient\AuthAction;
use Yiisoft\Yii\AuthClient\Widget\AuthChoice;
use Yiisoft\Yii\AuthClient\Collection;

/**
 * Generic social-login providers only — no HMRC/GovUk/OpenBanking clients
 * (those were rossaddison/invoice-specific forks/integrations tied to UK tax
 * software, not part of this template).
 *
 * @var array $params
 * @var array $params['yiisoft/yii-auth-client']
 */
$paramsYiisoft = $params['yiisoft/yii-auth-client'];
/** @var array $paramsClients */
$paramsClients = $paramsYiisoft['clients'];

/** @var array $paramsClients['facebook'] **/
$facebookClient = $paramsClients['facebook'];
/** @var array $paramsClients['github'] **/
$githubClient = $paramsClients['github'];
/** @var array $paramsClients['google'] **/
$googleClient = $paramsClients['google'];
/** @var array $paramsClients['linkedin'] **/
$linkedinClient = $paramsClients['linkedin'];
/** @var array $paramsClients['microsoftonline'] **/
$microsoftonlineClient = $paramsClients['microsoftonline'];
/** @var array $paramsClients['vkontakte'] **/
$vkontakteClient = $paramsClients['vkontakte'];
/** @var array $paramsClients['x'] **/
$xClient = $paramsClients['x'];
/** @var array $paramsClients['yandex'] **/
$yandexClient = $paramsClients['yandex'];

$constructArray = [
    'httpClient'     => Reference::to(ClientInterface::class),
    'requestFactory' => Reference::to(RequestFactoryInterface::class),
    'stateStorage'   => Reference::to(StateStorageInterface::class),
    'factory'        => Reference::to(Factory::class),
    'session'        => Reference::to(SessionInterface::class),
];

$construct = '__construct()';
$setClientId = 'setClientId()';
$setClientSecret = 'setClientSecret()';
$setReturnUrl = 'setReturnUrl()';
$setTenant = 'setTenant()';

return [
    // SessionInterface itself is bound in yiisoft/session's own di-web
    // config — reuse that single shared session rather than shadowing it.
    SessionStateStorage::class => [
        $construct => [
            'session' => Reference::to(SessionInterface::class),
        ],
    ],
    StateStorageInterface::class => Reference::to(SessionStateStorage::class),
    Facebook::class => [
        $construct => $constructArray,
        $setClientId => [$facebookClient['clientId']],
        $setClientSecret => [$facebookClient['clientSecret']],
        $setReturnUrl => [$facebookClient['returnUrl']],
    ],
    GitHub::class => [
        $construct => $constructArray,
        $setClientId => [$githubClient['clientId']],
        $setClientSecret => [$githubClient['clientSecret']],
        $setReturnUrl => [$githubClient['returnUrl']],
    ],
    Google::class => [
        $construct => $constructArray,
        $setClientId => [$googleClient['clientId']],
        $setClientSecret => [$googleClient['clientSecret']],
        $setReturnUrl => [$googleClient['returnUrl']],
    ],
    LinkedIn::class => [
        $construct => $constructArray,
        $setClientId => [$linkedinClient['clientId']],
        $setClientSecret => [$linkedinClient['clientSecret']],
        $setReturnUrl => [$linkedinClient['returnUrl']],
    ],
    MicrosoftOnline::class => [
        $construct => $constructArray,
        $setClientId => [$microsoftonlineClient['clientId']],
        $setClientSecret => [$microsoftonlineClient['clientSecret']],
        $setReturnUrl => [$microsoftonlineClient['returnUrl']],
        $setTenant => [$microsoftonlineClient['tenant']],
    ],
    VKontakte::class => [
        $construct => $constructArray,
        $setClientId => [$vkontakteClient['clientId']],
        $setClientSecret => [$vkontakteClient['clientSecret']],
        $setReturnUrl => [$vkontakteClient['returnUrl']],
    ],
    X::class => [
        $construct => $constructArray,
        $setClientId => [$xClient['clientId']],
        $setClientSecret => [$xClient['clientSecret']],
        $setReturnUrl => [$xClient['returnUrl']],
    ],
    Yandex::class => [
        $construct => $constructArray,
        $setClientId => [$yandexClient['clientId']],
        $setClientSecret => [$yandexClient['clientSecret']],
        $setReturnUrl => [$yandexClient['returnUrl']],
    ],
    Collection::class => [
        $construct => [
            'clients' => [
                'facebook' => Reference::to(Facebook::class),
                'github' => Reference::to(GitHub::class),
                'google' => Reference::to(Google::class),
                'linkedin' => Reference::to(LinkedIn::class),
                'microsoftonline' => Reference::to(MicrosoftOnline::class),
                'vkontakte' => Reference::to(VKontakte::class),
                'x' => Reference::to(X::class),
                'yandex' => Reference::to(Yandex::class),
            ],
        ],
    ],
    // Applied in: resources/views/auth/login.php
    AuthChoice::class => [
        $construct => [
            'clientCollection' => Reference::to(Collection::class),
            'assetManager' => Reference::to(AssetManager::class),
        ],
    ],
    AuthAction::class => [
        $construct => [
            'clientCollection' => Reference::to(Collection::class),
            'aliases' => Reference::to(Aliases::class),
            'webView' => Reference::to(WebView::class),
            'responseFactory' => Reference::to(ResponseFactoryInterface::class),
        ],
    ],
];
