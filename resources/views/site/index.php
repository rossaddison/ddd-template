<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\User\User;
use Yiisoft\Html\Html as H;
use Yiisoft\Html\Tag\A;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var User|null $user
 */

$card       = ['class' => 'card shadow p-5 my-5 mx-auto bg-white rounded'];
$body       = ['class' => 'card-body text-center'];
$title      = ['class' => 'card-title display-6 fw-bold'];
$actions    = ['class' => 'mt-4 d-flex gap-2 justify-content-center'];

echo H::openTag('div', $card); //0
 echo H::openTag('div', $body); //1
  echo H::openTag('h1', $title); //2
   echo H::encode('ddd-template');
  echo H::closeTag('h1'); //2
  echo H::openTag('p'); //2
   echo 'A lean Yii3 starter with auth, 2FA, RBAC, and a worked'
       . ' Domain/Application/Infrastructure/Shell example to build on.';
  echo H::closeTag('p'); //2
  echo H::openTag('div', $actions); //2
   if ($user === null) {
       echo  new A()
        ->addClass('btn btn-primary')
        ->content($translator->translate('login'))
        ->href($urlGenerator->generate('auth/login'))
        ->render();
       echo  new A()
        ->addClass('btn btn-outline-primary')
        ->content($translator->translate('menu.signup'))
        ->href($urlGenerator->generate('signup/signup'))
        ->render();
   } else {
       echo  new A()
        ->addClass('btn btn-primary')
        ->content('Shell')
        ->href($urlGenerator->generate('shell/setting/index'))
        ->render();
       echo  new A()
        ->addClass('btn btn-outline-secondary')
        ->content($translator->translate('logout'))
        ->href($urlGenerator->generate('auth/logout'))
        ->render();
   }
  echo H::closeTag('div'); //2
 echo H::closeTag('div'); //1
echo H::closeTag('div'); //0
