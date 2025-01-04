<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests;

use Brackets\AdvancedLogger\AdvancedLoggerServiceProvider;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void
    {
        if (file_exists($this->getRequestLogFileName())) {
            unlink($this->getRequestLogFileName());
        }

        parent::tearDown();
    }

    /**
     * @param Application $app
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    protected function getEnvironmentSetUp($app)
    {
        if (file_exists($this->getRequestLogFileName())) {
            unlink($this->getRequestLogFileName());
        }
        $app['config']->set('advanced-logger.request.file', $this->getFixturesDirectory('request.log'));
        $app['config']->set('advanced-logger.request.excluded-paths', ['excluded']);

        Route::get('/', static fn () => 'Hi there.');

        Route::get('/excluded', static fn () => 'This is excluded path.');
    }

    public function getFixturesDirectory(string $path): string
    {
        return __DIR__ . "/fixtures/{$path}";
    }

    public function getRequestLogFileName(): string
    {
        return $this->getFixturesDirectory('request-' . CarbonImmutable::now()->format('Y-m-d') . '.log');
    }

    /**
     * @param Application $app
     * @return array<class-string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    protected function getPackageProviders($app): array
    {
        return [
            AdvancedLoggerServiceProvider::class,
        ];
    }
}
