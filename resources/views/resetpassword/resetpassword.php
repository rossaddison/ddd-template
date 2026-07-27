<?php

declare(strict_types=1);

use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html as H;
use Yiisoft\Html\Tag\Form;
use Yiisoft\View\WebView;

/**
 * Related logic: see App\Auth\Controller\ResetPasswordController function resetpassword
 *
 * @var App\Auth\Form\ResetPasswordForm $formModel
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Translator\TranslatorInterface $translator
 * @var WebView $this
 * @var string $csrf
 * @var string $token
 * @var string $turnstileSiteKey
 */
$this->setTitle($translator->translate('password.reset'));
if ($turnstileSiteKey !== '') {
    $this->registerJsFile(
        'https://challenges.cloudflare.com/turnstile/v0/api.js',
        WebView::POSITION_END,
        ['async' => true, 'defer' => true],
    );
}

$container  = ['class' => 'container py-5 h-100'];
$row        = ['class' => 'row d-flex justify-content-center align-items-center h-100'];
$col        = ['class' => 'col-12 col-md-8 col-lg-6 col-xl-5'];
$card       = ['class' => 'card border border-dark shadow-2-strong rounded-3'];
$cardHeader = ['class' => 'card-header bg-dark text-white'];
$title      = ['class' => 'fw-normal h3 text-center'];
$cardBody   = ['class' => 'card-body p-5 text-center'];

echo H::openTag('div', $container); //0
 echo H::openTag('div', $row); //1
  echo H::openTag('div', $col); //2
   echo H::openTag('div', $card); //3
    echo H::openTag('div', $cardHeader); //4
     echo H::openTag('h1', $title); //5
      echo H::encode($this->getTitle());
     echo H::closeTag('h1'); //5
    echo H::closeTag('div'); //4
    echo H::openTag('div', $cardBody); //4
     echo  new Form()
      ->post($urlGenerator->generate('auth/resetpassword', ['token' => $token]))
      ->csrf($csrf)
      ->id('resetPasswordForm')
      ->open();
     echo Field::password($formModel, 'newPassword')
      ->addInputAttributes(['autocomplete' => 'new-password'])
      ->label($translator->translate('layout.password.new'));
     echo Field::password($formModel, 'newPasswordVerify')
      ->addInputAttributes(['autocomplete' => 'verify-new-password'])
      ->label($translator->translate('layout.password-verify.new'));
     if ($turnstileSiteKey !== '') {
         echo H::tag('div', '', ['class' => 'cf-turnstile', 'data-sitekey' => $turnstileSiteKey]);
     }
     echo Field::submitButton()
      ->buttonId('resetpassword-button')
      ->name('resetpassword-button')
      ->content($translator->translate('layout.submit'));
     echo  new Form()->close();
    echo H::closeTag('div'); //4
   echo H::closeTag('div'); //3
  echo H::closeTag('div'); //2
 echo H::closeTag('div'); //1
echo H::closeTag('div'); //0
