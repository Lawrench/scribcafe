<?php

require_once __DIR__ . '/vendor/autoload.php'; // Adjust the path as needed
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Hello World";
