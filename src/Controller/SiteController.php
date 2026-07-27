<?php

declare(strict_types=1);

namespace App\Controller;

use App\Auth\MailerAddresses;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * The generic "flash" pages the Auth flows redirect to — one alert message
 * each. Add your own real landing page in place of index() as your app
 * grows past "no frills".
 */
final class SiteController
{
    public function __construct(
        private WebViewRenderer $webViewRenderer,
        private readonly TranslatorInterface $translator,
        private readonly MailerAddresses $mailerAddresses,
    ) {
        $this->webViewRenderer = $webViewRenderer->withController($this);
    }

    public function index(): Response
    {
        return $this->webViewRenderer->render('index');
    }

    /**
     * OAuth/payment provider dashboards typically require a Privacy Policy
     * URL when registering an app — this is placeholder content, replace it
     * with real legal text before going to production.
     */
    public function privacypolicy(): Response
    {
        return $this->webViewRenderer->render('privacypolicy', [
            'contactEmail' => $this->mailerAddresses->adminEmail,
        ]);
    }

    /**
     * Same as privacypolicy() — a Terms of Service URL is commonly a
     * mandatory field in provider app-registration forms.
     */
    public function termsofservice(): Response
    {
        return $this->webViewRenderer->render('termsofservice', [
            'contactEmail' => $this->mailerAddresses->adminEmail,
        ]);
    }

    public function oauth2autherror(#[RouteArgument('message')] string $message): Response
    {
        return $this->message($message);
    }

    public function oauth2callbackresultunauthorised(): Response
    {
        return $this->message($this->translator->translate('oauth2.callback.unauthorised'));
    }

    public function usercancelledoauth2(): Response
    {
        return $this->message($this->translator->translate('layout.page.user-cancelled-oauth2'));
    }

    public function signupfailed(): Response
    {
        return $this->message($this->translator->translate('signup.failed'));
    }

    public function signupsuccess(): Response
    {
        return $this->message($this->translator->translate('signup.success'), 'success');
    }

    public function forgotalert(): Response
    {
        return $this->message($this->translator->translate('password.reset.email'));
    }

    public function forgotusernotfound(): Response
    {
        return $this->message($this->translator->translate('loginalert.user.not.found'), 'warning');
    }

    public function forgotemailfailed(): Response
    {
        return $this->message($this->translator->translate('password.reset.failed'));
    }

    public function resetpasswordfailed(): Response
    {
        return $this->message($this->translator->translate('password.reset.failed'), 'warning');
    }

    public function resetpasswordsuccess(): Response
    {
        return $this->message($this->translator->translate('password.reset.success'), 'success');
    }

    public function onetimepassworderror(): Response
    {
        return $this->message($this->translator->translate('onetime.password.error'), 'warning');
    }

    public function accountdisabled(): Response
    {
        return $this->message($this->translator->translate('account.disabled'), 'warning');
    }

    private function message(string $message, string $variant = 'info'): Response
    {
        return $this->webViewRenderer->render('message', ['message' => $message, 'variant' => $variant]);
    }
}
