<?php
// Database connection
$servername = "localhost";
$username = "root"; // Change as needed
$password = ""; // Change as needed
$dbname = "roomgenius_db"; // Change as needed

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    die(json_encode(['success' => false, 'message' => 'Order ID not provided']));
}

$orderId = $_GET['id'];

// Get order details
$sql = "SELECT * FROM orders WHERE id = $orderId";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die(json_encode(['success' => false, 'message' => 'Order not found']));
}

$order = $result->fetch_assoc();

// Get order items
$sql = "SELECT * FROM order_items WHERE order_id = $orderId";
$itemsResult = $conn->query($sql);

$items = [];
if ($itemsResult->num_rows > 0) {
    while($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
    }
}

// Return data as JSON
echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);

$conn->close();
?>