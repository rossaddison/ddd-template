<?php

declare(strict_types=1);

use App\Auth\Controller\{
    AuthController, ChangePasswordController, ForgotPasswordController,
    ResetPasswordController, SignupController};
use App\Controller\SiteController;
use App\Middleware\RateLimiter;
use Yiisoft\{
    Http\Method, Router\Route, Yii\AuthClient\AuthAction,
    Yii\RateLimiter\LimitRequestsMiddleware as LRM};

$mG = Method::GET;
$mP = Method::POST;

/**
 * Note: If middleware is used, it must always be inserted before the action
 */

return [
    // Site
    Route::get('/')
        ->action([SiteController::class, 'index'])
        ->name('site/index'),
    Route::methods([$mG, $mP], '/privacypolicy')
        ->action([SiteController::class, 'privacypolicy'])
        ->name('site/privacypolicy'),
    Route::methods([$mG, $mP], '/termsofservice')
        ->action([SiteController::class, 'termsofservice'])
        ->name('site/termsofservice'),
    Route::methods([$mG, $mP], '/oauth2autherror/{message}')
        ->action([SiteController::class, 'oauth2autherror'])
        ->name('site/oauth2autherror'),
    Route::methods([$mG, $mP], '/oauth2callbackresultunauthorised')
        ->action([SiteController::class, 'oauth2callbackresultunauthorised'])
        ->name('site/oauth2callbackresultunauthorised'),
    Route::methods([$mG, $mP], '/usercancelledoauth2')
        ->action([SiteController::class, 'usercancelledoauth2'])
        ->name('site/usercancelledoauth2'),
    Route::methods([$mG, $mP], '/signupfailed')
        ->action([SiteController::class, 'signupfailed'])
        ->name('site/signupfailed'),
    Route::methods([$mG, $mP], '/signupsuccess')
        ->action([SiteController::class, 'signupsuccess'])
        ->name('site/signupsuccess'),
    Route::methods([$mG, $mP], '/forgotalert')
        ->action([SiteController::class, 'forgotalert'])
        ->name('site/forgotalert'),
    Route::methods([$mG, $mP], '/forgotusernotfound')
        ->action([SiteController::class, 'forgotusernotfound'])
        ->name('site/forgotusernotfound'),
    Route::methods([$mG, $mP], '/forgotemailfailed')
        ->action([SiteController::class, 'forgotemailfailed'])
        ->name('site/forgotemailfailed'),
    Route::methods([$mG, $mP], '/resetpasswordfailed')
        ->action([SiteController::class, 'resetpasswordfailed'])
        ->name('site/resetpasswordfailed'),
    Route::methods([$mG, $mP], '/resetpasswordsuccess')
        ->action([SiteController::class, 'resetpasswordsuccess'])
        ->name('site/resetpasswordsuccess'),
    Route::methods([$mG, $mP], '/onetimepassworderror')
        ->action([SiteController::class, 'onetimepassworderror'])
        ->name('site/onetimepassworderror'),
    Route::methods([$mG, $mP], '/accountdisabled')
        ->action([SiteController::class, 'accountdisabled'])
        ->name('site/accountdisabled'),

    // Auth
    Route::methods([$mG, $mP], '/login')
        // Outer: 30 total POSTs per 60 s on /login, regardless of IP
        ->middleware(RateLimiter::global(30))
        // Inner: 5 per 60 s per real IP via CF-Connecting-IP
        ->middleware(RateLimiter::perIp(5, 'login_route'))
        ->action([AuthController::class, 'login'])
        ->name('auth/login'),
    Route::get('/authclient')
        ->action([AuthController::class, 'authclient'])
        ->name('auth/authclient'),
    Route::methods([$mG, $mP], '/callback')
        ->middleware(LRM::class)
        ->middleware(AuthAction::class)
        ->action([AuthController::class, 'callback'])
        ->name('auth/callback'),
    Route::methods([$mG, $mP], '/callbackFacebook')
        ->middleware(LRM::class)
        ->action([AuthController::class, 'callbackFacebook'])
        ->name('auth/callbackFacebook'),
    Route::methods([$mG, $mP], '/callbackGithub')
        ->middleware(LRM::class)
        ->action([AuthController::class, 'callbackGithub'])
        ->name('auth/callbackGithub'),
    Route::methods([$mG, $mP], '/callbackGoogle')
        ->middleware(LRM::class)
        ->action([AuthController::class, 'callbackGoogle'])
        ->name('auth/callbackGoogle'),
    Route::methods([$mG, $mP], '/callbackLinkedIn')
        ->middleware(LRM::class)
        ->action([AuthController::class, 'callbackLinkedIn'])
        ->name('auth/callbackLinkedIn'),
    Route::methods([$mG, $mP], '/callbackMicrosoftOnline')
        ->middleware(LRM::class)
        ->action([AuthController::class, 'callbackMicrosoftOnline'])
        ->name('auth/callbackMicrosoftOnline'),
    Route::methods([$mG, $mP], '/callbackVKontakte')
        ->middleware(LRM::class)
        ->action([AuthController::class, 'callbackVKontakte'])
        ->name('auth/callbackVKontakte'),
    Route::methods([$mG, $mP], '/callbackX')
        ->middleware(LRM::class)
        ->action([AuthController::class, 'callbackX'])
        ->name('auth/callbackX'),
    Route::methods([$mG, $mP], '/callbackYandex')
        ->middleware(LRM::class)
        ->action([AuthController::class, 'callbackYandex'])
        ->name('auth/callbackYandex'),
    Route::post('/logout')
        ->action([AuthController::class, 'logout'])
        ->name('auth/logout'),
    Route::methods([$mG, $mP], '/showSetup')
        ->middleware(LRM::class)
        ->action([AuthController::class, 'showSetup'])
        ->name('auth/showSetup'),
    Route::methods([$mG, $mP], '/ajaxShowSetup')
        ->middleware(LRM::class)
        ->action([AuthController::class, 'ajaxShowSetup'])
        ->name('auth/ajaxShowSetup'),
    Route::methods([$mG, $mP], '/verifySetup')
        ->action([AuthController::class, 'verifySetup'])
        ->name('auth/verifySetup'),
    Route::methods([$mG, $mP], '/verifyLogin')
        ->action([AuthController::class, 'verifyLogin'])
        ->name('auth/verifyLogin'),
    Route::methods([$mG, $mP], '/regenerateCodes')
        ->action([AuthController::class, 'regenerateCodes'])
        ->name('auth/regenerateCodes'),
    Route::methods([$mG, $mP], '/forgotpassword')
        // Global path counter — 5 POSTs per 60 s; triggers email so kept tight
        ->middleware(RateLimiter::global(5))
        // Per real-IP via CF-Connecting-IP; CAS fail → 429
        ->middleware(RateLimiter::perIp(2, 'forgot_route'))
        ->action([ForgotPasswordController::class, 'forgot'])
        ->name('auth/forgotpassword'),
    Route::methods([$mG, $mP],
            '/resetpassword/resetpassword/{token}')
        // Global path counter — 10 POSTs per 60 s; token gate makes this low-traffic
        ->middleware(RateLimiter::global(10))
        // Per real-IP via CF-Connecting-IP; CAS fail → 429
        ->middleware(RateLimiter::perIp(3, 'reset_route'))
        ->action([ResetPasswordController::class, 'resetpassword'])
        ->name('auth/resetpassword'),
    Route::methods([$mG, $mP], '/change')
        // Global path counter — 10 POSTs per 60 s regardless of IP
        ->middleware(RateLimiter::global(10))
        // Per real-IP via CF-Connecting-IP; CAS fail → 429
        ->middleware(RateLimiter::perIp(3, 'change_route'))
        ->action([ChangePasswordController::class, 'change'])
        ->name('auth/change'),

    // Signup
    Route::methods([$mG, $mP], '/signup')
        // Global path counter — blocks botnet waves regardless of IP
        ->middleware(RateLimiter::global(50, 10))
        // Per real-IP via CF-Connecting-IP; CAS fail → 429
        ->middleware(RateLimiter::perIp(5, 'signup'))
        ->action([SignupController::class, 'signup'])
        ->name('signup/signup'),
];
