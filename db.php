<?php
// db.php - Database connection

$servername = "localhost"; // Your MySQL server (localhost for local development)
$username = "root";        // Your MySQL username (default is root for local development)
$password = "root123";            // Your MySQL password (empty for local development)
$dbname = "roomgenius_db"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>