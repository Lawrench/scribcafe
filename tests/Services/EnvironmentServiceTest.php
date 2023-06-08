<?php

namespace Test\Services;

use App\Services\EnvironmentService;
use PHPUnit\Framework\TestCase;

class EnvironmentServiceTest extends TestCase
{
    protected EnvironmentService $environmentService;

    public function setUp(): void
    {
        $this->environmentService = new EnvironmentService();
    }

    public function testGetEnvironmentVariableWhichDoesNotExist()
    {
        // Assert that a non-existing variable returns null
        $result = $this->environmentService->get('NON_EXISTING_VARIABLE');
        $this->assertNull($result);
    }

    public function testGetEnvironmentVariableWhichExists()
    {
        // Set a test environment variable
        $_ENV['TEST_VARIABLE'] = 'Test Value';

        $result = $this->environmentService->get('TEST_VARIABLE');

        // Assert that the test variable exists and returns correct value
        $this->assertEquals('Test Value', $result);
    }
}
