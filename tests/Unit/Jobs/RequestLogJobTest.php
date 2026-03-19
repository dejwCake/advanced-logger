<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Jobs;

use Brackets\AdvancedLogger\Interpolations\RequestInterpolation;
use Brackets\AdvancedLogger\Interpolations\ResponseInterpolation;
use Brackets\AdvancedLogger\Jobs\RequestLogJob;
use Brackets\AdvancedLogger\Loggers\RequestLogger;
use Brackets\AdvancedLogger\Services\RequestLoggerService;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

use function assert;

final class RequestLogJobTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testHandleCallsRequestLoggerServiceLogOnce(): void
    {
        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);

        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);

        $config = Mockery::mock(Repository::class);
        assert($config instanceof Repository && $config instanceof MockInterface);
        $config->shouldReceive('get')
            ->with('advanced-logger.request.enabled')
            ->once()
            ->andReturn(false);

        $requestInterpolation = (new ReflectionClass(RequestInterpolation::class))->newInstanceWithoutConstructor();
        $responseInterpolation = (new ReflectionClass(ResponseInterpolation::class))->newInstanceWithoutConstructor();
        $logger = (new ReflectionClass(RequestLogger::class))->newInstanceWithoutConstructor();

        $service = (new ReflectionClass(RequestLoggerService::class))->newInstanceWithoutConstructor();

        $serviceRef = new ReflectionClass($service);
        $serviceRef->getProperty('config')->setValue($service, $config);
        $serviceRef->getProperty('logger')->setValue($service, $logger);
        $serviceRef->getProperty('requestInterpolation')->setValue($service, $requestInterpolation);
        $serviceRef->getProperty('responseInterpolation')->setValue($service, $responseInterpolation);

        $job = new RequestLogJob($request, $response);
        $job->handle($service);

        $requestProp = (new ReflectionClass($requestInterpolation))->getProperty('request');
        self::assertSame($request, $requestProp->getValue($requestInterpolation));

        $responseProp = (new ReflectionClass($responseInterpolation))->getProperty('response');
        self::assertSame($response, $responseProp->getValue($responseInterpolation));
    }
}
