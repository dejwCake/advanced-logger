<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Formatters;

use Brackets\AdvancedLogger\Services\Benchmark;
use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class LineWithHashFormatter extends LineFormatter
{
    public const KEY = 'hash';

    public function format(LogRecord $record): string
    {
        $output = parent::format($record);
        if (strpos($output, '%' . self::KEY . '%') !== false) {
            $output = str_replace(
                '%' . self::KEY . '%',
                $this->stringify($this->getRequestHash()),
                $output,
            );
        }

        return $output;
    }

    protected function getRequestHash(): ?string
    {
        try {
            return Benchmark::hash(config('advanced-logger.request.benchmark', 'application'));
        } catch (\Throwable) {
            return null;
        }
    }
}
