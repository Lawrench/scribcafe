<?php
session_start();

require_once('discourse/sso_login.php');
App\Discourse\SSOLogin::init();

echo "Back to index.php" . PHP_EOL;

// API DEMO
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://forums.scribcafe.com/admin/users/list/active.json',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Api-Key: 0b644dcd6687f2d1ca234622798afa73f3f6e128f285702744fca6b0563c3f9e',
        'Api-Username: system'
    ),
));
$response = curl_exec($curl);
curl_close($curl);
print("<pre>".print_r($response,true)."</pre>");


//echo phpinfo();

// TODO: don't use root for ftp lol
// TODO: setup github action for merge, use local docker instance for PHP
// TODO: consider Laravel or better mysql interface
// TODO: Code Sniffer
// TODO: Unit Tests and coverage
