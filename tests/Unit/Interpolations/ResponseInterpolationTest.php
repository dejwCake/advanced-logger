<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Interpolations;

use Brackets\AdvancedLogger\Interpolations\ResponseInterpolation;
use Brackets\AdvancedLogger\Services\Benchmark;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

use function assert;

final class ResponseInterpolationTest extends TestCase
{
    private ResponseInterpolation $interpolation;

    /** @var Repository&MockInterface */
    private Repository $config;

    /** @var Application&MockInterface */
    private Application $app;

    protected function setUp(): void
    {
        $this->config = Mockery::mock(Repository::class);
        $this->app = Mockery::mock(Application::class);
        $this->interpolation = new ResponseInterpolation($this->config, $this->app);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testReturnsTextUnchangedWhenResponseIsNull(): void
    {
        $result = $this->interpolation->interpolate('{status} {http-version}');

        self::assertSame('{status} {http-version}', $result);
    }

    public function testResolvesStatusFromResponse(): void
    {
        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);
        $response->shouldReceive('getStatusCode')->once()->andReturn(200);

        $this->interpolation->setResponse($response);

        $result = $this->interpolation->interpolate('{status}');

        self::assertSame('200', $result);
    }

    public function testResolvesHttpVersionFromResponse(): void
    {
        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);
        $response->shouldReceive('getProtocolVersion')->once()->andReturn('1.1');

        $this->interpolation->setResponse($response);

        $result = $this->interpolation->interpolate('{http-version}');

        self::assertSame('1.1', $result);
    }

    public function testResolvesResponseTime(): void
    {
        $benchmarkName = 'test_response_time';
        Benchmark::start($benchmarkName);

        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);
        $this->interpolation->setResponse($response);

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.benchmark', 'application')
            ->andReturn($benchmarkName);

        $result = $this->interpolation->interpolate('{response-time}');

        self::assertIsNumeric($result);
        self::assertGreaterThan(0.0, (float) $result);
    }

    public function testResolvesRequestHash(): void
    {
        $benchmarkName = 'test_request_hash';
        Benchmark::start($benchmarkName);

        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);
        $this->interpolation->setResponse($response);

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.benchmark', 'application')
            ->andReturn($benchmarkName);

        $result = $this->interpolation->interpolate('{request-hash}');

        self::assertSame(Benchmark::hash($benchmarkName), $result);
    }

    public function testReturnsRawVariableWhenUnresolvable(): void
    {
        $response = Mockery::mock(Response::class);
        assert($response instanceof Response && $response instanceof MockInterface);
        $this->interpolation->setResponse($response);

        $result = $this->interpolation->interpolate('{unresolvable-var}');

        self::assertSame('{unresolvable-var}', $result);
    }
}
