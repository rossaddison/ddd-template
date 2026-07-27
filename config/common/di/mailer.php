<?php

declare(strict_types=1);

use App\Auth\MailerAddresses;

/**
 * @var array $params
 * @var array{senderEmail: string, adminEmail: string} $params['mailer']
 */
$mailer = $params['mailer'];

return [
    MailerAddresses::class => static fn (): MailerAddresses =>
        new MailerAddresses($mailer['senderEmail'], $mailer['adminEmail']),
];
