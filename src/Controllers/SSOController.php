<?php

namespace App\Controllers;

use App\Services\SSOService;
use App\Services\EnvironmentService;
use App\Services\HttpService;
use App\Services\SessionService;

class SSOController
{
    public function init(): void
    {
        $httpService = new HttpService();
        $environmentService = new EnvironmentService();
        $sessionService = new SessionService();
        $SSOService = new SSOService($sessionService, $environmentService, $httpService);
        $SSOService->init();
    }
}
