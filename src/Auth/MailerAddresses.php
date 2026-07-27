<?php

declare(strict_types=1);

namespace App\Auth;

/**
 * The sender/admin addresses configured under params.php's 'mailer' key
 * (ADMIN_EMAIL/SENDER_EMAIL env vars) — built via a DI factory in
 * config/common/di/mailer.php rather than re-reading config at call time.
 */
final readonly class MailerAddresses
{
    public function __construct(
        public string $senderEmail,
        public string $adminEmail,
    ) {
    }
}
