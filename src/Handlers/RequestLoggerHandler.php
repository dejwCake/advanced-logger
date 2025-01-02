<?php

namespace Brackets\AdvancedLogger\Handlers;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;

class RequestLoggerHandler extends RotatingFileHandler
{
    public function __construct(
        ?string $filename = null,
        int $maxFiles = 0,
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
        ?int $filePermission = null,
        bool $useLocking = false,
    ) {
        $filename = !is_null($filename) ? $filename : config(
            'advanced-logger.request.file',
            storage_path('logs/request.log')
        );
        parent::__construct($filename, $maxFiles, $level, $bubble, $filePermission, $useLocking);
    }
}
