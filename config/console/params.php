<?php

declare(strict_types=1);

use App\Command\CacheClearCommand;
use App\Command\Router\ListCommand;
use App\User\Console\AssignRoleCommand;
use Yiisoft\Yii\Console\Application;
use Yiisoft\Yii\Console\Command\Serve;

return [
    'yiisoft/yii-console' => [
        'name' => Application::NAME,
        'version' => Application::VERSION,
        'autoExit' => false,
        'commands' => [
            'cache/clear' => CacheClearCommand::class,
            'serve' => Serve::class,
            // 'user/create' => CreateCommand::class, // re-enable once src/Auth/Form/SignupForm.php is ported
            'user/assignRole' => AssignRoleCommand::class,
            'router/list' => ListCommand::class,
        ],
    ],
];
