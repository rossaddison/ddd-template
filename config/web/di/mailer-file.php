<?php

declare(strict_types=1);

use Yiisoft\Mailer\FileMailer;
use Yiisoft\Mailer\MailerInterface;

/**
 * Local dev convenience: every email this app would send (e.g.
 * ForgotPasswordController's reset-link email) gets written to
 * runtime/mail/*.eml instead of actually being sent, so testing an
 * email-driven flow never needs a real inbox — open the newest file in
 * runtime/mail/ with any text editor and the link is right there in the
 * body. FileMailer itself is already registered by yiisoft/mailer (see
 * vendor/yiisoft/mailer/config/di.php, path taken from
 * $params['yiisoft/mailer']['fileMailer']['path'] = '@runtime/mail') —
 * this just points MailerInterface at it.
 *
 * Strictly opt-in to YII_ENV=dev so prod and test keep the real
 * yiisoft/mailer-symfony ESMTP transport untouched.
 */
return ($_ENV['YII_ENV'] ?? '') === 'dev'
    ? [MailerInterface::class => FileMailer::class]
    : [];
