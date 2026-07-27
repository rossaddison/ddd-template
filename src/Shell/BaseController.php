<?php

declare(strict_types=1);

namespace App\Shell;

use App\Service\WebControllerService;
use App\Shell\Traits\FlashMessage;
use App\Shell\ViewInjection\ShellViewInjection;
use App\User\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Session\Flash\Flash;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\CsrfViewInjection;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Mirrors the shape of App\Invoice\BaseController (shared constructor-
 * injected plumbing, permission-driven view setup, render()/alert()
 * helpers) with none of its Invoice-specific logic (custom fields, delivery
 * locations, VIEW_INV/EDIT_INV layout branching) — this section reuses
 * nothing from src/Invoice.
 *
 * Overrides WebViewRenderer's injections: the app-wide default
 * (config/common/params.php) includes CommonViewInjection/LayoutViewInjection,
 * which query Invoice's CompanyRepository/CompanyPrivateRepository/
 * SettingRepository on every render regardless of layout. Without this
 * override, Shell pages would silently pull in Invoice-module queries.
 *
 * Route-level RBAC (RoutePermission::check(Permissions::ACCESS_SHELL) in
 * routes-shell.php) is the actual enforcement point — Shell has exactly one
 * layout, so there is no permission-tiered layout selection to do here.
 */
abstract class BaseController
{
    use FlashMessage;

    protected string $controllerName = 'shell';

    public function __construct(
        protected WebControllerService $webService,
        protected UserService $userService,
        protected TranslatorInterface $translator,
        protected WebViewRenderer $webViewRenderer,
        protected SessionInterface $session,
        protected Flash $flash,
    ) {
        $this->initializeViewRenderer();
    }

    protected function initializeViewRenderer(): void
    {
        $this->webViewRenderer = $this->webViewRenderer
            ->withControllerName($this->controllerName)
            ->withLayout('@views/shell/layout/main.php')
            ->withInjections(CsrfViewInjection::class, ShellViewInjection::class);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function render(string $view, array $parameters = []): Response
    {
        return $this->webViewRenderer->render($view, $parameters);
    }

    protected function alert(): string
    {
        return $this->webViewRenderer->renderPartialAsString(
            '//shell/layout/alert',
            [
                'flash' => $this->flash,
            ],
        );
    }
}
