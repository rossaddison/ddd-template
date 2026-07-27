<?php

declare(strict_types=1);

/**
 * PHP-file-backed RBAC items (yiisoft/rbac-php) — role/permission definitions
 * only. Who has which role lives in the DB instead (yiisoft/rbac-cycle-db,
 * see config/common/di/rbac.php) via `php yii user/assignRole <role> <userId>`.
 *
 * shell-admin: auto-assigned to the first user who ever signs up (see
 * SignupController/Trait\Callback's assignRoleAndVerify()). Also required
 * for the mandatory-2FA-for-admins gate in AuthController::isAdminUser().
 *
 * user: everyone after the first signup. Add more roles/permissions here as
 * you build new modules on top of this template — nothing else needs to
 * change for a route to start enforcing a new permission string, see
 * App\Middleware\RoutePermission::check().
 */
return [
    [
        'name' => 'shell-admin',
        'type' => 'role',
        'updated_at' => 1753574400,
        'created_at' => 1753574400,
        'children' => [
            'access.shell',
            'manage.shell.settings',
            'manage.shell.telegram',
        ],
    ],
    [
        'name' => 'user',
        'type' => 'role',
        'updated_at' => 1753574400,
        'created_at' => 1753574400,
        'children' => [
            'access.shell',
        ],
    ],
    [
        'name' => 'access.shell',
        'type' => 'permission',
        'updated_at' => 1753574400,
        'created_at' => 1753574400,
    ],
    [
        'name' => 'manage.shell.settings',
        'type' => 'permission',
        'updated_at' => 1753574400,
        'created_at' => 1753574400,
    ],
    [
        'name' => 'manage.shell.telegram',
        'type' => 'permission',
        'updated_at' => 1753574400,
        'created_at' => 1753574400,
    ],
];
