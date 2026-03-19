<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Loggers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Log\LogManager;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Stringable;

final class RequestLogger implements LoggerInterface
{
    private readonly Logger $monolog;

    public function __construct(Repository $config, Container $container, LogManager $logManager,)
    {
        $this->monolog = $logManager->driver()->getLogger();
        $handlers = $config->get('advanced-logger.request.handlers');
        if ($config->get('advanced-logger.request.enabled') && $handlers) {
            $this->monolog->popHandler();
            foreach ($handlers as $handler) {
                if (class_exists($handler)) {
                    $this->monolog->pushHandler($container->make($handler));
                } else {
                    throw new RuntimeException(sprintf('Handler class [%s] does not exist', $handler));
                }
            }
        }
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->monolog->alert($message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->monolog->critical($message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->monolog->error($message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->monolog->warning($message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->monolog->notice($message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->monolog->info($message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->monolog->debug($message, $context);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->monolog->emergency($message, $context);
    }

    /**
     * Log a message to the logs.
     *
     * @param string $level
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->monolog->log($level, $message, $context);
    }
}
