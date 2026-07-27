<?php

declare(strict_types=1);

use Yiisoft\Bootstrap5\Nav;
use Yiisoft\Bootstrap5\NavLink;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Html\Tag\Html as TagHtml;
use Yiisoft\Html\Tag\Meta;
use Yiisoft\Html\Tag\Title;
use Yiisoft\Yii\View\Renderer\Csrf;

// The app-wide default layout (config/common/params.php's
// yiisoft/yii-view-renderer.layout) — everything except Shell (which
// overrides to @views/shell/layout/main.php in Shell\BaseController) renders
// through this one: login/signup/password flows and the generic site pages.

/**
 * @var App\Infrastructure\Persistence\User\User|null $user
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var Yiisoft\Translator\TranslatorInterface $translator
 * @var Yiisoft\View\WebView $this
 * @var string $content
 * @var Csrf $csrf
 */

// $this->beginPage() must be the first statement after the docblock — see
// resources/views/shell/layout/main.php for why (a narrow Psalm parser quirk).
$this->beginPage();

$isGuest = $user === null;
?>
<!DOCTYPE html>
<?php
echo new TagHtml()->lang($currentRoute->getArgument('_language') ?? 'en');
echo Html::openTag('head');
echo Meta::documentEncoding('utf-8');
echo Meta::data('viewport', 'width=device-width, initial-scale=1');
echo new Title()->content('ddd-template');
echo Html::tag('link', '', [
    'rel' => 'stylesheet',
    'href' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
]);
$this->head();
echo Html::closeTag('head');
echo Html::openTag('body');
$this->beginBody();

echo Html::openTag('header');
echo Html::openTag('nav', ['class' => 'navbar navbar-expand-lg bg-body-tertiary border-bottom']);
echo Html::openTag('div', ['class' => 'container-fluid']);
echo Html::a('ddd-template', $urlGenerator->generate('site/index'), ['class' => 'navbar-brand']);

if ($isGuest) {
    echo Nav::widget()
        ->class('navbar-nav ms-auto')
        ->items(
            NavLink::to($translator->translate('login'), $urlGenerator->generate('auth/login')),
            NavLink::to($translator->translate('menu.signup'), $urlGenerator->generate('signup/signup')),
        );
} else {
    echo Nav::widget()
        ->class('navbar-nav me-auto')
        ->items(
            NavLink::to('Shell', $urlGenerator->generate('shell/setting/index')),
            NavLink::to($translator->translate('password.change'), $urlGenerator->generate('auth/change')),
        );
    echo new Form()
        ->post($urlGenerator->generate('auth/logout'))
        ->csrf($csrf)
        ->open()
        . Button::submit($translator->translate('logout'))->addClass('btn btn-outline-secondary btn-sm')
        . new Form()->close();
}

echo Html::closeTag('div');
echo Html::closeTag('nav');
echo Html::closeTag('header');

echo Html::openTag('main', ['class' => 'container-fluid py-4']);
echo $content;
echo Html::closeTag('main');

echo Html::openTag('footer', ['class' => 'border-top py-3 text-center text-muted small']);
echo Html::a('Privacy Policy', $urlGenerator->generate('site/privacypolicy'), ['class' => 'text-muted me-3']);
echo Html::a('Terms of Service', $urlGenerator->generate('site/termsofservice'), ['class' => 'text-muted']);
echo Html::closeTag('footer');

echo Html::script('', ['src' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js']);

$this->endBody();
echo Html::closeTag('body');
echo Html::closeTag('html');
$this->endPage(true);
