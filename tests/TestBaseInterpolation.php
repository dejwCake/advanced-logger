<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests;

use Brackets\AdvancedLogger\Interpolations\BaseInterpolation;

final class TestBaseInterpolation extends BaseInterpolation
{
    public function interpolate(string $text): string
    {
        return $text;
    }

    public function testEscape(string $text): string
    {
        return $this->escape($text);
    }

    public function testConvertToString(array|string|int|null $value): string
    {
        return $this->convertToString($value);
    }

    public function testFormatSizeUnits(int $bytes): string
    {
        return $this->formatSizeUnits($bytes);
    }
}
