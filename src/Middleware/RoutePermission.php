<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;

/**
 * Shared route-config helper to avoid repeating the same permission-check
 * closure across every file in config/common/routes/.
 */
final class RoutePermission
{
    public static function check(string $permission): Closure
    {
        return static fn (AccessChecker $checker) => $checker->withPermission($permission);
    }
}
