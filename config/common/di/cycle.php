<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Setting\CycleSettingRepository;
use App\Infrastructure\Persistence\Setting\Setting;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseManager;
use Cycle\ORM\Collection\DoctrineCollectionFactory;
use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Yiisoft\Data\Writer\DataWriterInterface;

/** @var array $params */

return [
    // Replace Factory definition to redefine default collection type
    // https://github.com/yiisoft/yii-cycle/pull/141
    FactoryInterface::class => static function (DatabaseManager $dbManager, Spiral\Core\FactoryInterface $factory) {
        return new Factory(
            $dbManager,
            null,
            $factory,
            new DoctrineCollectionFactory(),
        );
    },

    // Bind the default database so Cycle-based repositories can inject DatabaseInterface.
    DatabaseInterface::class => static function (DatabaseManager $dbManager): DatabaseInterface {
        return $dbManager->database();
    },

    // CycleSettingRepository extends Select\Repository and needs Select<Setting>
    // which cannot be auto-resolved by the DI container — wire it explicitly here.
    // Domain\Setting\SettingRepositoryInterface binds to this class in
    // config/common/di/shell.php.
    CycleSettingRepository::class => static function (
        ORMInterface $orm,
        DataWriterInterface $writer,
    ): CycleSettingRepository {
        return new CycleSettingRepository(
            new Select($orm, Setting::class),
            $writer,
        );
    },
];
