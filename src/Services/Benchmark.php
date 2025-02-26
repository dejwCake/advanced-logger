<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Services;

use RuntimeException;
use Throwable;

class Benchmark
{
    /** @var array<string, array<string, string|float>> */
    protected static array $timers = [];

    public static function start(string $name): string|float
    {
        $start = microtime(true);
        static::$timers[$name] = [
            'hash' => self::generateRandomHash(),
            'start' => $start,
        ];

        return $start;
    }

    public static function end(string $name): float
    {
        $end = microtime(true);
        if (isset(static::$timers[$name]) && isset(static::$timers[$name]['start'])) {
            if (isset(static::$timers[$name]['duration'])) {
                return static::$timers[$name]['duration'];
            }
            $start = static::$timers[$name]['start'];
            static::$timers[$name]['end'] = $end;
            static::$timers[$name]['duration'] = $end - $start;

            return static::$timers[$name]['duration'];
        }

        throw new RuntimeException("Benchmark '{$name}' not started");
    }

    public static function duration(string $name): float
    {
        return static::end($name);
    }

    public static function hash(string $name): string
    {
        if (isset(static::$timers[$name]) && isset(static::$timers[$name]['start'])) {
            return static::$timers[$name]['hash'];
        }

        throw new RuntimeException("Benchmark '{$name}' not started");
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
