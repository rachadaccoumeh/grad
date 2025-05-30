<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = "root123"; // Replace with your database password
$dbname = "roomgenius_db"; // Replace with your database name

// Response array
$response = [
    'success' => false,
    'quantity' => 0,
    'message' => ''
];

// Check if product_id is provided
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    $response['message'] = 'Product ID is required';
    echo json_encode($response);
    exit;
}

$product_id = $_GET['product_id'];

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute query to get stock quantity
    $query = "SELECT stock_quantity FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['success'] = true;
        $response['quantity'] = (int)$row['stock_quantity'];
    } else {
        $response['message'] = 'Product not found';
    }
    
    // Close statement and connection
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return response as JSON
echo json_encode($response);
?>
