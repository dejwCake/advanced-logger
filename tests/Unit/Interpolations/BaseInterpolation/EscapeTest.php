<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Interpolations\BaseInterpolation;

use Brackets\AdvancedLogger\Tests\TestBaseInterpolation;
use PHPUnit\Framework\TestCase;

final class EscapeTest extends TestCase
{
    private TestBaseInterpolation $stub;

    protected function setUp(): void
    {
        $this->stub = new TestBaseInterpolation();
    }

    public function testReplacesSpaceWithBackslashS(): void
    {
        self::assertSame('hello\\sworld', $this->stub->testEscape('hello world'));
    }

    public function testReplacesTabWithBackslashS(): void
    {
        self::assertSame('hello\\sworld', $this->stub->testEscape("hello\tworld"));
    }

    public function testReplacesNewlineWithBackslashS(): void
    {
        self::assertSame('hello\\sworld', $this->stub->testEscape("hello\nworld"));
    }

    public function testLeavesNonWhitespaceUnchanged(): void
    {
        self::assertSame('helloworld123', $this->stub->testEscape('helloworld123'));
    }

    public function testLeavesEmptyStringUnchanged(): void
    {
        self::assertSame('', $this->stub->testEscape(''));
    }
}
