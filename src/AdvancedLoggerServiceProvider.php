<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger;

use Brackets\AdvancedLogger\Providers\EventServiceProvider;
use Brackets\AdvancedLogger\Services\Benchmark;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\ServiceProvider;

class AdvancedLoggerServiceProvider extends ServiceProvider
{
    use DispatchesJobs;

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/advanced-logger.php' => config_path('advanced-logger.php'),
        ], 'config');
        $this->app->register(EventServiceProvider::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/advanced-logger.php', 'advanced-logger');
        Benchmark::start(config('advanced-logger.request.benchmark', 'application'));
    }
}
