<?php

declare(strict_types=1);

use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html as H;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Html\Tag\Style;
use Yiisoft\Html\Tag\Table;
use Yiisoft\Html\Tag\Tr;
use Yiisoft\Html\Tag\Thead;
use Yiisoft\Html\Tag\Td;

/**
 * @var array $codes
 * @var string $csrf
 * @var string|null $error
 * @var App\Auth\Form\TwoFactorAuthenticationVerifyLoginForm $formModel
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\CurrentRoute             $currentRoute
 * @var Yiisoft\Router\UrlGeneratorInterface    $urlGenerator
 * @var Yiisoft\Translator\TranslatorInterface  $translator
 */

$container  = ['class' => 'container py-5 h-100'];
$row        = ['class' => 'row d-flex justify-content-center align-items-center h-100'];
$col        = ['class' => 'col-12 col-md-8 col-lg-6 col-xl-5'];
$card       = ['class' => 'card border border-dark shadow-2-strong rounded-3'];
$cardHeader = ['class' => 'card-header bg-dark text-white'];
$title      = ['class' => 'fw-normal h3 text-center'];
$cardBodyH6 = ['class' => 'card-body p-2 text-center'];
$cardBodyP1 = ['class' => 'card-body p-1 text-center'];

echo H::openTag('div', $container); //0
 echo H::openTag('div', $row); //1
  echo H::openTag('div', $col); //2
   echo H::openTag('div', $card); //3
    echo H::openTag('div', $cardHeader); //4
     echo H::openTag('h5', $title); //5
      echo $translator->translate('two.factor.authentication');
     echo H::closeTag('h5'); //5
    echo H::closeTag('div'); //4
    echo H::openTag('div', $cardBodyH6); //4
     echo H::openTag('h6'); //5
      echo $translator->translate('two.factor.authentication.new.six.digit.code');
     echo H::closeTag('h6'); //5
    echo H::closeTag('div'); //4
    echo H::openTag('div', $cardBodyH6); //4
     echo  new Style()->content(
         '.recovery-table { border-collapse: collapse; width: 100%;'
             . ' background: #f9f9fb; font-family: \'Segoe UI\', Arial, sans-serif;'
             . ' margin-top: 1em; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }'
         . '.recovery-table th, .recovery-table td { border: 1px solid #e3e3e3;'
             . ' padding: 12px 18px; text-align: left; }'
         . '.recovery-table th { background: #4F8EF7; color: #fff;'
             . ' letter-spacing: 1px; font-size: 1.05em; }'
         . '.recovery-table tr:nth-child(even) { background: #f0f4fa; }'
         . '.recovery-table tr:hover td { background: #e6f2ff; }'
     )->render();
     $headerRow =  new Thead()
      ->rows(
           new Tr()->dataStrings(['#', $translator->translate('oauth2.backup.recovery.codes')]),
      );
     $rows = [];
     /**
      * @var string $index
      * @var string $code
      */
     foreach ($codes as $index => $code) {
         $rows[] =  new Tr()->cells(
              new Td()->content((string) ((int) $index + 1)),
              new Td()->content(H::encode($code)),
         );
     }
     if (!empty($codes)) {
         echo  new Table()
          ->header($headerRow)
          ->rows(...$rows)
          ->addAttributes(['class' => 'recovery-table'])
          ->render();
     }
     $regenerateCodesUrl = $urlGenerator->generate('auth/regenerateCodes');
     echo  new A()
      ->addClass('btn btn-success')
      ->content(' ' . $translator->translate('oauth2.backup.recovery.codes.regenerate'))
      ->href($regenerateCodesUrl)
      ->id('btn-regenerate-codes')
      ->render();
    echo H::closeTag('div'); //4
    echo H::openTag('div', $cardBodyH6); //4
     echo  new Form()
      ->post($urlGenerator->generate('auth/verifyLogin'))
      ->class('form-floating')
      ->csrf($csrf)
      ->id('twoFactorAuthenticationVerfiyForm')
      ->open();
     echo Field::text($formModel, 'code')
      ->addInputAttributes([
          'autocomplete' => 'current-code',
          'id' => 'code',
          'name' => 'code',
          'minlength' => 6,
          // otp = 6 digits, backup recovery code = 8 digits
          'maxlength' => 8,
          'type' => 'tel',
      ])
      ->error($error ?? '')
      ->required(true)
      ->inputClass('form-control form-control-lg')
      ->label($translator->translate('layout.password.otp.6.8'))
      ->autofocus();
     echo Field::submitButton()
      ->buttonId('code-button')
      ->buttonClass('btn btn-primary')
      ->name('code-button')
      ->content($translator->translate('layout.submit'));
     echo  new Form()->close();
    echo H::closeTag('div'); //4
    echo H::openTag('div', $cardBodyP1); //4
     for ($i = 1; $i <= 9; $i++) {
         echo H::openTag('button', [
             'type' => 'button',
             'class' => 'btn btn-info btn-sm btn-digit',
             'data-digit' => $i,
         ]);
          echo $i;
         echo H::closeTag('button');
         echo ' ';
     }
     echo H::openTag('button', [
         'type' => 'button',
         'class' => 'btn btn-info btn-sm btn-digit',
         'data-digit' => '0',
     ]);
      echo 0;
     echo H::closeTag('button');
     echo ' ';
     echo H::openTag('button', [
         'type' => 'button',
         'class' => 'btn btn-info btn-sm btn-clear-otp',
     ]);
      echo 'Clear';
     echo H::closeTag('button');
    echo H::closeTag('div'); //4
   echo H::closeTag('div'); //3
  echo H::closeTag('div'); //2
 echo H::closeTag('div'); //1
echo H::closeTag('div'); //0
