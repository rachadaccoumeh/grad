<?php
/**
 * get_order_details.php - Fetches order details for the admin panel
 * 
 * This file retrieves order information and related order items from the database
 * and returns them in a structured JSON format for display in the admin panel.
 */

// Ensure we're always returning JSON
header('Content-Type: application/json');

// Prevent PHP from showing errors directly in the output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Log file for debugging
$logFile = 'order_details_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Request received\n", FILE_APPEND);

try {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "root123"; // Using the password from your configuration
    $dbname = "roomgenius_db";
    
    // Log connection attempt
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Connecting to database: $dbname\n", FILE_APPEND);
    
    // Create connection with error suppression
    $conn = @new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Connection failed: " . $conn->connect_error . "\n", FILE_APPEND);
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Connected to database\n", FILE_APPEND);
    
    // Check if ID is provided
    if (!isset($_GET['id'])) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - No ID provided\n", FILE_APPEND);
        throw new Exception('Order ID not provided');
    }
    
    // Get the order ID from the query parameter
    $orderId = intval($_GET['id']);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Looking for order ID: $orderId\n", FILE_APPEND);
    
    // Get order details
    $sql = "SELECT * FROM orders WHERE id = $orderId";
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - SQL: $sql\n", FILE_APPEND);
    
    $result = $conn->query($sql);
    
    if (!$result) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Query error: " . $conn->error . "\n", FILE_APPEND);
        throw new Exception('Database query error: ' . $conn->error);
    }
    
    if ($result->num_rows === 0) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - No order found with ID: $orderId\n", FILE_APPEND);
        throw new Exception("Order not found with ID: $orderId");
    }
    
    // Fetch the order data
    $order = $result->fetch_assoc();
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Order found: " . json_encode($order) . "\n", FILE_APPEND);
    
    // Add order_date field if it doesn't exist
    if (!isset($order['order_date'])) {
        $order['order_date'] = isset($order['created_at']) ? $order['created_at'] : date('Y-m-d H:i:s');
    }
    
    // Get order items with complete information including prices
    $orderIdStr = $conn->real_escape_string($order['order_id']);
    
    // Modified query to ensure we get all necessary fields with proper aliases
    $sql = "SELECT 
                oi.id, 
                oi.order_id, 
                oi.product_id, 
                oi.quantity, 
                oi.price_at_purchase as price, 
                oi.total_price, 
                p.name as product_name, 
                p.image_path as image,
                p.price as product_price
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = '$orderIdStr'";
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Items SQL: $sql\n", FILE_APPEND);
    
    $itemsResult = $conn->query($sql);
    
    if (!$itemsResult) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Items query error: " . $conn->error . "\n", FILE_APPEND);
    }
    
    // Prepare items array
    $items = [];
    if ($itemsResult && $itemsResult->num_rows > 0) {
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
        }
    }
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Found " . count($items) . " items\n", FILE_APPEND);
    
    // Prepare the response
    $response = [
        'success' => true,
        'order' => $order,
        'items' => $items
    ];
    
    // Convert to JSON
    $jsonResponse = json_encode($response);
    
    // Check for JSON encoding errors
    if ($jsonResponse === false) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - JSON encoding error: " . json_last_error_msg() . "\n", FILE_APPEND);
        throw new Exception('Failed to encode response as JSON: ' . json_last_error_msg());
    }
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Returning successful response\n", FILE_APPEND);
    
    // Return the order and items as JSON
    echo $jsonResponse;
    
} catch (Exception $e) {
    // Log the error
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} finally {
    // Close the database connection if it exists
    if (isset($conn) && $conn) {
        $conn->close();
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Database connection closed\n", FILE_APPEND);
    }
}
