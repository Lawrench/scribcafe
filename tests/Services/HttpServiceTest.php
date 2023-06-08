<?php

namespace Test\Services;

use App\Services\HttpService;
use PHPUnit\Framework\TestCase;

class HttpServiceTest extends TestCase
{
    protected HttpService $httpService;

    public function testGetRequestParamExisting()
    {
        $_GET['param'] = 'value';
        $result = $this->httpService->getRequestParam('param', 'default');
        $this->assertEquals('value', $result);
    }

    public function testGetRequestParamNonExisting()
    {
        $result = $this->httpService->getRequestParam('param', 'default');
        $this->assertEquals('default', $result);
    }

    public function testIsResponse()
    {
        $_GET['sso'] = 'value';
        $result = $this->httpService->isResponse();
        $this->assertTrue($result);
    }

    protected function setUp(): void
    {
        $this->httpService = new HttpService();
        $_GET = [];
    }
}
