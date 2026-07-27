<?php

declare(strict_types=1);

use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html as H;
use Yiisoft\Html\Tag\Form;
use Yiisoft\View\WebView;

/**
 * Related logic: see App\Auth\Controller\ChangePasswordController function change
 *
 * @var App\Auth\Form\ChangePasswordForm $formModel
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Translator\TranslatorInterface $translator
 * @var WebView $this
 *
 * @var string|null $login
 * @var string $csrf
 * @var array<string, list<string>> $errors
 * @var string $turnstileSiteKey
 */
$this->setTitle($translator->translate('password.change'));
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
      // note: the change function actually appears in the ChangePasswordController
      ->post($urlGenerator->generate('auth/change'))
      ->csrf($csrf)
      ->id('changePasswordForm')
      ->open();
     echo Field::errorSummary($formModel)
      ->errors($errors)
      ->header($translator->translate('error.summary'));
     echo Field::text($formModel, 'login')
      ->label($translator->translate('layout.login'))
      ->addInputAttributes([
          'autocomplete' => 'username',
          'value' => $login ?? '',
          'readonly' => 'readonly',
      ]);
     echo Field::password($formModel, 'password')
      ->addInputAttributes(['autocomplete' => 'current-password'])
      ->label($translator->translate('layout.password'));
     echo Field::password($formModel, 'newPassword')
      ->addInputAttributes(['autocomplete' => 'new-password'])
      ->label($translator->translate('layout.password.new'));
     echo Field::password($formModel, 'newPasswordVerify')
      ->addInputAttributes(['autocomplete' => 'verify-password'])
      ->label($translator->translate('layout.password-verify.new'));
     if ($turnstileSiteKey !== '') {
         echo H::tag('div', '', ['class' => 'cf-turnstile', 'data-sitekey' => $turnstileSiteKey]);
     }
     echo Field::submitButton()
      ->buttonId('change-button')
      ->name('change-button')
      ->addButtonAttributes(['class' => 'btn btn-success bi bi-floppy w-100'])
      ->content(' ' . $translator->translate('layout.submit'));
     echo  new Form()->close();
    echo H::closeTag('div'); //4
   echo H::closeTag('div'); //3
  echo H::closeTag('div'); //2
 echo H::closeTag('div'); //1
echo H::closeTag('div'); //0
