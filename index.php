<?php

use App\Controllers\SSOController;

session_start();

// AUTOLOAD
require_once __DIR__ . '/vendor/autoload.php';

// SSO AUTHENTICATION
$ssoController = new SSOController();
$ssoController->init();

// START

echo "Hello World <br />" . PHP_EOL; // TODO Delete me

require_once('src/database/mysql.php'); // TODO: Remove me, testing
