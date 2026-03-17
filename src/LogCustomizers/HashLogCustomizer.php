<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\LogCustomizers;

use Brackets\AdvancedLogger\Formatters\LineWithHashFormatter;
use Illuminate\Contracts\Container\Container;
use Illuminate\Log\Logger;
use Monolog\Handler\FormattableHandlerInterface;

final class HashLogCustomizer
{
    public function __construct(private readonly Container $container,)
    {
    }

    /**
     * Customize the given logger instance.
     */
    public function __invoke(Logger $logger): void
    {
        if (method_exists($logger, 'getHandlers')) {
            foreach ($logger->getHandlers() as $handler) {
                if ($handler instanceof FormattableHandlerInterface) {
                    $handler->setFormatter($this->container->make(
                        LineWithHashFormatter::class,
                        ['format' => "[%datetime%] %hash% %channel%.%level_name%: %message% %context% %extra%\n"],
                    ));
                }
            }
        }
    }
}
