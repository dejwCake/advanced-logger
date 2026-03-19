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

final class HashLogCustomizerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testSetsLineWithHashFormatterOnFormattableHandlers(): void
    {
        /** @var Repository&MockInterface $config */
        $config = Mockery::mock(Repository::class);
        $config->shouldReceive('get')->andReturn(null);

        $formatter = new LineWithHashFormatter($config);

        /** @var Container&MockInterface $container */
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('make')
            ->once()
            ->with(
                LineWithHashFormatter::class,
                ['format' => "[%datetime%] %hash% %channel%.%level_name%: %message% %context% %extra%\n"],
            )
            ->andReturn($formatter);

        /** @var (FormattableHandlerInterface&HandlerInterface&MockInterface) $handler */
        $handler = Mockery::mock(FormattableHandlerInterface::class . ',' . HandlerInterface::class);
        $handler->shouldReceive('setFormatter')->once()->with($formatter);

        $logger = new TestLogger([$handler]);

        $customizer = new HashLogCustomizer($container);
        $customizer($logger);

        self::assertInstanceOf(LineWithHashFormatter::class, $formatter);
    }

    public function testSkipsHandlersThatDoNotImplementFormattableHandlerInterface(): void
    {
        /** @var Container&MockInterface $container */
        $container = Mockery::mock(Container::class);
        $container->shouldNotReceive('make');

        /** @var HandlerInterface&MockInterface $handler */
        $handler = Mockery::mock(HandlerInterface::class);

        $logger = new TestLogger([$handler]);

        $customizer = new HashLogCustomizer($container);
        $customizer($logger);

        self::assertInstanceOf(HandlerInterface::class, $handler);
    }

    public function testDoesNothingWhenLoggerHasNoGetHandlersMethod(): void
    {
        /** @var Container&MockInterface $container */
        $container = Mockery::mock(Container::class);
        $container->shouldNotReceive('make');

        $loggerRef = new ReflectionClass(Logger::class);
        $loggerWithoutGetHandlers = $loggerRef->newInstanceWithoutConstructor();

        self::assertFalse(method_exists($loggerWithoutGetHandlers, 'getHandlers'));

        $customizer = new HashLogCustomizer($container);
        $customizer($loggerWithoutGetHandlers);
    }
}
