<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Interpolations\BaseInterpolation;

use Brackets\AdvancedLogger\Tests\TestBaseInterpolation;
use PHPUnit\Framework\TestCase;

final class FormatSizeUnitsTest extends TestCase
{
    private TestBaseInterpolation $stub;

    protected function setUp(): void
    {
        $this->stub = new TestBaseInterpolation();
    }

    public function testZeroBytesReturns0B(): void
    {
        self::assertSame('0B', $this->stub->testFormatSizeUnits(0));
    }

    public function testOneBytesReturns1Byte(): void
    {
        self::assertSame('1 byte', $this->stub->testFormatSizeUnits(1));
    }

    public function test500BytesReturns500B(): void
    {
        self::assertSame('500B', $this->stub->testFormatSizeUnits(500));
    }

    public function test1024BytesReturns1KB(): void
    {
        self::assertSame('1.00KB', $this->stub->testFormatSizeUnits(1024));
    }

    public function test1048576BytesReturns1MB(): void
    {
        self::assertSame('1.00MB', $this->stub->testFormatSizeUnits(1048576));
    }

    public function test1073741824BytesReturns1GB(): void
    {
        self::assertSame('1.00GB', $this->stub->testFormatSizeUnits(1073741824));
    }
}
