<?php

// TODO: use library

// TODO: DELETE ME
function testDatabaseConnection($host, $user, $password, $database): void
{
    $mysqli = new mysqli($host, $user, $password, $database);

    if ($mysqli->connect_error) {
        echo "Connection failed: " . $mysqli->connect_error;
    } else {
        echo "Connected successfully to the database!";
        $mysqli->close();
    }
}

// TODO: DELETE ME
$host = 'db'; // The hostname of the MySQL container
$user = getenv('MYSQL_USER'); // Replace with your MySQL user
$password = getenv('MYSQL_PASSWORD'); // Replace with your MySQL user's password
$database = getenv('MYSQL_DATABASE'); // Replace with your MySQL database name

testDatabaseConnection($host, $user, $password, $database);
