<?php

declare(strict_types=1);

namespace App\Widget;

use Yiisoft\Bootstrap5\Button as B5;

final readonly class Button
{
    public static function tfaToggleSecret(): string
    {
        return B5::widget()
        // The encode => false option ensures the span (icon) is rendered as HTML, not escaped text.
        ->label('<span id="eyeIcon" class="bi bi-eye"></span>', false)
        ->class('btn', 'btn-outline-primary')
        ->id('toggleSecret')
        ->attribute('type', 'button')
        ->render();
    }

    public static function tfaCopyToClipboard(): string
    {
        return B5::widget()
        ->label('<span id="copySecret" class="bi bi-clipboard"></span>', false)
        ->class('btn', 'btn-outline-primary')
        ->id('copySecret')
        ->attribute('type', 'button')
        ->attribute('title', 'Copy to clipboard')
        ->render();
    }
}
