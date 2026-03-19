<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests;

use Illuminate\Log\Logger;
use Monolog\Handler\HandlerInterface;

final class TestLogger extends Logger
{
    /** @param array<HandlerInterface> $handlers */
    public function __construct(private array $handlers)
    {
    }

    /** @return array<HandlerInterface> */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}
