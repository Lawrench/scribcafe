<?php

$servername = "localhost";
$username = "username";
$password = "mauFJcuf5dhRMQrjj";

// Create connection
// $conn = new mysqli($servername, $username, $password);
$dbh = new PDO($servername, $username, $password);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
