<?php

declare(strict_types=1);

use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Bridge\Mockery\MockeryPlugin;

// Must run before any class is autoloaded — without this, Mockery cannot
// mock the `final` Cycle ORM repository classes (UserRepository,
// CycleSettingRepository, etc.) that services in this project are
// constructed with directly, since Mockery generates a subclass of the
// target and PHP forbids subclassing final classes.
DG\BypassFinals::enable();

return new ApplicationConfig(
    src: ['src'],
    plugins: [new MockeryPlugin()],
    suites: [
        new SuiteConfig(
            name: 'Unit',
            location: ['Tests/Testo'],
        ),
    ],
);
