<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Unit\Formatters;

use Brackets\AdvancedLogger\Formatters\LineWithHashFormatter;
use Brackets\AdvancedLogger\Services\Benchmark;
use DateTimeImmutable;
use Illuminate\Contracts\Config\Repository;
use Mockery;
use Mockery\MockInterface;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

final class LineWithHashFormatterTest extends TestCase
{
    /** @var Repository&MockInterface */
    private Repository $config;

    protected function setUp(): void
    {
        $this->config = Mockery::mock(Repository::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testReplacesHashPlaceholderWhenPresent(): void
    {
        $benchmarkName = 'test_hash_formatter_with_hash';
        Benchmark::start($benchmarkName);
        $expectedHash = Benchmark::hash($benchmarkName);

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.benchmark', 'application')
            ->andReturn($benchmarkName);

        $formatter = new LineWithHashFormatter($this->config, "%hash% %message%\n");
        $record = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'test message',
        );

        $output = $formatter->format($record);

        self::assertStringContainsString($expectedHash, $output);
        self::assertStringNotContainsString('%hash%', $output);
    }

    public function testLeavesOutputUnchangedWhenNoHashPlaceholder(): void
    {
        $formatter = new LineWithHashFormatter($this->config, "%message%\n");
        $record = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'test message',
        );

        $output = $formatter->format($record);

        self::assertStringContainsString('test message', $output);
        self::assertStringNotContainsString('%hash%', $output);
    }

    public function testReturnsNullHashGracefullyWhenBenchmarkNotStarted(): void
    {
        $benchmarkName = 'benchmark_never_started_xyz';

        $this->config->shouldReceive('get')
            ->with('advanced-logger.request.benchmark', 'application')
            ->andReturn($benchmarkName);

        $formatter = new LineWithHashFormatter($this->config, "%hash% %message%\n");
        $record = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'test message',
        );

        $output = $formatter->format($record);

        self::assertStringNotContainsString('%hash%', $output);
        self::assertStringContainsString('test message', $output);
    }
}
