<?php

declare(strict_types=1);

use Yiisoft\{FormModel\Field as F};
use Yiisoft\Html\{Html as H, Tag\A, Tag\Img, Tag\Form, Tag\Span};
use Yiisoft\View\WebView;
use Yiisoft\Yii\AuthClient\Widget\AuthChoice;

/**
 * @var WebView                                     $this
 * @var App\Auth\Form\LoginForm                     $formModel
 * @var Yiisoft\Router\CurrentRoute                 $currentRoute
 * @var Yiisoft\Router\UrlGeneratorInterface        $urlGenerator
 * @var Yiisoft\Translator\TranslatorInterface      $translator
 * @var array                                       $class
 * @var array                                       $idpList
 * @var string                                      $csrf
 * @var string                                      $styleTagFadeOut
 * @var array<string, list<string>>                 $errors
 * @var string                                      $turnstileSiteKey
 * @var bool                                        $tfaEnabled
 * @var bool                                        $tfaWithDisabling
 */

$styleTagFadeOut;

if ($turnstileSiteKey !== '') {
    $this->registerJsFile(
        'https://challenges.cloudflare.com/turnstile/v0/api.js',
        WebView::POSITION_END,
        ['async' => true, 'defer' => true],
    );
}

echo H::openTag('div', ['class' => (string) $class[1]]);
 echo H::openTag('div', ['class' => (string) $class[2]]);
  echo H::openTag('div', ['class' => (string) $class[3]]);
   echo H::openTag('div', ['class' => (string) $class[4]]);
    echo H::openTag('div', ['class' => (string) $class[5]]);
     echo H::openTag('h1', ['class' => (string) $class[6]]);
      echo H::encode($translator->translate('login'));
     echo H::closeTag('h1');
    echo H::closeTag('div');
    echo H::openTag('div', ['class' => (string) $class[7]]);

    /**
     * Note: The links are authRouted.
     * because these are absolute links that go to Identity Providers e.g.
     * facebook
     * ->authRoute will be used for the callbacks
     */
    $authChoice = AuthChoice::widget();

    /**
     * Selection of Identity Providers e.g. Google, Facebook for OAuth2
     * @var string $provider
     * @var array $idpList[$provider]
     * @var string $provider
     * @var array $info
     * @var bool $info['noflag']
     */
    foreach ($idpList as $provider => $info) {
        $noContinueButton = $info['noflag'];
        if ($noContinueButton) {
            continue;
        }
        $button = $authChoice->authRoutedButtons(
            'auth/authclient',
            $idpList[$provider],
            $provider
        );
        // authRoutedButtons() returns '' when the provider has no
        // clientId configured (see AuthChoice::authRoutedButtons) — skip
        // the spacing too, or unconfigured providers leave dead
        // <br><br> gaps above the form.
        if ($button === '') {
            continue;
        }
        echo '<br><br>';
        echo $button;
    }

    echo H::closeTag('div');
    if ($tfaEnabled) {
      echo H::openTag('div', [
          'id' => 'tfa-badge', 'class' => (string) $class[8]]);
        $tfaEnabledLabel = 'two.factor.authentication.enabled';
        echo  new Span()
             ->addAttributes([
                 'class' => (string) $class[9],
                 'style' => 'white-space:normal;word-break:break-word;'
                . 'max-width:100%;display:inline-block;',
                 'data-toggle-bs' => 'tooltip',
                 'title' => $tfaWithDisabling
                ? $translator->translate($tfaEnabledLabel . '.with.disabling')
                : $translator->translate($tfaEnabledLabel . '.without.disabling'
             )])
             ->content($translator->translate($tfaEnabledLabel . '.aegis'))
             ->render();
        echo H::openTag('br');
        echo  new A()
         ->href('https://getaegis.app')
         ->addAttributes([
            'target' => '_blank',
            'data-toggle-bs' => 'tooltip',
            'title' => $translator->translate('download')
         ])
         ->content( new Img()
                    ->size(60, 60)
                    ->src('/img/aegis.png')
                    ->alt('Opensource Two Factor Authentication Software'))
         ->render();
      echo H::closeTag('div');
    }
    echo H::openTag('div', ['class' => (string) $class[10]]);
    echo  new Form()
    ->post($urlGenerator->generate('auth/login'))
    ->class('form-floating')
    ->csrf($csrf)
    ->id('loginForm')
    ->open();
    echo F::text($formModel, 'login')
    ->addInputAttributes(['autocomplete' => 'username'])
    ->inputClass((string) $class[11])
    ->label($translator->translate('layout.login'));
    echo F::password($formModel, 'password')
    ->addInputAttributes(['autocomplete' => 'current-password'])
    ->inputClass((string) $class[11])
    ->label($translator->translate('layout.password'));
    echo F::checkbox($formModel, 'rememberMe')
    ->containerClass((string) $class[12])
    ->inputClass((string) $class[13])
    ->label($translator->translate('layout.remember'))
    ->inputLabelClass((string) $class[14]);
    echo F::errorSummary($formModel)
    ->errors($errors)
    ->header($translator->translate('error.summary'));
    if ($turnstileSiteKey !== '') {
        echo H::tag('div', '', ['class' => 'cf-turnstile', 'data-sitekey' => $turnstileSiteKey]);
    }
    echo F::submitButton()
    ->buttonId('login-button')
    ->buttonClass((string) $class[15])
    ->name('login-button')
    ->content($translator->translate('layout.submit'));
    echo  new Form()->close();
    echo H::br();
    echo  new A()
    ->attribute('style', 'color:#999')
    ->addClass('text-decoration-none')
    ->addClass((string) $class[16])
    ->href($urlGenerator->generate('auth/forgotpassword'))
    ->content($translator->translate('forgot.your.password'))
    ->render();
    echo H::br();
    echo  new A()
    ->addClass('text-decoration-none')
    ->addClass((string) $class[16])
    ->href($urlGenerator->generate('signup/signup'))
    ->content($translator->translate('account.no'))
    ->render();
    echo H::closeTag('div'); // 5
   echo H::closeTag('div'); // 4
  echo H::closeTag('div'); // 3
 echo H::closeTag('div'); // 2
echo H::closeTag('div'); // 1
