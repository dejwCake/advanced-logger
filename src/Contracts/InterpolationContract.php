<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Contracts;

interface InterpolationContract
{
    public function interpolate(string $text): string;
}
