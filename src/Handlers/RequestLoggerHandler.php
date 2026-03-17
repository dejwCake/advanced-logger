<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Handlers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;

final class RequestLoggerHandler extends RotatingFileHandler
{
    public function __construct(
        Repository $config,
        Application $app,
        ?string $filename = null,
        int $maxFiles = 0,
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
        ?int $filePermission = null,
        bool $useLocking = false,
    ) {
        $filename ??= $config->get(
            'advanced-logger.request.file',
            $app->storagePath('logs/request.log'),
        );

        parent::__construct($filename, $maxFiles, $level, $bubble, $filePermission, $useLocking);
    }
}
