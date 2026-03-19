<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Interpolations\BaseInterpolation;

use Brackets\AdvancedLogger\Tests\TestBaseInterpolation;
use PHPUnit\Framework\TestCase;

final class ConvertToStringTest extends TestCase
{
    private TestBaseInterpolation $stub;

    protected function setUp(): void
    {
        $this->stub = new TestBaseInterpolation();
    }

    public function testArrayIsConvertedToJsonString(): void
    {
        self::assertSame('{"foo":"bar"}', $this->stub->testConvertToString(['foo' => 'bar']));
    }

    public function testStringIsReturnedUnchanged(): void
    {
        self::assertSame('hello', $this->stub->testConvertToString('hello'));
    }

    public function testIntIsConvertedToString(): void
    {
        self::assertSame('42', $this->stub->testConvertToString(42));
    }

    public function testNullIsConvertedToLiteralNullString(): void
    {
        self::assertSame('null', $this->stub->testConvertToString(null));
    }
}
