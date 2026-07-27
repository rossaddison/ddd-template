<?php

declare(strict_types=1);

use App\Shell\ViewInjection\ShellViewInjection;
use Psr\Log\LogLevel;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Definitions\Reference;
use Yiisoft\Form\Field\SubmitButton;
use Yiisoft\Form\Field\Checkbox;
use Yiisoft\Form\Field\RadioList;
use Yiisoft\Form\Field\CheckboxLabelPlacement;
use Yiisoft\Form\Field\ErrorSummary;
use Yiisoft\FormModel\ValidationRulesEnricher;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\Cycle\Schema\Conveyor\MetadataSchemaConveyor;
use Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider;
use Cycle\Schema\Provider\PhpFileSchemaProvider;
use Yiisoft\Yii\View\Renderer\CsrfViewInjection;

$env = $_ENV['APP_ENV'] ?? 'local';
$dbUser = ($_ENV['DB_USERNAME'] ?? '') ?: 'root';
$dbName = ($_ENV['DB_NAME'] ?? '') ?: 'ddd_template';
$dbPassword = ($_ENV['DB_PASSWORD'] ?? '') ?: null;

switch ($env) {
    case 'docker':
        $dbHost = $_ENV['DB_HOST_IP_ADDRESS'] ?? '192.168.0.24';
        break;
    default:
        $dbHost = $_ENV['DB_HOST_IP_ADDRESS'] ?? 'localhost';
}
$buttonClass = 'buttonClass()';
$containerClass = 'containerClass()';
$submitButtonConfigs = [
    'default' => [
        $buttonClass => ['btn btn-primary btn-sm mt-3'],
        $containerClass => ['d-grid gap-2'],
    ],
    'bootstrap5-vertical' => [
        $buttonClass => ['btn btn-primary'],
    ],
    'bootstrap5-horizontal' => [
        $buttonClass => ['btn btn-primary'],
    ],
];

