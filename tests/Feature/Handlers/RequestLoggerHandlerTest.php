<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Feature\Handlers;

use Brackets\AdvancedLogger\Handlers\RequestLoggerHandler;
use Brackets\AdvancedLogger\Tests\TestCase;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Monolog\Handler\RotatingFileHandler;

final class RequestLoggerHandlerTest extends TestCase
{
    public function testHandlerUsesFilenameFromConfig(): void
    {
        $config = $this->app->make(Repository::class);
        $app = $this->app->make(Application::class);

        $handler = new RequestLoggerHandler($config, $app);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);
        $expectedBase = $this->getFixturesDirectory('request.log');
        $expectedUrl = str_replace('.log', '-' . CarbonImmutable::now()->format('Y-m-d') . '.log', $expectedBase);
        self::assertSame($expectedUrl, $handler->getUrl());
    }

    public function testHandlerUsesExplicitFilenameWhenProvided(): void
    {
        $config = $this->app->make(Repository::class);
        $app = $this->app->make(Application::class);
        $explicitPath = $this->getFixturesDirectory('explicit.log');

        $handler = new RequestLoggerHandler($config, $app, $explicitPath);

        $expectedUrl = str_replace('.log', '-' . CarbonImmutable::now()->format('Y-m-d') . '.log', $explicitPath);
        self::assertSame($expectedUrl, $handler->getUrl());
    }

    public function testHandlerUsesDefaultStoragePathWhenConfigHasNoValue(): void
    {
        $config = $this->app->make(Repository::class);
        $app = $this->app->make(Application::class);
        $config->set('advanced-logger.request', []);

        $handler = new RequestLoggerHandler($config, $app);

        $defaultBase = $app->storagePath('logs/request.log');
        $expectedUrl = str_replace('.log', '-' . CarbonImmutable::now()->format('Y-m-d') . '.log', $defaultBase);
        self::assertSame($expectedUrl, $handler->getUrl());
    }
}
