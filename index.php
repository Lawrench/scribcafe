<?php

session_start();

// AUTOLOAD
require_once __DIR__ . '/vendor/autoload.php';

// vlucas/phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// SSO AUTHENTICATION
require_once('src/discourse/sso_login.php');
App\Discourse\SSOLogin::init();

// START

echo "Hello World <br />" . PHP_EOL; // TODO Delete me

require_once('src/database/mysql.php');