return [
    'yiisoft/log-target-file' => [
        'fileTarget' => [
            'file' => '@runtime/logs/app.log',
            'levels' => [
                LogLevel::EMERGENCY,
                LogLevel::ERROR,
                LogLevel::WARNING,
                LogLevel::INFO,
                LogLevel::DEBUG,
            ],
            'dirMode' => 0o755,
            'fileMode' => null,
        ],
        'fileRotator' => [
            'maxFileSize' => 500,
            'maxFiles' => 100,
            'fileMode' => null,
            'compressRotatedFiles' => false,
        ],
    ],

    'env' => $_ENV['YII_ENV'] ?? 'dev',

    'server' => [
        'remote_port' => $_SERVER['REMOTE_PORT'] ?? null,
        'http_x_forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
        'http_client_ip' => $_SERVER['HTTP_CLIENT_IP'] ?? null,
    ],
    'license' => [
        'id' => 'ddd-template_BSD-3-Clause',
    ],
    'product' => [
        'name'    => 'DDD Template',
        'version' => 'pre-release',
        'server'  => PHP_VERSION,
    ],
    'mailer' => [
        'adminEmail' => ($_ENV['ADMIN_EMAIL'] ?? '') ?: 'info@yourhost.com',
        // Used by src/Auth/Controller/SignupController and ForgotPasswordController.
        'senderEmail' => ($_ENV['SENDER_EMAIL'] ?? '') ?: 'info@yourhost.com',
    ],

    /**
     * Generic social-login providers only. Related logic: src/Auth/Controller/AuthController
     * function login(); resources/views/auth/login.php.
     */
    'yiisoft/yii-auth-client' => [
        'enabled' => true,
        'clients' => [
            'facebook' => [
                'class' => 'Yiisoft\Yii\AuthClient\Client\Facebook::class',
                'clientId' => $_ENV['FACEBOOK_API_CLIENT_ID'] ?? '',
                'clientSecret' => $_ENV['FACEBOOK_API_CLIENT_SECRET'] ?? '',
                'returnUrl' => $_ENV['FACEBOOK_API_CLIENT_RETURN_URL'] ?? '',
            ],
            'github' => [
                'class' => 'Yiisoft\Yii\AuthClient\Client\Github::class',
                'clientId' => $_ENV['GITHUB_API_CLIENT_ID'] ?? '',
                'clientSecret' => $_ENV['GITHUB_API_CLIENT_SECRET'] ?? '',
                'returnUrl' => $_ENV['GITHUB_API_CLIENT_RETURN_URL'] ?? '',
            ],
            'google' => [
                'class' => 'Yiisoft\Yii\AuthClient\Client\Google::class',
                'clientId' => $_ENV['GOOGLE_API_CLIENT_ID'] ?? '',
                'clientSecret' => $_ENV['GOOGLE_API_CLIENT_SECRET'] ?? '',
                'returnUrl' => $_ENV['GOOGLE_API_CLIENT_RETURN_URL'] ?? '',
            ],
            'linkedin' => [
                'class' => 'Yiisoft\Yii\AuthClient\Client\LinkedIn::class',
                'clientId' => $_ENV['LINKEDIN_API_CLIENT_ID'] ?? '',
                'clientSecret' => $_ENV['LINKEDIN_API_CLIENT_SECRET'] ?? '',
                'returnUrl' => $_ENV['LINKEDIN_API_CLIENT_RETURN_URL'] ?? '',
            ],
            'microsoftonline' => [
                'class' => 'Yiisoft\Yii\AuthClient\Client\MicrosoftOnline::class',
                'clientId' => $_ENV['MICROSOFTONLINE_API_CLIENT_ID'] ?? '',
                'clientSecret' => $_ENV['MICROSOFTONLINE_API_CLIENT_SECRET'] ?? '',
                'returnUrl' => $_ENV['MICROSOFTONLINE_API_CLIENT_RETURN_URL'] ?? '',
                // 'common', 'organisations', 'consumers', or a tenant ID
                'tenant' => $_ENV['MICROSOFTONLINE_API_CLIENT_TENANT'] ?? 'common',
            ],
            'vkontakte' => [
                'class' => 'Yiisoft\Yii\AuthClient\Client\VKontakte::class',
                'clientId' => $_ENV['VKONTAKTE_API_CLIENT_ID'] ?? '',
                'clientSecret' => $_ENV['VKONTAKTE_API_CLIENT_SECRET'] ?? '',
                'returnUrl' => $_ENV['VKONTAKTE_API_CLIENT_RETURN_URL'] ?? '',
            ],
            'x' => [
                'class' => 'Yiisoft\Yii\AuthClient\Client\X::class',
                'clientId' => $_ENV['X_API_CLIENT_ID'] ?? '',
                'clientSecret' => $_ENV['X_API_CLIENT_SECRET'] ?? '',
                'returnUrl' => $_ENV['X_API_CLIENT_RETURN_URL'] ?? '',
            ],
            'yandex' => [
                'class' => 'Yiisoft\Yii\AuthClient\Client\Yandex::class',
                'clientId' => $_ENV['YANDEX_API_CLIENT_ID'] ?? '',
                'clientSecret' => $_ENV['YANDEX_API_CLIENT_SECRET'] ?? '',
                'returnUrl' => $_ENV['YANDEX_API_CLIENT_RETURN_URL'] ?? '',
            ],
        ],
    ],
    'yiisoft/aliases' => [
        'aliases' => [
            '@root' => dirname(__DIR__, 2),
            '@views' => dirname(__DIR__, 2) . '/resources/views',
            '@assets' => '@root/public/assets',
            '@assetsUrl' => '@baseUrl/assets',
            '@baseUrl' => '',
            '@messages' => '@resources/messages',
            '@npm' => '@root/node_modules',
            '@public' => '@root/public',
            '@resources' => '@root/resources',
            '@runtime' => '@root/runtime',
            '@src' => '@root/src',
            '@validatorMessages' => '@vendor/yiisoft/validator/messages',
            '@vendor' => '@root/vendor',
            '@layout' => '@views/layout',
        ],
    ],
    'yiisoft/form' => [
        'themes' => [
            'defaultTheme' => 'bootstrap5-vertical',
            'validationRulesEnricher' => new ValidationRulesEnricher(),
            'default' => [
                'containerClass' => 'form-floating mb-3',
                'inputClass' => 'form-control',
                'invalidClass' => 'is-invalid',
                'validClass' => 'is-valid',
                'template' => '{input}{label}{hint}{error}',
                'labelClass' => 'floatingInput h6',
                'errorClass' => 'invalid-feedback',
                'hintClass' => 'form-text text-muted',
                'fieldConfigs' => [
                    $submitButtonConfigs['default'],
                    Checkbox::class => [
                        $containerClass => ['form-check mb-3'],
                        'inputClass()' => ['form-check-input'],
                        'inputLabelClass()' => ['form-check-label'],
                        'labelPlacement()' => [CheckboxLabelPlacement::SIDE],
                    ],
                    RadioList::class => [
                        $containerClass => ['mb-3'],
                        'template()' => ["{label}\n{input}\n{hint}\n{error}"],
                        'labelClass()' => ['form-label'],
                    ],
                ],
            ],
            'bootstrap5-vertical' => [
                'template' => "{label}\n{input}\n{hint}\n{error}",
                'containerClass' => 'mb-3',
                'labelClass' => 'form-label',
                'inputClass' => 'form-control',
                'hintClass' => 'form-text',
                'errorClass' => 'invalid-feedback',
                'inputValidClass' => 'is-valid',
                'inputInvalidClass' => 'is-invalid',
                'fieldConfigs' => [
                    ErrorSummary::class => [
                        $containerClass => ['alert alert-danger'],
                        'listAttributes()' => [['class' => 'mb-0']],
                        'header()' => [''],
                    ],
                    SubmitButton::class =>
                                    $submitButtonConfigs['bootstrap5-vertical'],
                ],
                'enrichFromValidationRules' => true,
            ],
            'bootstrap5-horizontal' => [
                'template' =>
             "{label}\n<div class=\"col-sm-10\">{input}\n{hint}\n{error}</div>",
                'containerClass' => 'row mb-3',
                'labelClass' => 'col-sm-2 col-form-label',
                'inputClass' => 'form-control',
                'hintClass' => 'form-text',
                'errorClass' => 'invalid-feedback',
                'inputValidClass' => 'is-valid',
                'inputInvalidClass' => 'is-invalid',
                'fieldConfigs' => [
                    SubmitButton::class =>
                                    $submitButtonConfigs['bootstrap5-horizontal'],
                    ErrorSummary::class => [
                        $containerClass => ['alert alert-danger'],
                        'listClass()' => ['mb-0'],
                        'header()' => [''],
                    ],
                ],
                'enrichFromValidationRules' => true,
            ],
        ],
    ],
    'yiisoft/rbac-rules-container' => [
        'rules' => require_once __DIR__ . '/rbac-rules.php',
    ],
    'yiisoft/router-fastroute' => [
        'enableCache' => false,
        'encodeRaw' => true,
    ],
    'yiisoft/translator' => [
        'locale' => 'en',
        'fallbackLocale' => 'en',
        'defaultCategory' => 'app',
        'validatorCategory' => 'yii-validator',
    ],
    'yiisoft/view' => [
        'basePath' => '@views',
        'parameters' => [
            'assetManager' => Reference::to(AssetManager::class),
            'urlGenerator' => Reference::to(UrlGeneratorInterface::class),
            'currentRoute' => Reference::to(CurrentRoute::class),
            'translator' => Reference::to(TranslatorInterface::class),
            'session' => Reference::to(SessionInterface::class),
        ],
    ],
    'yiisoft/cookies' => [
        // Must be set per deployment via COOKIE_SECRET_KEY — signs/encrypts
        // cookies including the "remember me" login flow. Falls back to ''
        // (fails safe) rather than a shared literal secret.
        'secretKey' => $_ENV['COOKIE_SECRET_KEY'] ?? '',
    ],
    'yiisoft/yii-view-renderer' => [
        'viewPath' => '@views',
        'layout' => '@views/layout/guest.php',
        'injections' => [
            Reference::to(CsrfViewInjection::class),
            Reference::to(ShellViewInjection::class),
        ],
    ],
    'yiisoft/yii-cycle' => [
        'dbal' => [
            'query-logger' => null,
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'mysql'],
            ],
            'connections' => [
                'mysql' => new Cycle\Database\Config\MySQLDriverConfig(
                    connection: new Cycle\Database\Config\MySQL\DsnConnectionConfig(
                        'mysql:host=' . $dbHost . ';dbname='. $dbName,
                        $dbUser,
                        $dbPassword,
                    ),
                    driver: Cycle\Database\Driver\MySQL\MySQLDriver::class,
                ),
            ],
        ],
        'migrations' => [
            'directory' => '@root/migrations',
            'namespace' => 'App\\Migration',
            'table' => 'migration',
            'safe' => false,
        ],
        /**
         * Schema sync workflow (no migration files — entity-driven):
         * 1. Set .env BUILD_DATABASE=true
         * 2. Change/add an entity
         * 3. Delete runtime/schema.php
         * 4. Reload the app (web request or `./yii` command) — schema.php
         *    rebuilds and SyncTables applies the diff to the live DB
         * 5. Revert BUILD_DATABASE to false
         */
        'schema-providers' => [
            PhpFileSchemaProvider::class => [
                /** @psalm-suppress RiskyTruthyFalsyComparison */
                'mode' => $_ENV['BUILD_DATABASE'] ?? '' ?
                    PhpFileSchemaProvider::MODE_WRITE_ONLY :
                    PhpFileSchemaProvider::MODE_READ_AND_WRITE,
                'file' => 'runtime/schema.php',
            ],
            FromConveyorSchemaProvider::class => [
                'generators' => [
                    Cycle\Schema\Generator\SyncTables::class,
                ],
            ],
        ],
        // Collection factories — see https://cycle-orm.dev/docs/relation-collections/2.x
        'collections' => [
            'default' => 'doctrine',
            'factories' => [
                'doctrine' => \Cycle\ORM\Collection\DoctrineCollectionFactory::class,
            ],
        ],
        'entity-paths' => [
            '@src',
            '@src/Infrastructure',
        ],
        'conveyor' => MetadataSchemaConveyor::class,
    ],
    'yiisoft/mailer' => [
        'fileMailer' => [
            'path' => '@runtime/mail',
        ],
    ],
    'yiisoft/mailer-symfony' => [
        'esmtpTransport' => [
            'enabled' => true,
            'useSendMail' => false,
            'scheme' => 'smtps',
            'host' => $_ENV['SYMFONY_MAILER_HOST'] ?? 'smtp.gmail.com',
            'port' => (int) ($_ENV['SYMFONY_MAILER_PORT'] ?? 465),
            'username' => $_ENV['SYMFONY_MAILER_USERNAME'] ?? '',
            'password' => $_ENV['SYMFONY_MAILER_PASSWORD'] ?? '',
            'options' => [],
        ],
        'messageSettings' => [
            'charset' => 'utf-8',
            'from' => null,
            'addFrom' => null,
            'to' => null,
            'addTo' => null,
            'replyTo' => null,
            'addReplyTo' => null,
            'cc' => null,
            'addCc' => null,
            'bcc' => null,
            'addBcc' => null,
            'subject' => null,
            'date' => null,
            'priority' => null,
            'returnPath' => null,
            'sender' => null,
            'textBody' => null,
            'htmlBody' => null,
            'attachments' => null,
            'addAttachments' => null,
            'embeddings' => null,
            'addEmbeddings' => null,
            'headers' => [],
            'overwriteHeaders' => null,
            'convertHtmlToText' => true,
        ],
    ],
];
