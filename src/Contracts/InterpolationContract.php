<?php

namespace Brackets\AdvancedLogger\Contracts;

interface InterpolationContract
{
    public function interpolate(string $text): string;
}
