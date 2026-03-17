<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Services;

use RuntimeException;
use Throwable;

final class Benchmark
{
    /** @var array<string, array<string, string|float>> */
    private static array $timers = [];

    public static function start(string $name): float
    {
        $start = microtime(true);
        self::$timers[$name] = [
            'hash' => self::generateRandomHash(),
            'start' => $start,
        ];

        return $start;
    }

    public static function end(string $name): float
    {
        $end = microtime(true);
        if (isset(self::$timers[$name]) && isset(self::$timers[$name]['start'])) {
            if (isset(self::$timers[$name]['duration'])) {
                return self::$timers[$name]['duration'];
            }
            $start = self::$timers[$name]['start'];
            self::$timers[$name]['end'] = $end;
            self::$timers[$name]['duration'] = $end - $start;

            return self::$timers[$name]['duration'];
        }

        throw new RuntimeException(sprintf('Benchmark \'%s\' not started', $name));
    }

    public static function duration(string $name): float
    {
        return self::end($name);
    }

    public static function hash(string $name): string
    {
        if (isset(self::$timers[$name]) && isset(self::$timers[$name]['start'])) {
            return self::$timers[$name]['hash'];
        }

        throw new RuntimeException(sprintf('Benchmark \'%s\' not started', $name));
    }

    public static function generateRandomHash(): string
    {
        try {
            return substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(32))), 0, 10);
        } catch (Throwable) {
            return substr(sha1((string) time()), 0, 10);
        }
    }
}
