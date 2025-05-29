<?php
/**
 * save_order.php - Handles saving order data to the database
 * 
 * This file processes the order data from the checkout form and saves it to the database,
 * updates product stock quantities, and returns a JSON response indicating success or failure.
 */

// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging but don't display to user
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Log incoming data for debugging
$rawInput = file_get_contents('php://input');
error_log('Received order data: ' . $rawInput);

try {
    // Database connection settings
    $servername = "localhost";
    $username = "root";
    $password = "root123";
    $dbname = "roomgenius_db";
    
    // Create connection with error suppression
    $conn = @new mysqli($servername, $username, $password, $dbname);
    
    // Enable transaction support
    $conn->autocommit(false);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    // Parse the JSON input
    $data = json_decode($rawInput, true);
    
    // Validate input data
    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }
    
    // Check required fields
    $requiredFields = ['orderId', 'fullName', 'email', 'address', 'city', 'zipCode', 'governorate', 'phone', 'items'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Extract and sanitize order details
    $orderId = $conn->real_escape_string($data['orderId']);
    $fullName = $conn->real_escape_string($data['fullName']);
    $email = $conn->real_escape_string($data['email']);
    $address = $conn->real_escape_string($data['address']);
    $city = $conn->real_escape_string($data['city']);
    $zipCode = $conn->real_escape_string($data['zipCode']);
    $governorate = $conn->real_escape_string($data['governorate']);
    $phone = $conn->real_escape_string($data['phone']);
    $latitude = isset($data['latitude']) ? floatval($data['latitude']) : 0;
    $longitude = isset($data['longitude']) ? floatval($data['longitude']) : 0;
    $paymentMethod = $conn->real_escape_string($data['paymentMethod']);
    $subtotal = floatval($data['subtotal']);
    $shipping = floatval($data['shipping']);
    $tax = isset($data['tax']) ? floatval($data['tax']) : 0;
    $total = floatval($data['total']);
    
    // Card payment details (if applicable)
    $cardName = isset($data['cardName']) ? $conn->real_escape_string($data['cardName']) : '';
    $cardNumber = isset($data['cardNumber']) ? $conn->real_escape_string($data['cardNumber']) : '';
    $cardExpiry = isset($data['cardExpiry']) ? $conn->real_escape_string($data['cardExpiry']) : '';
    
    // Default order status
    $status = 'pending';
    
    // Check if order ID already exists and generate a new one if needed
    $checkSql = "SELECT order_id FROM orders WHERE order_id = '$orderId'";
    $result = $conn->query($checkSql);
    
    if ($result && $result->num_rows > 0) {
        // Generate a new unique order ID
        $orderId = 'ORD' . date('YmdHis') . rand(100, 999);
        error_log('Generated new order ID due to collision: ' . $orderId);
    }
    
    // Insert the order into the database
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
        shipping, 
        tax, 
        total,
        card_name,
        card_number,
        card_expiry,
        status
    ) VALUES (
        '$orderId', 
        '$fullName', 
        '$email', 
        '$address', 
        '$city', 
        '$zipCode', 
        '$governorate', 
        '$phone', 
        $latitude, 
        $longitude, 
        '$paymentMethod', 
        $subtotal, 
        $shipping, 
        $tax, 
        $total,
        '$cardName',
        '$cardNumber',
        '$cardExpiry',
        '$status'
    )";
    
    // Log the SQL query for debugging
    error_log('Order SQL: ' . $sql);
    
    // Execute the query
    if (!$conn->query($sql)) {
        throw new Exception('Error saving order: ' . $conn->error);
    }
    
    // Process order items
    $orderItems = $data['items'];
    $itemsSaved = 0;
    $itemsAttempted = 0;
    $itemsSkipped = 0;
    
    // Validate items array
    if (!is_array($orderItems) || empty($orderItems)) {
        error_log('No items in order or invalid items array');
        // Continue with the order even without items
        // This is a fallback to ensure the order is still created
    } else {
        // First, get all valid product IDs from the database
        // Add detailed logging to help diagnose the issue
        error_log('Fetching valid product IDs from database');
        
        $validProductIds = array();
        $productsSql = "SELECT id, product_id, name FROM products";
        $productsResult = $conn->query($productsSql);
        
        if (!$productsResult) {
            error_log('Error fetching products: ' . $conn->error);
        } else {
            error_log('Found ' . $productsResult->num_rows . ' products in database');
            
            if ($productsResult->num_rows > 0) {
                while ($row = $productsResult->fetch_assoc()) {
                    // Store both the numeric ID and the product_id string
                    $validProductIds[] = $row['id'];
                    error_log("Product found: ID={$row['id']}, product_id={$row['product_id']}, name={$row['name']}");
                }
            }
        }
        
        error_log('Valid product IDs in database: ' . implode(',', $validProductIds));
        
        // Insert each item that has a valid product ID
        foreach ($orderItems as $item) {
            $itemsAttempted++;
            
            // Validate item data
            if (!isset($item['id']) || !isset($item['price']) || !isset($item['quantity'])) {
                error_log('Skipping invalid item format: ' . json_encode($item));
                $itemsSkipped++;
                continue;
            }
            
            // Extract and sanitize item details
            // Log the raw item data for debugging
            error_log('Processing item: ' . json_encode($item));
            
            // The ID in the cart might be a string or a product_id, not the actual database ID
            $cartItemId = $item['id'];
            $quantity = intval($item['quantity']);
            $price = floatval($item['price']);
            $totalPrice = $price * $quantity;
            
            // Try to find the actual product ID in the database
            // First, check if the cart item ID directly matches a product ID
            $productId = null;
            
            // Query to find the product by either id or product_id
            $findProductSql = "SELECT id FROM products WHERE id = '$cartItemId' OR product_id = '$cartItemId' LIMIT 1";
            error_log("Looking for product with query: $findProductSql");
            
            $productResult = $conn->query($findProductSql);
            
            if ($productResult && $productResult->num_rows > 0) {
                $productRow = $productResult->fetch_assoc();
                $productId = $productRow['id'];
                error_log("Found product ID: $productId for cart item ID: $cartItemId");
                
                // Check product stock availability
                $stockCheckSql = "SELECT stock_quantity FROM products WHERE id = $productId";
                $stockResult = $conn->query($stockCheckSql);
                
                if ($stockResult && $stockResult->num_rows > 0) {
                    $stockRow = $stockResult->fetch_assoc();
                    $currentStock = (int)$stockRow['stock_quantity'];
                    error_log("Current stock for product ID $productId: $currentStock, Requested: $quantity");
                    
                    // Check if we have enough stock
                    if ($currentStock < $quantity) {
                        error_log("Insufficient stock for product ID: $productId. Available: $currentStock, Requested: $quantity");
                        $itemsSkipped++;
                        continue;
                    }
                } else {
                    error_log("Could not retrieve stock information for product ID: $productId");
                    $itemsSkipped++;
                    continue;
                }
            } else {
                error_log("Could not find product for cart item ID: $cartItemId");
                $itemsSkipped++;
                continue;
            }
            
            // Insert the item using the correct product ID
            $itemSql = "INSERT INTO order_items (
                order_id,
                product_id,
                quantity,
                price_at_purchase,
                total_price
            ) VALUES (
                '$orderId',
                $productId,
                $quantity,
                $price,
                $totalPrice
            )";
            
            // Log the item SQL for debugging
            error_log('Order item SQL: ' . $itemSql);
            
            // Execute the query
            if ($conn->query($itemSql)) {
                // Update the product stock quantity
                $newStock = $currentStock - $quantity;
                $updateStockSql = "UPDATE products SET stock_quantity = $newStock WHERE id = $productId";
                
                if ($conn->query($updateStockSql)) {
                    $itemsSaved++;
                    error_log("Successfully saved order item and updated stock for product ID: $productId. New stock: $newStock");
                } else {
                    error_log('Error updating product stock: ' . $conn->error);
                    $conn->rollback();
                    throw new Exception('Error updating product stock for product ID: ' . $productId);
                }
            } else {
                error_log('Error saving order item: ' . $conn->error);
                $itemsSkipped++;
            }
        }
    }
    
    // Commit the transaction if everything was successful
    if (!$conn->commit()) {
        error_log('Transaction commit failed: ' . $conn->error);
        throw new Exception('Transaction commit failed. Please try again.');
    }
    
    // Return success response with detailed information
    // This helps the user understand what happened with their order
    $message = 'Order placed successfully!'; 
    
    // Add information about items if there were issues
    if ($itemsAttempted > 0 && $itemsSkipped > 0) {
        $message .= ' Note: ' . $itemsSkipped . ' out of ' . $itemsAttempted . ' items could not be added to your order due to product availability.'; 
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'order_id' => $orderId,
        'items_saved' => $itemsSaved,
        'items_attempted' => $itemsAttempted,
        'items_skipped' => $itemsSkipped
    ]);
    
} catch (Exception $e) {
    // Rollback the transaction if an error occurred
    if (isset($conn) && $conn) {
        $conn->rollback();
    }
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log the error
    error_log('Order processing error: ' . $e->getMessage());
    
} finally {
    // Close the database connection if it exists
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>