<?php

declare(strict_types=1);

use App\Middleware\RoutePermission;
use App\Shell\Permissions;
use App\Shell\Setting\SettingController;
use App\Shell\Telegram\TelegramController;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Http\Method;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    // Inline Authentication + RoutePermission::check(), same precedent as
    // routes-backend.php — not RoutePermission::invoiceGroup(), since this
    // section deliberately has no dependency on the invoice route group.
    Group::create('/shell')
        ->middleware(Authentication::class)
        ->middleware(RoutePermission::check(Permissions::ACCESS_SHELL))
        ->routes(
            // Landing route — no bare-404 root, lands on Settings.
            Route::get('')
                ->middleware(RoutePermission::check(Permissions::MANAGE_SETTINGS))
                ->action([SettingController::class, 'index'])
                ->name('shell/index'),

            Route::get('/setting')
                ->middleware(RoutePermission::check(Permissions::MANAGE_SETTINGS))
                ->action([SettingController::class, 'index'])
                ->name('shell/setting/index'),

            Route::post('/setting/save')
                ->middleware(RoutePermission::check(Permissions::MANAGE_SETTINGS))
                ->action([SettingController::class, 'save'])
                ->name('shell/setting/save'),

            Route::get('/telegram')
                ->middleware(RoutePermission::check(Permissions::MANAGE_TELEGRAM))
                ->action([TelegramController::class, 'index'])
                ->name('shell/telegram/index'),

            Route::post('/telegram/save')
                ->middleware(RoutePermission::check(Permissions::MANAGE_TELEGRAM))
                ->action([TelegramController::class, 'save'])
                ->name('shell/telegram/save'),

            Route::post('/telegram/test')
                ->middleware(RoutePermission::check(Permissions::MANAGE_TELEGRAM))
                ->action([TelegramController::class, 'test'])
                ->name('shell/telegram/test'),

            Route::post('/telegram/setWebhook')
                ->middleware(RoutePermission::check(Permissions::MANAGE_TELEGRAM))
                ->action([TelegramController::class, 'setWebhook'])
                ->name('shell/telegram/setWebhook'),

            Route::post('/telegram/deleteWebhook')
                ->middleware(RoutePermission::check(Permissions::MANAGE_TELEGRAM))
                ->action([TelegramController::class, 'deleteWebhook'])
                ->name('shell/telegram/deleteWebhook'),

            Route::get('/telegram/webhookInfo')
                ->middleware(RoutePermission::check(Permissions::MANAGE_TELEGRAM))
                ->action([TelegramController::class, 'webhookInfo'])
                ->name('shell/telegram/webhookInfo'),
        ),

    // Not under the /shell group above: Telegram's servers must be able to
    // POST here with no app session/RBAC — secured by the webhook secret
    // token, not Authentication middleware. See
    // App\Shell\Telegram\TelegramController::webhook().
    Route::methods([Method::GET, Method::POST], '/shell/telegram/webhook')
        ->action([TelegramController::class, 'webhook'])
        ->name('shell/telegram/webhook'),
];
