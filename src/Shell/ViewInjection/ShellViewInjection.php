<?php

declare(strict_types=1);

namespace App\Shell\ViewInjection;

use App\User\UserService;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;

/**
 * Provides the small set of framework-level variables the bare Shell layout
 * needs (nav links, logout form). Kept separate from CsrfViewInjection so
 * Shell\BaseController's withInjections() call stays a plain, explicit list
 * rather than reaching back into Invoice's CommonViewInjection/LayoutViewInjection.
 */
final readonly class ShellViewInjection implements CommonParametersInjectionInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private CurrentRoute $currentRoute,
        private TranslatorInterface $translator,
        private UserService $userService,
    ) {
    }

    #[\Override]
    public function getCommonParameters(): array
    {
        return [
            'urlGenerator' => $this->urlGenerator,
            'currentRoute' => $this->currentRoute,
            'translator' => $this->translator,
            'user' => $this->userService->getUser(),
        ];
    }
}
