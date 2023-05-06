<?php

namespace Brackets\AdvancedLogger\Contracts;

/**
 * Interface InterpolationContract
 */
interface InterpolationContract
{
    public function interpolate(string $text): string;
}
