<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Feature\Loggers;

use Brackets\AdvancedLogger\Handlers\RequestLoggerHandler;
use Brackets\AdvancedLogger\Loggers\RequestLogger;
use Brackets\AdvancedLogger\Tests\TestCase;
use Illuminate\Contracts\Config\Repository;
use RuntimeException;

final class RequestLoggerTest extends TestCase
{
    public function testLoggerCanBeConstructedFromContainer(): void
    {
        $logger = $this->app->make(RequestLogger::class);

        self::assertInstanceOf(RequestLogger::class, $logger);
    }

    public function testInfoCallDelegatesToMonolog(): void
    {
        $config = $this->app->make(Repository::class);
        $config->set('advanced-logger.request.handlers', [RequestLoggerHandler::class]);
        $config->set('advanced-logger.request.enabled', true);

        $logger = $this->app->make(RequestLogger::class);
        $logger->info('test info message');

        self::assertFileExists($this->getRequestLogFileName());
        self::assertStringContainsString('test info message', file_get_contents($this->getRequestLogFileName()));
    }

    public function testErrorCallDelegatesToMonolog(): void
    {
        $config = $this->app->make(Repository::class);
        $config->set('advanced-logger.request.handlers', [RequestLoggerHandler::class]);
        $config->set('advanced-logger.request.enabled', true);

        $logger = $this->app->make(RequestLogger::class);
        $logger->error('test error message');

        self::assertFileExists($this->getRequestLogFileName());
        self::assertStringContainsString('test error message', file_get_contents($this->getRequestLogFileName()));
    }

    public function testThrowsRuntimeExceptionWhenHandlerClassDoesNotExist(): void
    {
        $config = $this->app->make(Repository::class);
        $config->set('advanced-logger.request.handlers', ['NonExistentHandlerClass']);
        $config->set('advanced-logger.request.enabled', true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Handler class [NonExistentHandlerClass] does not exist');

        $this->app->make(RequestLogger::class);
    }
}
