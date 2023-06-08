<?php

use App\Services\HttpService;
use App\Services\EnvironmentService;
use App\Services\SessionService;
use App\Services\SSOService;
use App\Services\RequestService;

session_start();

// AUTOLOAD
require_once __DIR__ . '/vendor/autoload.php';

// SSO AUTHENTICATION
$httpService = new HttpService();
$environmentService = new EnvironmentService();
$sessionService = new SessionService();
$requestService = new RequestService();

$SSOService = new SSOService(
    $sessionService,
    $environmentService,
    $httpService,
    $requestService
);

$SSOService->init();

// START

echo "Hello World <br />" . PHP_EOL; // TODO Delete me

require_once('src/database/mysql.php'); // TODO: Remove me, testing
