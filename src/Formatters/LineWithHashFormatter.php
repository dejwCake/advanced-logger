<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Formatters;

use Brackets\AdvancedLogger\Services\Benchmark;
use Illuminate\Contracts\Config\Repository;
use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;
use Throwable;

final class LineWithHashFormatter extends LineFormatter
{
    public const KEY = 'hash';

    public function __construct(
        private readonly Repository $config,
        ?string $format = null,
        ?string $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = false,
    ) {
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    public function format(LogRecord $record): string
    {
        $output = parent::format($record);
        if (str_contains($output, '%' . self::KEY . '%')) {
            $output = str_replace(
                '%' . self::KEY . '%',
                $this->stringify($this->getRequestHash()),
                $output,
            );
        }

        return $output;
    }

    private function getRequestHash(): ?string
    {
        try {
            return Benchmark::hash($this->config->get('advanced-logger.request.benchmark', 'application'));
        } catch (Throwable) {
            return null;
        }
    }
}
