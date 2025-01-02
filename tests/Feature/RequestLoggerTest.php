<?php

namespace Brackets\AdvancedLogger\Test\Feature;

use Brackets\AdvancedLogger\Test\TestCase;

class RequestLoggerTest extends TestCase
{

    public function testRequestIsLoggedInFile(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $this->assertStringContainsString('127.0.0.1', file_get_contents($this->getRequestLogFileName()));
        $this->assertStringContainsString(' GET http://localhost', file_get_contents($this->getRequestLogFileName()));
    }

    public function testExcludedPathIsNotLogged(): void
    {
        $response = $this->get('/excluded');
        $response->assertStatus(200);
        $this->assertFileDoesNotExist($this->getRequestLogFileName());
        //We are deleting request file, so there should not be a files
    }
}
