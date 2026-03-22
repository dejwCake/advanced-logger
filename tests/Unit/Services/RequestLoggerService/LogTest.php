<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Services\RequestLoggerService;

use Brackets\AdvancedLogger\Interpolations\RequestInterpolation;
use Brackets\AdvancedLogger\Interpolations\ResponseInterpolation;
use Brackets\AdvancedLogger\Loggers\RequestLogger;
use Brackets\AdvancedLogger\Services\RequestLoggerService;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

use function assert;

final class LogTest extends TestCase
{
    /** @var Repository&MockInterface */
    private Repository $config;

    private RequestLogger $logger;
    private RequestInterpolation $requestInterpolation;
    private ResponseInterpolation $responseInterpolation;
    private RequestLoggerService $service;

    protected function setUp(): void
    {
        $this->config = Mockery::mock(Repository::class);

        $this->logger = (new ReflectionClass(RequestLogger::class))->newInstanceWithoutConstructor();
        $this->requestInterpolation = (new ReflectionClass(
            RequestInterpolation::class,
        ))->newInstanceWithoutConstructor();
        $this->responseInterpolation = (new ReflectionClass(
            ResponseInterpolation::class,
        ))->newInstanceWithoutConstructor();

        $serviceRef = new ReflectionClass(RequestLoggerService::class);
        $this->service = $serviceRef->newInstanceWithoutConstructor();
        $serviceRef->getProperty('config')->setValue($this->service, $this->config);
        $serviceRef->getProperty('logger')->setValue($this->service, $this->logger);
        $serviceRef->getProperty('requestInterpolation')->setValue($this->service, $this->requestInterpolation);
        $serviceRef->getProperty('responseInterpolation')->setValue($this->service, $this->responseInterpolation);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testWhenEnabledCallsSetRequestSetResponseInterpolatesAndCallsLogger(): void
    {
        $this->expectNotToPerformAssertions();

        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('method')->andReturn('GET');

        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.enabled')
            ->andReturn(true);

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.format', 'full')
            ->andReturn('{method} {status}');

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.level', 'info')
            ->andReturn('info');

        $monolog = Mockery::mock(Logger::class);
        $monolog->shouldReceive('log')
            ->once()
            ->with('info', 'GET 200', ['RESPONSE']);

        (new ReflectionClass($this->logger))->getProperty('monolog')->setValue($this->logger, $monolog);

        $this->service->log($request, $response);
    }

    public function testWhenDisabledDoesNotCallLogger(): void
    {
        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);

        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.enabled')
            ->andReturn(false);

        $monolog = Mockery::mock(Logger::class);
        $monolog->shouldNotReceive('log');

        (new ReflectionClass($this->logger))->getProperty('monolog')->setValue($this->logger, $monolog);

        $this->service->log($request, $response);

        $requestProp = (new ReflectionClass($this->requestInterpolation))->getProperty('request');
        self::assertSame($request, $requestProp->getValue($this->requestInterpolation));

        $responseProp = (new ReflectionClass($this->responseInterpolation))->getProperty('response');
        self::assertSame($response, $responseProp->getValue($this->responseInterpolation));
    }

    public function testUsesCustomFormatStringFromConfig(): void
    {
        $this->expectNotToPerformAssertions();

        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('method')->andReturn('DELETE');

        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);
        $response->shouldReceive('getStatusCode')->andReturn(404);

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.enabled')
            ->andReturn(true);

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.format', 'full')
            ->andReturn('{method} {status}');

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.level', 'info')
            ->andReturn('warning');

        $monolog = Mockery::mock(Logger::class);
        $monolog->shouldReceive('log')
            ->once()
            ->with('warning', 'DELETE 404', ['RESPONSE']);

        (new ReflectionClass($this->logger))->getProperty('monolog')->setValue($this->logger, $monolog);

        $this->service->log($request, $response);
    }

    public function testFallsBackToPredefinedFormatName(): void
    {
        $this->expectNotToPerformAssertions();

        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('method')->andReturn('GET');
        $request->shouldReceive('url')->andReturn('http://localhost/test');

        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getContent')->andReturn('hello');

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.enabled')
            ->andReturn(true);

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.format', 'full')
            ->andReturn('tiny');

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.level', 'info')
            ->andReturn('info');

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.benchmark', 'application')
            ->andReturn('benchmark_fallback_test');

        $monolog = Mockery::mock(Logger::class);
        $monolog->shouldReceive('log')
            ->once()
            ->withArgs(static fn (string $level, string $message): bool => $level === 'info'
                && str_contains($message, 'GET')
                && str_contains($message, '200'));

        (new ReflectionClass($this->logger))->getProperty('monolog')->setValue($this->logger, $monolog);

        $tempDir = sys_get_temp_dir();
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('storagePath')->andReturn($tempDir);

        $responseInterpolationRef = new ReflectionClass(ResponseInterpolation::class);
        $responseInterpolationRef->getProperty('config')->setValue($this->responseInterpolation, $this->config);
        $responseInterpolationRef->getProperty('app')->setValue($this->responseInterpolation, $app);

        $this->service->log($request, $response);
    }
}
