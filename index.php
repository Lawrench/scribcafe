<?php

use App\Managers\SSOManager;
use App\Managers\EnvironmentManager;
use App\Managers\HttpManager;
use App\Managers\SessionManager;

session_start();

// AUTOLOAD
require_once __DIR__ . '/vendor/autoload.php';

// SSO AUTHENTICATION
$httpManager = new HttpManager();
$environmentManager = new EnvironmentManager();
$sessionManager = new SessionManager();
$ssoManager = new SSOManager($sessionManager, $environmentManager, $httpManager);
$ssoManager->init();

// START

echo "Hello World <br />" . PHP_EOL; // TODO Delete me

require_once('src/database/mysql.php'); // TODO: Remove me, testing
