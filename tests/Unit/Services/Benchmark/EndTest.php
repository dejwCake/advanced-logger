<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Services\Benchmark;

use Brackets\AdvancedLogger\Services\Benchmark;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EndTest extends TestCase
{
    public function testReturnsFloatDurationAfterStart(): void
    {
        Benchmark::start('end_duration_test');
        $duration = Benchmark::end('end_duration_test');

        self::assertIsFloat($duration);
        self::assertGreaterThanOrEqual(0.0, $duration);
    }

    public function testReturnsSameDurationOnSecondCall(): void
    {
        Benchmark::start('end_cached_test');
        $first = Benchmark::end('end_cached_test');
        $second = Benchmark::end('end_cached_test');

        self::assertSame($first, $second);
    }

    public function testThrowsRuntimeExceptionIfNeverStarted(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs("Benchmark 'end_never_started_test' not started");

        Benchmark::end('end_never_started_test');
    }
}
