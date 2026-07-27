<?php

declare(strict_types=1);

use App\Auth\Form\RequestPasswordResetTokenForm;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html as H;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView                         $this
 * @var TranslatorInterface             $translator
 * @var UrlGeneratorInterface           $urlGenerator
 * @var string                          $csrf
 * @var RequestPasswordResetTokenForm   $formModel
 * @var string                          $turnstileSiteKey
 */
$this->setTitle($translator->translate('password.reset.request.token'));
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
      ->post($urlGenerator->generate('auth/forgotpassword'))
      ->csrf($csrf)
      ->id('requestPasswordResetTokenForm')
      ->open();
     echo Field::email($formModel, 'email')
      ->label($translator->translate('email'))
      ->autofocus();
     if ($turnstileSiteKey !== '') {
         echo H::tag('div', '', ['class' => 'cf-turnstile', 'data-sitekey' => $turnstileSiteKey]);
     }
     echo Field::submitButton()
      ->buttonId('password-reset-token-button')
      ->name('password-reset-token-button')
      ->content($translator->translate('layout.submit'));
     echo  new Form()->close();
    echo H::closeTag('div'); //4
   echo H::closeTag('div'); //3
  echo H::closeTag('div'); //2
 echo H::closeTag('div'); //1
echo H::closeTag('div'); //0
