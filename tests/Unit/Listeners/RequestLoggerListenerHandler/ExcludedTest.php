<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Listeners\RequestLoggerListenerHandler;

use Brackets\AdvancedLogger\Interpolations\RequestInterpolation;
use Brackets\AdvancedLogger\Interpolations\ResponseInterpolation;
use Brackets\AdvancedLogger\Jobs\RequestLogJob;
use Brackets\AdvancedLogger\Listeners\RequestLoggerListenerHandler;
use Brackets\AdvancedLogger\Loggers\RequestLogger;
use Brackets\AdvancedLogger\Services\Benchmark;
use Brackets\AdvancedLogger\Services\RequestLoggerService;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

use function assert;

final class ExcludedTest extends TestCase
{
    private string $benchmarkName = 'test_listener_benchmark';

    protected function setUp(): void
    {
        Benchmark::start($this->benchmarkName);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testWhenRequestPathMatchesExcludedPathJobIsNotCreated(): void
    {
        $config = Mockery::mock(Repository::class);
        assert($config instanceof Repository && $config instanceof MockInterface);
        $config->shouldReceive('get')
            ->with('advanced-logger.request.benchmark', 'application')
            ->andReturn($this->benchmarkName);
        $config->shouldReceive('get')
            ->with('advanced-logger.request.excluded-paths')
            ->andReturn(['admin/*', 'excluded']);

        $container = Mockery::mock(Container::class);
        assert($container instanceof Container && $container instanceof MockInterface);
        $container->shouldNotReceive('make');

        $busDispatcher = Mockery::mock(BusDispatcher::class);
        assert($busDispatcher instanceof BusDispatcher && $busDispatcher instanceof MockInterface);
        $busDispatcher->shouldNotReceive('dispatch');

        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('is')->with('admin/*')->andReturn(false);
        $request->shouldReceive('is')->with('excluded')->andReturn(true);

        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);

        $event = new RequestHandled($request, $response);

        $listener = new RequestLoggerListenerHandler($config, $container, $busDispatcher);
        $listener->handle($event);

        self::assertTrue(true);
    }

