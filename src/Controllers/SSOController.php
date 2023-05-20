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
        $httpManager = new HttpService();
        $environmentManager = new EnvironmentService();
        $sessionManager = new SessionService();
        $ssoManager = new SSOService($sessionManager, $environmentManager, $httpManager);
        $ssoManager->init();
    }
}
