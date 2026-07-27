<?php

declare(strict_types=1);

use App\Auth\Form\SignupForm;
use Yiisoft\{FormModel\Field as F, Html\Html as H, Html\Tag\Form,
    Router\UrlGeneratorInterface, Translator\TranslatorInterface,
    View\WebView, Yii\AuthClient\Widget\AuthChoice};

/**
 * @var Psr\Http\Message\ServerRequestInterface $request
 * @var SignupForm                              $formModel
 * @var Yiisoft\Router\CurrentRoute             $currentRoute
 * @var WebView                                 $this
 * @var TranslatorInterface                     $translator
 * @var UrlGeneratorInterface                   $urlGenerator
 * @var string                                  $csrf
 * @var array                                   $class
 * @var array                                   $idpList
 * @var array<string, list<string>>             $errors
 * @var string                                  $turnstileSiteKey
 */
$this->setTitle($translator->translate('menu.signup'));
if ($turnstileSiteKey !== '') {
    $this->registerJsFile(
        'https://challenges.cloudflare.com/turnstile/v0/api.js',
        \Yiisoft\View\WebView::POSITION_END,
        ['async' => true, 'defer' => true],
    );
}
echo H::openTag('div', ['class' => (string) $class[1]]);
 echo H::openTag('div', ['class' => (string) $class[2]]);
  echo H::openTag('div', ['class' => (string) $class[3]]);
   echo H::openTag('div', ['class' => (string) $class[4]]);
    echo H::openTag('div', ['class' => (string) $class[5]]);
     echo H::openTag('h1', ['class' => (string) $class[6]]);
      echo H::encode($this->getTitle());
     echo H::closeTag('h1');
    echo H::closeTag('div');
    echo H::openTag('div', ['class' => (string) $class[7]]);
    $authChoice = AuthChoice::widget();
    /**
     * @var string $provider
     * @var array $idpList[$provider]
     * @var array $info
     * @var bool $info['noflag']
     */
    foreach ($idpList as $provider => $info) {
        $noContinueButton = $info['noflag'];
        if (!$noContinueButton) {
            echo '<br><br>';
            echo $authChoice->absoluteButtons(
                $request,
                $idpList[$provider],
                $provider
            );
        }
    }
    echo H::closeTag('div');
    echo H::openTag('div', ['class' => (string) $class[10]]);
    echo  new Form()
    ->post($urlGenerator->generate('signup/signup'))
    ->csrf($csrf)
    ->id('signupForm')
    ->open();
    echo F::text($formModel, 'login')
    ->label($translator->translate('layout.login'))
    ->autofocus();
    echo F::email($formModel, 'email')
    ->label($translator->translate('email'))
    ->autofocus();
    echo F::password($formModel, 'password')
    ->addInputAttributes(['autocomplete' => 'current-password'])
    ->label($translator->translate('layout.password'));
    echo F::password($formModel, 'passwordVerify')
    ->addInputAttributes(['autocomplete' => 'current-password'])
    ->label($translator->translate('layout.password-verify.new'));
    echo F::errorSummary($formModel)
    ->errors($errors)
    ->header($translator->translate('error.summary'));
    if ($turnstileSiteKey !== '') {
        echo H::tag('div', '', ['class' => 'cf-turnstile', 'data-sitekey' => $turnstileSiteKey]);
    }
    echo F::submitButton()
    ->buttonId('register-button')
    ->buttonClass((string) $class[15])
    ->name('register-button')
    ->content($translator->translate('layout.submit'));
    echo  new Form()->close();
    echo H::closeTag('div'); // 5
   echo H::closeTag('div'); // 4
  echo H::closeTag('div'); // 3
 echo H::closeTag('div'); // 2
echo H::closeTag('div'); // 1
