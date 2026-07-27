<?php

declare(strict_types=1);

use Yiisoft\Bootstrap5\Alert;
use Yiisoft\Bootstrap5\AlertVariant;

/** @var Yiisoft\Session\Flash\Flash $flash */

$variants = [
    'danger' => AlertVariant::DANGER,
    'info' => AlertVariant::INFO,
    'primary' => AlertVariant::PRIMARY,
    'secondary' => AlertVariant::SECONDARY,
    'success' => AlertVariant::SUCCESS,
    'warning' => AlertVariant::WARNING,
    'light' => AlertVariant::LIGHT,
    'dark' => AlertVariant::DARK,
];

/**
 * @var array|string $value
 * @var string $key
 */
foreach ($flash->getAll() as $key => $value) {
    if (!is_array($value)) {
        continue;
    }
    /** @var Stringable|string $body */
    foreach ($value as $body) {
        echo Alert::widget()
            ->variant($variants[$key] ?? AlertVariant::INFO)
            ->body($body, true)
            ->dismissable(true)
            ->render();
    }
}
