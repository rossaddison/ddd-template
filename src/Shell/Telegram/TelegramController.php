<?php

declare(strict_types=1);

namespace App\Shell\Telegram;

use App\Application\Telegram\ConfigureBotConnection;
use App\Application\Telegram\DeleteBotWebhook;
use App\Application\Telegram\GetBotWebhookInfo;
use App\Application\Telegram\SetBotWebhook;
use App\Application\Telegram\TestBotConnection;
use App\Domain\Telegram\BotConnectionRepositoryInterface;
use App\Domain\Telegram\InvalidBotCredentialsException;
use App\Service\WebControllerService;
use App\Shell\BaseController;
use App\User\UserService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Http\Method;
use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Session\Flash\Flash;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Telegram bot connectivity administration only (credentials, test message,
 * webhook set/delete/status). No invoice-sending/payment features — those
 * stay in App\Invoice\Telegram\TelegramController for when the Invoice
 * module is later composed into this shell.
 */
final class TelegramController extends BaseController
{
    protected string $controllerName = 'shell/telegram';

    public function __construct(
        WebControllerService $webService,
        UserService $userService,
        TranslatorInterface $translator,
        WebViewRenderer $webViewRenderer,
        SessionInterface $session,
        Flash $flash,
        private readonly BotConnectionRepositoryInterface $connections,
        private readonly ConfigureBotConnection $configureBotConnection,
        private readonly TestBotConnection $testBotConnection,
        private readonly SetBotWebhook $setBotWebhook,
        private readonly DeleteBotWebhook $deleteBotWebhook,
        private readonly GetBotWebhookInfo $getBotWebhookInfo,
    ) {
        parent::__construct($webService, $userService, $translator, $webViewRenderer, $session, $flash);
    }

    public function index(): Response
    {
        $connection = $this->connections->get();
        return $this->render('index', [
            'alert' => $this->alert(),
            'isConfigured' => $connection->isConfigured(),
            'isEnabled' => $connection->isEnabled(),
            'token' => $connection->credentials()?->token() ?? '',
            'chatId' => $connection->credentials()?->chatId() ?? '',
            'webhookSecret' => $connection->credentials()?->webhookSecret() ?? '',
        ]);
    }

    public function save(Request $request): Response
    {
        if ($request->getMethod() === Method::POST) {
            $body = $request->getParsedBody();
            if (is_array($body)) {
                /** @var mixed $token */
                $token = $body['token'] ?? '';
                /** @var mixed $chatId */
                $chatId = $body['chat_id'] ?? '';
                /** @var mixed $webhookSecret */
                $webhookSecret = $body['webhook_secret'] ?? '';
                try {
                    ($this->configureBotConnection)(
                        is_string($token) ? $token : '',
                        is_string($chatId) && $chatId !== '' ? $chatId : null,
                        is_string($webhookSecret) && $webhookSecret !== '' ? $webhookSecret : null,
                    );
                    $this->flashMessage('success', $this->translator->translate('record.successfully.created'));
                } catch (InvalidBotCredentialsException $e) {
                    $this->flashMessage('danger', $e->getMessage());
                }
            }
        }
        return $this->webService->getRedirectResponse('shell/telegram/index');
    }

    public function test(): Response
    {
        $sent = ($this->testBotConnection)();
        $this->flashMessage(
            $sent ? 'success' : 'danger',
            $this->translator->translate($sent
                ? 'record.successfully.created'
                : 'record.successfully.created.not'),
        );
        return $this->webService->getRedirectResponse('shell/telegram/index');
    }

    public function setWebhook(UrlGenerator $urlGenerator): Response
    {
        $webhookUrl = $urlGenerator->generateAbsolute('shell/telegram/webhook');
        $ok = ($this->setBotWebhook)($webhookUrl);
        $this->flashMessage(
            $ok ? 'success' : 'danger',
            $this->translator->translate($ok
                ? 'record.successfully.created'
                : 'record.successfully.created.not'),
        );
        return $this->webService->getRedirectResponse('shell/telegram/index');
    }

    public function deleteWebhook(): Response
    {
        ($this->deleteBotWebhook)();
        return $this->webService->getRedirectResponse('shell/telegram/index');
    }

    public function webhookInfo(): Response
    {
        return $this->render('webhookInfo', [
            'alert' => $this->alert(),
            'info' => ($this->getBotWebhookInfo)(),
        ]);
    }

    /**
     * Bare acknowledgement receiver — no update processing. Its only job is
     * to give setWebhook() a real endpoint to register so Telegram gets 200
     * instead of 404. Actual update handling (payments, messages) belongs to
     * whatever module is later composed into the shell.
     */
    public function webhook(Request $request, ResponseFactoryInterface $responseFactory): Response
    {
        return $responseFactory->createResponse(200);
    }
}
