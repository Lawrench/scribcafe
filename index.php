<?php

use App\Managers\SSOManager;

session_start();

// AUTOLOAD
require_once __DIR__ . '/vendor/autoload.php';

// SSO AUTHENTICATION
SSOManager::init();

// START

echo "Hello World <br />" . PHP_EOL; // TODO Delete me

require_once('src/database/mysql.php');
