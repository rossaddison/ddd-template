<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/**
 * @var string $alert
 * @var array<string, mixed> $info
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */
?>
<?= $alert ?>

<h1>Webhook info</h1>

<?php if ($info === []): ?>
    <p>No webhook info available — the bot may not be configured yet.</p>
<?php else: ?>
    <table class="table">
        <tbody>
        <?php
        /** @var mixed $value */
        foreach ($info as $key => $value): ?>
            <tr>
                <th><?= Html::encode($key) ?></th>
                <td><?= Html::encode(is_scalar($value) ? (string) $value : json_encode($value)) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="<?= Html::encode($urlGenerator->generate('shell/telegram/index')) ?>" class="btn btn-outline-secondary">Back</a>
