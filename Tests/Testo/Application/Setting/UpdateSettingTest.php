<?php

declare(strict_types=1);

namespace Tests\Testo\Application\Setting;

use App\Application\Setting\UpdateSetting;
use App\Domain\Setting\Setting;
use App\Domain\Setting\SettingKey;
use App\Domain\Setting\SettingRepositoryInterface;
use Mockery as m;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;

#[Test]
#[Covers(UpdateSetting::class)]
final class UpdateSettingTest
{
    public function createsANewSettingWhenNoneExistsYet(): void
    {
        $key = new SettingKey('shell_app_name');
        $repository = m::mock(SettingRepositoryInterface::class);
        /** @var \Mockery\Expectation $findExpectation */
        $findExpectation = $repository->shouldReceive('find');
        $findExpectation->once()->andReturn(null);

        /** @var Setting|null $saved */
        $saved = null;
        /** @var \Mockery\Expectation $saveExpectation */
        $saveExpectation = $repository->shouldReceive('save');
        $saveExpectation->once()->andReturnUsing(function (Setting $setting) use (&$saved): void {
            $saved = $setting;
        });

        $updateSetting = new UpdateSetting($repository);

        $updateSetting($key, 'My App');

        Assert::instanceOf($saved, Setting::class);
        Assert::same($saved->value(), 'My App');
        Assert::true($saved->key()->equals($key));
    }

    public function updatesAnExistingSettingsValue(): void
    {
        $key = new SettingKey('shell_app_name');
        $existing = new Setting($key, 'Old Value');
        $repository = m::mock(SettingRepositoryInterface::class);
        /** @var \Mockery\Expectation $findExpectation */
        $findExpectation = $repository->shouldReceive('find');
        $findExpectation->once()->andReturn($existing);

        /** @var Setting|null $saved */
        $saved = null;
        /** @var \Mockery\Expectation $saveExpectation */
        $saveExpectation = $repository->shouldReceive('save');
        $saveExpectation->once()->andReturnUsing(function (Setting $setting) use (&$saved): void {
            $saved = $setting;
        });

        $updateSetting = new UpdateSetting($repository);

        $updateSetting($key, 'New Value');

        Assert::instanceOf($saved, Setting::class);
        Assert::same($saved->value(), 'New Value');
    }
}
