<?php

declare(strict_types=1);

namespace App\User;

use App\Infrastructure\Persistence\User\User;
use Cycle\ORM\Select;
use Throwable;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Cycle\Writer\EntityWriter;

/**
 * @template TEntity of RecoveryCode
 * @extends Select\Repository<TEntity>
 */
final class RecoveryCodeRepository extends Select\Repository
{
    /**
         * @param Select<TEntity> $select
         * @param EntityWriter $entityWriter
         */
    public function __construct(Select $select, private readonly EntityWriter $entityWriter)
    {
        parent::__construct($select);
    }

    /**
     * @psalm-return EntityReader
     */
    public function findAllPreloaded(): EntityReader
    {
        $query = $this->select();
        return $this->prepareDataReader($query);
    }

    /**
     * @psalm-return EntityReader
     */
    public function getReader(): EntityReader
    {
        return (new EntityReader($this->select()))
            ->withSort($this->getSort());
    }

    private function getSort(): Sort
    {
        return Sort::only(['id'])->withOrder(['id' => 'asc']);
    }

    public function findByUser(User $user): EntityReader
    {
        $userId = $user->reqId();
        $query = $this->select()
                ->where(['user_id' => $userId]);
        return $this->prepareDataReader($query);
    }

    public function findByUserCount(User $user): int
    {
        $userId = $user->reqId();
        return $this->select()
                    ->where(['user_id' => $userId])
                    ->count();
    }

    /**
     * @param array|RecoveryCode|null $backup
     * @psalm-param TEntity $backup
     * @throws Throwable
     */
    public function save(array|RecoveryCode|null $backup): void
    {
        $this->entityWriter->write([$backup]);
    }

    /**
     * @param array|RecoveryCode|null $backup
     * @throws Throwable
     */
    public function delete(array|RecoveryCode|null $backup): void
    {
        $this->entityWriter->delete([$backup]);
    }

    private function prepareDataReader(Select $query): EntityReader
    {
        return (new EntityReader($query))->withSort(
            Sort::only(['id'])
                ->withOrder(['id' => 'asc']),
        );
    }

    /**
     * @psalm-return TEntity|null
     */
    public function repoRecoveryCodeLoadedquery(string $id): ?RecoveryCode
    {
        $query = $this->select()->where(['id' => $id]);
        return  $query->fetchOne() ?: null;
    }

    public function repoCount(string $id): int
    {
        $query = $this->select()
                      ->where(['id' => $id]);
        return $query->count();
    }
}
