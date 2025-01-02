<?php

namespace Brackets\AdvancedLogger\Formatters;

use Brackets\AdvancedLogger\Services\Benchmark;
use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class LineWithHashFormatter extends LineFormatter
{
    public const KEY = 'hash';

    public function format(LogRecord $record): string
    {
        $output = parent::format($record);
        if (false !== strpos($output, '%' . self::KEY . '%')) {
            $output = str_replace(
                '%' . self::KEY . '%',
                $this->stringify($this->getRequestHash()),
                $output
            );
        }
        return $output;
    }

    protected function getRequestHash(): ?string
    {
        try {
            return Benchmark::hash(config('advanced-logger.request.benchmark', 'application'));
        } catch (Exception) {
            return null;
        }
    }
}
