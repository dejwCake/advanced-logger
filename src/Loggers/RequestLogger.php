<?php

namespace Brackets\AdvancedLogger\Loggers;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Stringable;

class RequestLogger implements LoggerInterface
{
    protected Logger $monolog;

    public function __construct()
    {
        if (version_compare(app()->version(), '5.5.99', '<=')) {
            $this->monolog = clone app('log')->getMonolog();
        } else {
            $this->monolog = app('log')->driver()->getLogger();
        }
        if (config('advanced-logger.request.enabled') && $handlers = config('advanced-logger.request.handlers')) {
            if (count($handlers)) {
                $this->monolog->popHandler();
                foreach ($handlers as $handler) {
                    if (class_exists($handler)) {
                        $this->monolog->pushHandler(app($handler));
                    } else {
                        throw new RuntimeException("Handler class [{$handler}] does not exist");
                    }
                }
            }
        }
    }

    /**
     * Log an alert message to the logs.
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->monolog->alert($message, $context);
    }

    /**
     * Log a critical message to the logs.
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->monolog->critical($message, $context);
    }

    /**
     * Log an error message to the logs.
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->monolog->error($message, $context);
    }

    /**
     * Log a warning message to the logs.
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->monolog->warning($message, $context);
    }

    /**
     * Log a notice to the logs.
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->monolog->notice($message, $context);
    }

    /**
     * Log an informational message to the logs.
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->monolog->info($message, $context);
    }

    /**
     * Log a debug message to the logs.
     */
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->monolog->debug($message, $context);
    }


    /**
     * System is unusable.
     */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->monolog->emergency($message, $context);
    }

    /**
     * Log a message to the logs.
     *
     * @param string $level
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->monolog->log($level, $message, $context);
    }

    /**
     * Register a file log handler.
     */
    public function useFiles(string $path, string $level = 'debug'): void
    {
    }

    /**
     * Register a daily file log handler.
     */
    public function useDailyFiles(string $path, int $days = 0, string $level = 'debug'): void
    {
    }
}
