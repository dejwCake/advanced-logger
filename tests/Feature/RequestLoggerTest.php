<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Tests\Feature;

use Brackets\AdvancedLogger\Tests\TestCase;

class RequestLoggerTest extends TestCase
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
        //We are deleting request file, so there should not be a files
    }
}
