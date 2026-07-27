<?php

declare(strict_types=1);

namespace App\Shell;

/**
 * Deliberately its own class, not reusing/extending App\Auth\Permissions
 * (which mixes generic constants with invoice-specific ones). RBAC checks
 * are purely string-name-driven (see resources/rbac/items.php) — a new
 * permission needs nothing beyond an items.php entry to work.
 */
final class Permissions
{
    public const string ACCESS_SHELL = 'access.shell';

    public const string MANAGE_SETTINGS = 'manage.shell.settings';

    public const string MANAGE_TELEGRAM = 'manage.shell.telegram';
}
