<?php

declare(strict_types=1);

use Yiisoft\Bootstrap5\Assets\BootstrapAsset;
use Yiisoft\Bootstrap5\Assets\BootstrapCdnAsset;
use Yiisoft\Cookies\CookieMiddleware;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\RequestProvider\RequestCatcherMiddleware;
use Yiisoft\Router\Middleware\Router;
use Yiisoft\Session\SessionMiddleware;
use Yiisoft\User\Login\Cookie\CookieLoginMiddleware;
use App\Middleware\ContentSecurityPolicyMiddleware;
use App\Middleware\CsrfExemptMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use Yiisoft\Yii\Middleware\Locale;

return [
    'locale' => [
        'locales' => [
            'en' => 'en-US',
        ],
        'ignoredRequests' => [
            '/gii**',
            '/debug**',
            '/inspect**',
        ],
    ],
    'middlewares' => [
        RequestCatcherMiddleware::class,
        ErrorCatcher::class,
        ContentSecurityPolicyMiddleware::class,
        SecurityHeadersMiddleware::class,
        SessionMiddleware::class,
        CsrfExemptMiddleware::class,
        CookieMiddleware::class,
        CookieLoginMiddleware::class,
        Locale::class,
        Router::class,
    ],

    // Content-Security-Policy directives. jsdelivr.net is allowlisted for
    // the Bootstrap 5 CDN assets the bare Shell layout loads directly
    // (see resources/views/shell/layout/main.php) — drop this once you
    // switch to bundled local assets.
    'csp' => [
        'policy' => implode('; ', [
            "default-src 'self'",
            "script-src 'self' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "font-src 'self' https://cdn.jsdelivr.net",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "frame-src 'self'",
            "child-src 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
            "manifest-src 'self'",
            "worker-src 'self'",
        ]),
    ],
    // Guaranteed regardless of the web server in front of the app.
    'security-headers' => [
        'headers' => [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(),'
                . ' payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()',
        ],
    ],
    // Overrides yiisoft/session's package default (cookie_secure => 0), but
    // only once SESSION_COOKIE_SECURE is explicitly set — see autoload.php:
    // yiisoft/session throws on every request if this is on and the request
    // scheme isn't https, so this must stay opt-in per deployment.
    'yiisoft/session' => [
        'session' => [
            'options' => [
                'cookie_secure' => (int) $_ENV['SESSION_COOKIE_SECURE'],
            ],
        ],
    ],
    'yiisoft/widget' => [
        'defaultTheme' => 'bootstrap5',
    ],
    'yiisoft/assets' => [
        'assetManager' => [
            'customizedBundles' => [
                BootstrapAsset::class => [
                    'css' => [],
                ],
                BootstrapCdnAsset::class => [
                    'css' => [],
                ],
            ],
        ],
    ],
];
