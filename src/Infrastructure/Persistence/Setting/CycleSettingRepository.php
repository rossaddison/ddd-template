<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Setting;

use App\Domain\Setting\Setting as DomainSetting;
use App\Domain\Setting\SettingKey;
use App\Domain\Setting\SettingRepositoryInterface;
use Cycle\ORM\Select;
use Yiisoft\Data\Writer\DataWriterInterface;

/**
 * Implements the Domain-facing SettingRepositoryInterface against the same
 * `setting` table already used by the invoice app's own
 * App\Invoice\Setting\SettingRepository — no schema change, no second table.
 *
 * Request-scope cache mirrors the guard in the existing SettingRepository's
 * loadSettings() (`if ($this->settingsArray !== []) return;`), which exists
 * because its absence previously caused one query per getSetting() call
 * (41 queries/page). Kept here in the Infrastructure implementation, not the
 * Domain interface, since caching is a persistence concern, not a domain one.
 *
 * @template TEntity of Setting
 * @extends Select\Repository<TEntity>
 */
final class CycleSettingRepository extends Select\Repository implements SettingRepositoryInterface
{
    /** @var array<string, string>|null */
    private ?array $cache = null;

    /**
     * @param Select<TEntity> $select
     */
    public function __construct(
        Select $select,
        private readonly DataWriterInterface $entityWriter,
    ) {
        parent::__construct($select);
    }

    #[\Override]
    public function find(SettingKey $key): ?DomainSetting
    {
        $this->loadCache();
        /** @var array<string, string> $cache */
        $cache = $this->cache;
        $value = $cache[$key->value()] ?? null;
        return $value === null ? null : new DomainSetting($key, $value);
    }

    #[\Override]
    public function save(DomainSetting $setting): void
    {
        $this->loadCache();
        $entity = $this->select()
            ->where('setting_key', $setting->key()->value())
            ->fetchOne();
        if (!$entity instanceof Setting) {
            $entity = new Setting($setting->key()->value(), $setting->value());
        } else {
            $entity->setSettingValue($setting->value());
        }
        $this->entityWriter->write([$entity]);
        /** @var array<string, string> $cache */
        $cache = $this->cache;
        $cache[$setting->key()->value()] = $setting->value();
        $this->cache = $cache;
    }

    #[\Override]
    public function all(): iterable
    {
        $this->loadCache();
        /** @var array<string, string> $cache */
        $cache = $this->cache;
        foreach ($cache as $key => $value) {
            yield new DomainSetting(new SettingKey($key), $value);
        }
    }

    private function loadCache(): void
    {
        if ($this->cache !== null) {
            return;
        }
        $cache = [];
        /** @var Setting $entity */
        foreach ($this->select()->fetchAll() as $entity) {
            $cache[$entity->getSettingKey()] = $entity->getSettingValue();
        }
        $this->cache = $cache;
    }
}
