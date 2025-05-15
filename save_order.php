<?php
// save_order.php - Save order data to the database

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

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    die(json_encode(['success' => false, 'message' => 'Invalid input data']));
}

// Extract order details
$orderId = $data['orderId'];
$fullName = $data['fullName'];
$email = $data['email'];
$address = $data['address'];
$city = $data['city'];
$zipCode = $data['zipCode'];
$governorate = $data['governorate'];
$phone = $data['phone'];
$latitude = $data['latitude'];
$longitude = $data['longitude'];
$paymentMethod = $data['paymentMethod'];
$subtotal = $data['subtotal'];
$tax = $data['tax'];
$shipping = $data['shipping'];
$total = $data['total'];
$orderStatus = $data['orderStatus'];
$paymentStatus = $data['paymentStatus'];
$orderDate = date('Y-m-d H:i:s');

// Store the main order
$sql = "INSERT INTO orders (
    order_id, 
    customer_name, 
    email, 
    address, 
    city, 
    zip_code, 
    governorate, 
    phone, 
    latitude, 
    longitude, 
    payment_method, 
    subtotal, 
    tax, 
    shipping, 
    total, 
    order_status,
    payment_status,
    order_date
) VALUES (
    '$orderId', 
    '$fullName', 
    '$email', 
    '$address', 
    '$city', 
    '$zipCode', 
    '$governorate', 
    '$phone', 
    '$latitude', 
    '$longitude', 
    '$paymentMethod', 
    '$subtotal', 
    '$tax', 
    '$shipping', 
    '$total', 
    '$orderStatus',
    '$paymentStatus',
    '$orderDate'
)";

if ($conn->query($sql) !== TRUE) {
    die(json_encode(['success' => false, 'message' => 'Error: ' . $sql . '<br>' . $conn->error]));
}

// Get the auto-incremented ID
$orderId = $conn->insert_id;

// Store order items
foreach ($data['items'] as $item) {
    $productId = $item['id'];
    $productName = $conn->real_escape_string($item['name']);
    $quantity = $item['quantity'];
    $price = $item['price'];
    $image = $conn->real_escape_string($item['img']);
    
    $sql = "INSERT INTO order_items (
        order_id, 
        product_id, 
        product_name, 
        quantity, 
        price,
        image
    ) VALUES (
        '$orderId', 
        '$productId', 
        '$productName', 
        '$quantity', 
        '$price',
        '$image'
    )";
    
    $conn->query($sql);
}

// Return success response
echo json_encode(['success' => true, 'message' => 'Order saved successfully']);

$conn->close();
?>