    public function testWhenExcludedPathsConfigIsNullNothingIsExcluded(): void
    {
        $config = Mockery::mock(Repository::class);
        assert($config instanceof Repository && $config instanceof MockInterface);
        $config->shouldReceive('get')
            ->with('advanced-logger.request.benchmark', 'application')
            ->andReturn($this->benchmarkName);
        $config->shouldReceive('get')
            ->with('advanced-logger.request.excluded-paths')
            ->andReturn(null);
        $config->shouldReceive('get')
            ->with('advanced-logger.request.queue')
            ->andReturn(null);
        $config->shouldReceive('get')
            ->with('advanced-logger.request.enabled')
            ->andReturn(false);

        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);

        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);

        $serviceRef = new ReflectionClass(RequestLoggerService::class);
        $service = $serviceRef->newInstanceWithoutConstructor();

        $configMockForService = Mockery::mock(Repository::class);
        $configMockForService->shouldReceive('get')
            ->with('advanced-logger.request.enabled')
            ->andReturn(false);

        $logger = (new ReflectionClass(RequestLogger::class))->newInstanceWithoutConstructor();
        $requestInterpolation = (new ReflectionClass(RequestInterpolation::class))->newInstanceWithoutConstructor();
        $responseInterpolation = (new ReflectionClass(ResponseInterpolation::class))->newInstanceWithoutConstructor();

        $serviceRef->getProperty('config')->setValue($service, $configMockForService);
        $serviceRef->getProperty('logger')->setValue($service, $logger);
        $serviceRef->getProperty('requestInterpolation')->setValue($service, $requestInterpolation);
        $serviceRef->getProperty('responseInterpolation')->setValue($service, $responseInterpolation);

        $jobRef = new ReflectionClass(RequestLogJob::class);
        $job = $jobRef->newInstanceWithoutConstructor();
        $jobRef->getProperty('request')->setValue($job, $request);
        $jobRef->getProperty('response')->setValue($job, $response);

        $container = Mockery::mock(Container::class);
        assert($container instanceof Container && $container instanceof MockInterface);
        $container->shouldReceive('make')
            ->once()
            ->with(RequestLogJob::class, ['request' => $request, 'response' => $response])
            ->andReturn($job);
        $container->shouldReceive('make')
            ->once()
            ->with(RequestLoggerService::class)
            ->andReturn($service);

        $busDispatcher = Mockery::mock(BusDispatcher::class);
        assert($busDispatcher instanceof BusDispatcher && $busDispatcher instanceof MockInterface);
        $busDispatcher->shouldNotReceive('dispatch');

        $event = new RequestHandled($request, $response);

        $listener = new RequestLoggerListenerHandler($config, $container, $busDispatcher);
        $listener->handle($event);

        self::assertSame(
            $request,
            (new ReflectionClass($requestInterpolation))->getProperty('request')->getValue($requestInterpolation),
        );
        self::assertSame(
            $response,
            (new ReflectionClass($responseInterpolation))->getProperty('response')->getValue($responseInterpolation),
        );
    }

    public function testWhenExcludedPathsConfigIsEmptyArrayNothingIsExcluded(): void
    {
        $config = Mockery::mock(Repository::class);
        assert($config instanceof Repository && $config instanceof MockInterface);
        $config->shouldReceive('get')
            ->with('advanced-logger.request.benchmark', 'application')
            ->andReturn($this->benchmarkName);
        $config->shouldReceive('get')
            ->with('advanced-logger.request.excluded-paths')
            ->andReturn([]);
        $config->shouldReceive('get')
            ->with('advanced-logger.request.queue')
            ->andReturn(null);

        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);

        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);

        $serviceRef = new ReflectionClass(RequestLoggerService::class);
        $service = $serviceRef->newInstanceWithoutConstructor();

        $configMockForService = Mockery::mock(Repository::class);
        $configMockForService->shouldReceive('get')
            ->with('advanced-logger.request.enabled')
            ->andReturn(false);

        $logger = (new ReflectionClass(RequestLogger::class))->newInstanceWithoutConstructor();
        $requestInterpolation = (new ReflectionClass(RequestInterpolation::class))->newInstanceWithoutConstructor();
        $responseInterpolation = (new ReflectionClass(ResponseInterpolation::class))->newInstanceWithoutConstructor();

        $serviceRef->getProperty('config')->setValue($service, $configMockForService);
        $serviceRef->getProperty('logger')->setValue($service, $logger);
        $serviceRef->getProperty('requestInterpolation')->setValue($service, $requestInterpolation);
        $serviceRef->getProperty('responseInterpolation')->setValue($service, $responseInterpolation);

        $jobRef = new ReflectionClass(RequestLogJob::class);
        $job = $jobRef->newInstanceWithoutConstructor();
        $jobRef->getProperty('request')->setValue($job, $request);
        $jobRef->getProperty('response')->setValue($job, $response);

        $container = Mockery::mock(Container::class);
        assert($container instanceof Container && $container instanceof MockInterface);
        $container->shouldReceive('make')
            ->once()
            ->with(RequestLogJob::class, ['request' => $request, 'response' => $response])
            ->andReturn($job);
        $container->shouldReceive('make')
            ->once()
            ->with(RequestLoggerService::class)
            ->andReturn($service);

        $busDispatcher = Mockery::mock(BusDispatcher::class);
        assert($busDispatcher instanceof BusDispatcher && $busDispatcher instanceof MockInterface);
        $busDispatcher->shouldNotReceive('dispatch');

        $event = new RequestHandled($request, $response);

        $listener = new RequestLoggerListenerHandler($config, $container, $busDispatcher);
        $listener->handle($event);

        self::assertSame(
            $request,
            (new ReflectionClass($requestInterpolation))->getProperty('request')->getValue($requestInterpolation),
        );
        self::assertSame(
            $response,
            (new ReflectionClass($responseInterpolation))->getProperty('response')->getValue($responseInterpolation),
        );
    }
}
