<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Feature\Listeners\RequestLoggerListenerHandler;

use Brackets\AdvancedLogger\Tests\TestCase;

final class HandleTest extends TestCase
{
    public function testRequestIsLoggedInFile(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        self::assertStringContainsString('127.0.0.1', file_get_contents($this->getRequestLogFileName()));
        self::assertStringContainsString(' GET http://localhost', file_get_contents($this->getRequestLogFileName()));
    }

    public function testExcludedPathIsNotLogged(): void
    {
        $response = $this->get('/excluded');
        $response->assertStatus(200);
        self::assertFileDoesNotExist($this->getRequestLogFileName());
    }

    public function testLogContainsResponseStatus(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        self::assertStringContainsString('200', file_get_contents($this->getRequestLogFileName()));
    }

    public function testLogContainsRequestMethod(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        self::assertStringContainsString('GET', file_get_contents($this->getRequestLogFileName()));
    }
}
