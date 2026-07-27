<?php

declare(strict_types=1);

use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Html\Tag\Input;
use Yiisoft\Html\Tag\Label;
use Yiisoft\Yii\View\Renderer\Csrf;

/**
 * @var string $alert
 * @var array<string, string> $values
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Csrf $csrf
 */
?>
<?= $alert ?>

<h1>Settings</h1>

<?= new Form()->post($urlGenerator->generate('shell/setting/save'))->csrf($csrf)->open() ?>

<h2>General</h2>
<div class="mb-3">
    <?= new Label()->forId('shell_app_name')->content('App name') ?>
    <?= Input::text('shell_app_name', $values['shell_app_name'])->id('shell_app_name')->addClass('form-control') ?>
</div>
<div class="mb-3">
    <?= new Label()->forId('shell_welcome_message')->content('Welcome message') ?>
    <?= Input::text('shell_welcome_message', $values['shell_welcome_message'])->id('shell_welcome_message')->addClass('form-control') ?>
</div>

<h2>Appearance</h2>
<div class="mb-3">
    <?= new Label()->forId('shell_theme_color')->content('Theme color') ?>
    <?= Input::text('shell_theme_color', $values['shell_theme_color'])->id('shell_theme_color')->addClass('form-control') ?>
</div>

<?= Button::submit('Save')->addClass('btn btn-primary') ?>

<?= new Form()->close() ?>
