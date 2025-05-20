<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root123');
define('DB_NAME', 'roomgenius_db');

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper character encoding
$conn->set_charset("utf8mb4");

// Set timezone to Beirut
// date_default_timezone_set('Asia/Beirut');

// Error reporting for development (comment out in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
