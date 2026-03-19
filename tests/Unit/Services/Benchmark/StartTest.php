<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Services\Benchmark;

use Brackets\AdvancedLogger\Services\Benchmark;
use PHPUnit\Framework\TestCase;

final class StartTest extends TestCase
{
    public function testReturnsFloat(): void
    {
        $before = microtime(true);
        $result = Benchmark::start('start_returns_float');
        $after = microtime(true);

        self::assertIsFloat($result);
        self::assertGreaterThanOrEqual($before, $result);
        self::assertLessThanOrEqual($after, $result);
    }

    public function testHashReturns10CharStringAfterStart(): void
    {
        Benchmark::start('start_hash_test');
        $hash = Benchmark::hash('start_hash_test');

        self::assertIsString($hash);
        self::assertSame(10, strlen($hash));
    }

    public function testEndReturnsFloatDurationAfterStart(): void
    {
        Benchmark::start('start_end_test');
        $duration = Benchmark::end('start_end_test');

        self::assertIsFloat($duration);
        self::assertGreaterThanOrEqual(0.0, $duration);
    }
}
