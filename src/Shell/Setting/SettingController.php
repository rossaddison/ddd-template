<?php

declare(strict_types=1);

namespace App\Shell\Setting;

use App\Application\Setting\GetSetting;
use App\Application\Setting\UpdateSetting;
use App\Domain\Setting\SettingKey;
use App\Service\WebControllerService;
use App\Shell\BaseController;
use App\User\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Http\Method;
use Yiisoft\Session\Flash\Flash;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Two deliberately example-only tabs (General, Appearance) — nowhere near
 * the invoice app's 22-tab/~72-key Settings, since this section is a
 * "no frills" reference, not a real business settings area.
 */
final class SettingController extends BaseController
{
    protected string $controllerName = 'shell/setting';

    /** @var array<string, string> Setting key => example tab label */
    private const array KEYS = [
        'shell_app_name' => 'General',
        'shell_welcome_message' => 'General',
        'shell_theme_color' => 'Appearance',
    ];

    public function __construct(
        WebControllerService $webService,
        UserService $userService,
        TranslatorInterface $translator,
        WebViewRenderer $webViewRenderer,
        SessionInterface $session,
        Flash $flash,
        private readonly GetSetting $getSetting,
        private readonly UpdateSetting $updateSetting,
    ) {
        parent::__construct($webService, $userService, $translator, $webViewRenderer, $session, $flash);
    }

    public function index(): Response
    {
        $values = [];
        foreach (array_keys(self::KEYS) as $key) {
            $values[$key] = ($this->getSetting)(new SettingKey($key))?->value() ?? '';
        }
        return $this->render('index', [
            'alert' => $this->alert(),
            'values' => $values,
        ]);
    }

    public function save(Request $request): Response
    {
        if ($request->getMethod() === Method::POST) {
            $body = $request->getParsedBody();
            if (is_array($body)) {
                foreach (array_keys(self::KEYS) as $key) {
                    /** @var mixed $value */
                    $value = $body[$key] ?? null;
                    if (is_string($value)) {
                        ($this->updateSetting)(new SettingKey($key), $value);
                    }
                }
                $this->flashMessage('success', $this->translator->translate('record.successfully.created'));
            }
        }
        return $this->webService->getRedirectResponse('shell/setting/index');
    }
}
