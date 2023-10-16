<?php
// Database configuration
$dbHost = "localhost";
$dbUser = "cron";
$dbPass = "1234";
$dbName = "asterisk";

// Create a database connection
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$db = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
