<?php

declare(strict_types=1);

namespace App\Auth;

/**
 * The two roles this template ships with (see resources/rbac/items.php):
 * the first user to ever sign up becomes ROLE_ADMIN automatically, everyone
 * after gets ROLE_USER. Add more roles as your app grows past "no frills".
 */
final class Roles
{
    public const string ADMIN = 'shell-admin';

    public const string USER = 'user';
}
