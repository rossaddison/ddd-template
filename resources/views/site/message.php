<?php

declare(strict_types=1);

use Yiisoft\Bootstrap5\Alert;
use Yiisoft\Bootstrap5\AlertVariant;

/**
 * Shared flash-message view for every Auth redirect target — see
 * App\Controller\SiteController.
 *
 * @var string $message
 * @var string $variant 'info' | 'success' | 'warning'
 */
$variantMap = [
    'info' => AlertVariant::INFO,
    'success' => AlertVariant::SUCCESS,
    'warning' => AlertVariant::WARNING,
];

echo Alert::widget()
    ->addClass('shadow')
    ->variant($variantMap[$variant] ?? AlertVariant::INFO)
    ->body($message, true)
    ->dismissable(true)
    ->render();
