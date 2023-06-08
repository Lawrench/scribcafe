<?php

namespace Test\Services;

use App\Services\SSOService;
use App\Services\SessionService;
use App\Services\EnvironmentService;
use App\Services\HttpService;
use App\Services\RequestService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Exception;

class SSOServiceTest extends TestCase
{
    private SSOService $ssoService;
    private SessionService $sessionService;
    private EnvironmentService $environmentService;
    private HttpService $httpService;
    private RequestService $requestService;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->sessionService = $this->createMock(SessionService::class);
        $this->environmentService = $this->createMock(EnvironmentService::class);
        $this->httpService = $this->createMock(HttpService::class);
        $this->requestService = $this->createMock(RequestService::class);

        $this->ssoService = new SSOService(
            $this->sessionService,
            $this->environmentService,
            $this->httpService,
            $this->requestService
        );
    }

    public function testHeaderCalled()
    {
        $sso = 'bm9uY2U9dmFsaWRfbm9uY2U=';
        $sig = 'valid_sig';

        $this->sessionService->method('get')
            ->will(
                $this->returnValueMap([
                    ['login', false],
                    ['nonce', 'valid_nonce'],
                ])
            );

        $this->httpService->method('getRequestParam')
            ->willReturnMap([
                ['sso', '', $sso],
                ['sig', '', $sig],
            ]);

        $this->environmentService->method('get')
            ->will(
                $this->returnValueMap([
                    ['DISCOURSE_SSO_SECRET', 'valid_secret'],
                    ['DISCOURSE_URL', 'https://test.com'],
                ])
            );

        $sso_data = base64_encode(
            http_build_query([
                'nonce' => 'valid_nonce',
            ])
        );

        $this->assertEquals($sso, $sso_data);
        $this->httpService->expects($this->once())
            ->method('setHeader')
            ->with($this->stringContains('Access-Control-Allow-Origin:'));

        $this->ssoService->login('http://localhost');
    }

    public function testInitWhenIsResponseReturnsFalse(): void
    {
        $errorCode = 400;

        $this->requestService->method('get')->will(
            $this->returnValueMap([
                ['REQUEST_SCHEME', 'https', 'https'],
                ['HTTP_HOST', null, 'test.com'],
                ['SCRIPT_NAME', null, ''],
            ])
        );

        $this->sessionService->method('get')->will(
            $this->returnValueMap([
                ['login', null],  // user is not logged in
                ['nonce', null],  // user is not logged in
            ])
        );

        $this->httpService->method('isResponse')->willReturn(true);
        $this->httpService->expects($this->atLeastOnce())->method('sendError')->with($errorCode);

        $this->ssoService->init();
    }

    public function testInitWhenIsResponseReturnsTrue(): void
    {
        $currentLocation = 'https://test.com';

        $this->requestService->method('get')->will(
            $this->returnValueMap([
                ['REQUEST_SCHEME', 'https', 'https'],
                ['HTTP_HOST', null, 'test.com'],
                ['SCRIPT_NAME', null, ''],
            ])
        );

        $this->sessionService->method('get')->will(
            $this->returnValueMap([
                ['login', true],  // user is logged in
                ['nonce', null],  // user is not logged in
            ])
        );

        $this->httpService->method('isResponse')->willReturn(true);
        $this->httpService->expects($this->once())->method('redirectTo')->with($currentLocation);

        $this->ssoService->init();
    }
}
