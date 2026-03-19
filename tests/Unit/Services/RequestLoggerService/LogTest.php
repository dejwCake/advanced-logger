<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Services\RequestLoggerService;

use Brackets\AdvancedLogger\Interpolations\RequestInterpolation;
use Brackets\AdvancedLogger\Interpolations\ResponseInterpolation;
use Brackets\AdvancedLogger\Loggers\RequestLogger;
use Brackets\AdvancedLogger\Services\RequestLoggerService;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

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
        $this->requestInterpolation = (new ReflectionClass(RequestInterpolation::class))->newInstanceWithoutConstructor();
        $this->responseInterpolation = (new ReflectionClass(ResponseInterpolation::class))->newInstanceWithoutConstructor();

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
        /** @var Request&MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('method')->andReturn('GET');

        /** @var Response&MockInterface $response */
        $response = Mockery::mock(Response::class);
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

        $logCalls = [];
        $monolog = Mockery::mock(\Monolog\Logger::class);
        $monolog->shouldReceive('log')
            ->once()
            ->withArgs(static function (string $level, string $message, array $context) use (&$logCalls): bool {
                $logCalls[] = [$level, $message, $context];
                return true;
            });

        (new ReflectionClass($this->logger))->getProperty('monolog')->setValue($this->logger, $monolog);

        $this->service->log($request, $response);

        self::assertCount(1, $logCalls);
        self::assertSame('info', $logCalls[0][0]);
        self::assertSame('GET 200', $logCalls[0][1]);
        self::assertSame(['RESPONSE'], $logCalls[0][2]);
    }

    public function testWhenDisabledDoesNotCallLogger(): void
    {
        /** @var Request&MockInterface $request */
        $request = Mockery::mock(Request::class);

        /** @var Response&MockInterface $response */
        $response = Mockery::mock(Response::class);

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.enabled')
            ->andReturn(false);

        $monolog = Mockery::mock(\Monolog\Logger::class);
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
        /** @var Request&MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('method')->andReturn('DELETE');

        /** @var Response&MockInterface $response */
        $response = Mockery::mock(Response::class);
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

        $logCalls = [];
        $monolog = Mockery::mock(\Monolog\Logger::class);
        $monolog->shouldReceive('log')
            ->once()
            ->withArgs(static function (string $level, string $message, array $context) use (&$logCalls): bool {
                $logCalls[] = [$level, $message, $context];
                return true;
            });

        (new ReflectionClass($this->logger))->getProperty('monolog')->setValue($this->logger, $monolog);

        $this->service->log($request, $response);

        self::assertCount(1, $logCalls);
        self::assertSame('warning', $logCalls[0][0]);
        self::assertSame('DELETE 404', $logCalls[0][1]);
    }

    public function testFallsBackToPredefinedFormatName(): void
    {
        /** @var Request&MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('method')->andReturn('GET');
        $request->shouldReceive('url')->andReturn('http://localhost/test');

        /** @var Response&MockInterface $response */
        $response = Mockery::mock(Response::class);
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

        $logCalls = [];
        $monolog = Mockery::mock(\Monolog\Logger::class);
        $monolog->shouldReceive('log')
            ->once()
            ->withArgs(static function (string $level, string $message, array $context) use (&$logCalls): bool {
                $logCalls[] = [$level, $message, $context];
                return true;
            });

        (new ReflectionClass($this->logger))->getProperty('monolog')->setValue($this->logger, $monolog);

        $tempDir = sys_get_temp_dir();
        $app = Mockery::mock(\Illuminate\Contracts\Foundation\Application::class);
        $app->shouldReceive('storagePath')->andReturn($tempDir);

        $responseInterpolationRef = new ReflectionClass(ResponseInterpolation::class);
        $responseInterpolationRef->getProperty('config')->setValue($this->responseInterpolation, $this->config);
        $responseInterpolationRef->getProperty('app')->setValue($this->responseInterpolation, $app);

        $this->service->log($request, $response);

        self::assertCount(1, $logCalls);
        self::assertSame('info', $logCalls[0][0]);
        self::assertStringContainsString('GET', $logCalls[0][1]);
        self::assertStringContainsString('200', $logCalls[0][1]);
    }
}
