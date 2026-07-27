<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Html\Tag\Input;
use Yiisoft\Html\Tag\Label;
use Yiisoft\Yii\View\Renderer\Csrf;

/**
 * @var string $alert
 * @var bool $isConfigured
 * @var bool $isEnabled
 * @var string $token
 * @var string $chatId
 * @var string $webhookSecret
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Csrf $csrf
 */
?>
<?= $alert ?>

<h1>Telegram bot connection</h1>

<p>
    Status:
    <?= $isConfigured ? Html::tag('span', 'Configured', ['class' => 'badge bg-success']) : Html::tag('span', 'Not configured', ['class' => 'badge bg-secondary']) ?>
    <?= $isEnabled ? Html::tag('span', 'Enabled', ['class' => 'badge bg-success ms-1']) : Html::tag('span', 'Disabled', ['class' => 'badge bg-secondary ms-1']) ?>
</p>

<?= new Form()->post($urlGenerator->generate('shell/telegram/save'))->csrf($csrf)->open() ?>
<div class="mb-3">
    <?= new Label()->forId('token')->content('Bot token') ?>
    <?= Input::text('token', $token)->id('token')->addClass('form-control') ?>
</div>
<div class="mb-3">
    <?= new Label()->forId('chat_id')->content('Chat ID') ?>
    <?= Input::text('chat_id', $chatId)->id('chat_id')->addClass('form-control') ?>
</div>
<div class="mb-3">
    <?= new Label()->forId('webhook_secret')->content('Webhook secret (optional)') ?>
    <?= Input::text('webhook_secret', $webhookSecret)->id('webhook_secret')->addClass('form-control') ?>
</div>
<?= Button::submit('Save')->addClass('btn btn-primary') ?>
<?= new Form()->close() ?>

<hr>

<?= new Form()->post($urlGenerator->generate('shell/telegram/test'))->csrf($csrf)->open() ?>
<?= Button::submit('Send test message')->addClass('btn btn-outline-secondary me-2') ?>
<?= new Form()->close() ?>

<?= new Form()->post($urlGenerator->generate('shell/telegram/setWebhook'))->csrf($csrf)->open() ?>
<?= Button::submit('Set webhook')->addClass('btn btn-outline-secondary me-2') ?>
<?= new Form()->close() ?>

<?= new Form()->post($urlGenerator->generate('shell/telegram/deleteWebhook'))->csrf($csrf)->open() ?>
<?= Button::submit('Delete webhook')->addClass('btn btn-outline-secondary me-2') ?>
<?= new Form()->close() ?>

<a href="<?= Html::encode($urlGenerator->generate('shell/telegram/webhookInfo')) ?>" class="btn btn-outline-secondary">Webhook info</a>
