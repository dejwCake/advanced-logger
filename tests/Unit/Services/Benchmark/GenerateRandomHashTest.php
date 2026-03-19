<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Services\Benchmark;

use Brackets\AdvancedLogger\Services\Benchmark;
use PHPUnit\Framework\TestCase;

final class GenerateRandomHashTest extends TestCase
{
    public function testReturns10CharacterString(): void
    {
        $hash = Benchmark::generateRandomHash();

        self::assertIsString($hash);
        self::assertSame(10, strlen($hash));
    }

    public function testTwoCallsReturnDifferentValues(): void
    {
        $first = Benchmark::generateRandomHash();
        $second = Benchmark::generateRandomHash();

        self::assertNotSame($first, $second);
    }
}
