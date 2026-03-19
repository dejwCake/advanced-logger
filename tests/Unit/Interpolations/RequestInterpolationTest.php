<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Interpolations;

use Brackets\AdvancedLogger\Interpolations\RequestInterpolation;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

use function assert;

final class RequestInterpolationTest extends TestCase
{
    private RequestInterpolation $interpolation;

    protected function setUp(): void
    {
        $this->interpolation = new RequestInterpolation();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testReturnsTextUnchangedWhenRequestIsNull(): void
    {
        $result = $this->interpolation->interpolate('{method} {url}');

        self::assertSame('{method} {url}', $result);
    }

    public function testResolvesMethodFromRequest(): void
    {
        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('method')->once()->andReturn('GET');

        $this->interpolation->setRequest($request);

        $result = $this->interpolation->interpolate('{method}');

        self::assertSame('GET', $result);
    }

    public function testResolvesPostMethodFromRequest(): void
    {
        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('method')->once()->andReturn('POST');

        $this->interpolation->setRequest($request);

        $result = $this->interpolation->interpolate('{method}');

        self::assertSame('POST', $result);
    }

    public function testResolvesUrlFromRequest(): void
    {
        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('url')->once()->andReturn('https://example.com/test');

        $this->interpolation->setRequest($request);

        $result = $this->interpolation->interpolate('{url}');

        self::assertSame('https://example.com/test', $result);
    }

    public function testResolvesIpFromRemoteAddr(): void
    {
        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('ip')->once()->andReturn('127.0.0.1');

        $this->interpolation->setRequest($request);

        $result = $this->interpolation->interpolate('{remote-addr}');

        self::assertSame('127.0.0.1', $result);
    }

    public function testResolvesUserAgentFromServerVars(): void
    {
        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('server')->with('HTTP_USER_AGENT')->andReturn('TestBrowser/1.0');

        $this->interpolation->setRequest($request);

        $result = $this->interpolation->interpolate('{user-agent}');

        self::assertSame('TestBrowser/1.0', $result);
    }

    public function testReturnsRawVariableWhenUnresolvable(): void
    {
        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('server')->andReturn(null);

        $this->interpolation->setRequest($request);

        $result = $this->interpolation->interpolate('{unresolvable-var}');

        self::assertSame('{unresolvable-var}', $result);
    }

    public function testResolvesDateWithClfFormat(): void
    {
        $request = Mockery::mock(Request::class);
        assert($request instanceof Request && $request instanceof MockInterface);
        $request->shouldReceive('server')->andReturn(null);

        $this->interpolation->setRequest($request);

        $result = $this->interpolation->interpolate('{date[clf]}');

        self::assertMatchesRegularExpression('/\d{2}\/\w{3}\/\d{4}:\d{2}:\d{2}:\d{2}\\\\s[+-]\d{4}/', $result);
    }
}
