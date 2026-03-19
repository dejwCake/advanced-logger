<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\LogCustomizers;

use Brackets\AdvancedLogger\Formatters\LineWithHashFormatter;
use Brackets\AdvancedLogger\LogCustomizers\HashLogCustomizer;
use Brackets\AdvancedLogger\Tests\TestLogger;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Log\Logger;
use Mockery;
use Mockery\MockInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function assert;

final class HashLogCustomizerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testSetsLineWithHashFormatterOnFormattableHandlers(): void
    {
        $config = Mockery::mock(Repository::class);
        assert($config instanceof Repository && $config instanceof MockInterface);
        $config->shouldReceive('get')->andReturn(null);

        $formatter = new LineWithHashFormatter($config);

        $container = Mockery::mock(Container::class);
        assert($container instanceof Container && $container instanceof MockInterface);
        $container->shouldReceive('make')
            ->once()
            ->with(
                LineWithHashFormatter::class,
                ['format' => "[%datetime%] %hash% %channel%.%level_name%: %message% %context% %extra%\n"],
            )
            ->andReturn($formatter);

        $handler = Mockery::mock(FormattableHandlerInterface::class . ',' . HandlerInterface::class);
        assert(
            $handler instanceof FormattableHandlerInterface
            && $handler instanceof HandlerInterface
            && $handler instanceof MockInterface,
        );
        $handler->shouldReceive('setFormatter')->once()->with($formatter);

        $logger = new TestLogger([$handler]);

        $customizer = new HashLogCustomizer($container);
        $customizer($logger);

        self::assertInstanceOf(LineWithHashFormatter::class, $formatter);
    }

    public function testSkipsHandlersThatDoNotImplementFormattableHandlerInterface(): void
    {
        $container = Mockery::mock(Container::class);
        assert($container instanceof Container && $container instanceof MockInterface);
        $container->shouldNotReceive('make');

        $handler = Mockery::mock(HandlerInterface::class);
        assert($handler instanceof HandlerInterface && $handler instanceof MockInterface);

        $logger = new TestLogger([$handler]);

        $customizer = new HashLogCustomizer($container);
        $customizer($logger);

        self::assertInstanceOf(HandlerInterface::class, $handler);
    }

    public function testDoesNothingWhenLoggerHasNoGetHandlersMethod(): void
    {
        $container = Mockery::mock(Container::class);
        assert($container instanceof Container && $container instanceof MockInterface);
        $container->shouldNotReceive('make');

        $loggerRef = new ReflectionClass(Logger::class);
        $loggerWithoutGetHandlers = $loggerRef->newInstanceWithoutConstructor();

        self::assertFalse(method_exists($loggerWithoutGetHandlers, 'getHandlers'));

        $customizer = new HashLogCustomizer($container);
        $customizer($loggerWithoutGetHandlers);
    }
}
