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

// Bare layout - no company branding, no locale dropdown, no offcanvas.
// Bootstrap 5 is loaded via CDN directly rather than through Invoice's
// AssetManager/asset-bundle classes, keeping this section free of any
// dependency on src/Invoice.

/**
 * @var App\Infrastructure\Persistence\User\User|null $user
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var Yiisoft\Translator\TranslatorInterface $translator
 * @var Yiisoft\View\WebView $this
 * @var string $content
 * @var Csrf $csrf
 */

// $this->beginPage() must be the first statement after the docblock: Psalm
// only honours the @var Yiisoft\View\WebView $this override on the first
// $this usage if no assignment statement precedes it (an existing, narrow
// Psalm parser quirk also relied on implicitly by resources/views/layout/guest.php).
$this->beginPage();

$isGuest = $user === null;
?>
<!DOCTYPE html>
<?php
echo new TagHtml()->lang($currentRoute->getArgument('_language') ?? 'en');
echo Html::openTag('head');
echo Meta::documentEncoding('utf-8');
echo Meta::data('viewport', 'width=device-width, initial-scale=1');
echo new Title()->content('Shell');
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
echo Html::tag('span', 'Shell', ['class' => 'navbar-brand']);

if (!$isGuest) {
    echo Nav::widget()
        ->class('navbar-nav me-auto')
        ->items(
            NavLink::to($translator->translate('setting'), $urlGenerator->generate('shell/setting/index')),
            NavLink::to('Telegram', $urlGenerator->generate('shell/telegram/index')),
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

echo Html::script('', ['src' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js']);

$this->endBody();
echo Html::closeTag('body');
echo Html::closeTag('html');
$this->endPage(true);
