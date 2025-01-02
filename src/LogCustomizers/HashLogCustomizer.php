<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\LogCustomizers;

use Brackets\AdvancedLogger\Formatters\LineWithHashFormatter;
use Illuminate\Log\Logger;
use Monolog\Handler\FormattableHandlerInterface;

class HashLogCustomizer
{
    /**
     * Customize the given logger instance.
     */
    public function __invoke(Logger $logger): void
    {
        if (method_exists($logger, 'getHandlers')) {
            foreach ($logger->getHandlers() as $handler) {
                if ($handler instanceof FormattableHandlerInterface) {
                    $handler->setFormatter(app(
                        LineWithHashFormatter::class,
                        ['format' => "[%datetime%] %hash% %channel%.%level_name%: %message% %context% %extra%\n"],
                    ));
                }
            }
        }
    }
}
