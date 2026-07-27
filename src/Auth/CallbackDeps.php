<?php

declare(strict_types=1);

namespace App\Auth;

use App\User\UserRepository;
use Yiisoft\Translator\TranslatorInterface;

final class CallbackDeps
{
    public function __construct(
        public readonly TranslatorInterface $translator,
        public readonly UserRepository $uR,
    ) {
    }
}
