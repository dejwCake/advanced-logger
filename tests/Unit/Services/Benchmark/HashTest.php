<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Services\Benchmark;

use Brackets\AdvancedLogger\Services\Benchmark;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HashTest extends TestCase
{
    public function testReturnsStringHashAfterStart(): void
    {
        Benchmark::start('hash_after_start_test');
        $hash = Benchmark::hash('hash_after_start_test');

        self::assertIsString($hash);
        self::assertSame(10, strlen($hash));
    }

    public function testThrowsRuntimeExceptionIfNeverStarted(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Benchmark 'hash_never_started_test' not started");

        Benchmark::hash('hash_never_started_test');
    }
}
