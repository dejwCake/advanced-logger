<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger;

use Brackets\AdvancedLogger\Listeners\RequestLoggerListenerHandler;
use Brackets\AdvancedLogger\Services\Benchmark;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\ServiceProvider;

class AdvancedLoggerServiceProvider extends ServiceProvider
{
    public function boot(Dispatcher $events): void
    {
        $events->listen(RequestHandled::class, RequestLoggerListenerHandler::class);

        if ($this->app->runningInConsole()) {
            $this->publish();
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/advanced-logger.php', 'advanced-logger');

        $config = $this->app->make(Repository::class);
        Benchmark::start($config->get('advanced-logger.request.benchmark', 'application'));
    }

    private function publish(): void
    {
        $this->publishes([
            __DIR__ . '/../config/advanced-logger.php' => $this->app->configPath('advanced-logger.php'),
        ], 'config');
    }
}
