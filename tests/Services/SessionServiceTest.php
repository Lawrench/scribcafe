<?php

namespace Test\Services;

use PHPUnit\Framework\TestCase;
use App\Services\SessionService;

class SessionServiceTest extends TestCase
{
    protected SessionService $sessionService;

    public function setUp(): void
    {
        $this->sessionService = $this->getMockBuilder(SessionService::class)
            ->onlyMethods(['getSession'])
            ->getMock();
    }

    public function testGet()
    {
        $this->sessionService->method('getSession')
            ->willReturn([
                'testKey' => 'testValue',
            ]);

        $this->assertEquals('testValue', $this->sessionService->get('testKey'));
        $this->assertNull($this->sessionService->get('nonExistentKey'));
    }

    public function testSet()
    {
        $key = 'testKey';
        $value = 'testValue';

        $sessionService = $this->getMockBuilder(SessionService::class)
            ->onlyMethods(['set'])
            ->getMock();

        $sessionService->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo($key),
                $this->equalTo($value)
            );

        $sessionService->set($key, $value);
    }

}
