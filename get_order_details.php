<?php
/**
 * get_order_details.php - Fetches order details for the admin panel and returns them as JSON
 * 
 * This file retrieves order information and related order items from the database
 * and returns them in a structured JSON format for display in the admin panel.
 */

// Set content type to JSON
header('Content-Type: application/json');

// Prevent PHP from showing errors directly in the output
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "root123"; // Using the password from your configuration
    $dbname = "roomgenius_db";
    
    // Create connection with error suppression
    $conn = @new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    // Check if ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('Order ID not provided');
    }
    
    // Get the order ID from the query parameter
    $orderId = intval($_GET['id']);
    
    // Get order details
    $sql = "SELECT * FROM orders WHERE id = $orderId";
    $result = $conn->query($sql);
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception('Order not found');
    }
    
    // Fetch the order data
    $order = $result->fetch_assoc();
    
    // Add order_date field if it doesn't exist
    if (!isset($order['order_date'])) {
        $order['order_date'] = isset($order['created_at']) ? $order['created_at'] : date('Y-m-d H:i:s');
    }
    
    // Get order items
    $sql = "SELECT oi.*, p.name as product_name, p.image_url as image 
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = '{$order['order_id']}'";
    
    $itemsResult = $conn->query($sql);
    
    // Prepare items array
    $items = [];
    if ($itemsResult && $itemsResult->num_rows > 0) {
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
        }
    }
    
    // Return the order and items as JSON
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} finally {
    // Close the database connection if it exists
    if (isset($conn) && $conn) {
        $conn->close();
    }
}

    if ($order['payment_status'] == 'paid') {
        $paymentStatusText = 'Paid';
        $paymentStatusClass = 'completed';
    } else {
        $paymentStatusText = 'Pending';
        $paymentStatusClass = 'pending';
    }
} else {
    $paymentStatusText = 'Pending';
    $paymentStatusClass = 'pending';
}
// Output HTML for the order details modal
?>

<div class="order-details">
    <h3>Order Information</h3>
    <div class="details-grid">
        <div class="detail-item">
            <div class="detail-label">Order ID</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['order_id']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Date</div>
            <div class="detail-value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="status-<?php echo strtolower($order['status']); ?>">
                    <?php echo ucfirst($order['status']); ?>
                </span>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Payment Method</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['payment_method']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Payment Status</div>
            <div class="detail-value">
                <span class="payment-<?php echo $paymentStatusClass; ?>">
                    <?php echo $paymentStatusText; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="order-details">
    <h3>Customer Information</h3>
    <div class="details-grid">
        <div class="detail-item">
            <div class="detail-label">Name</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Email</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['email']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Phone</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['phone']); ?></div>
        </div>
    </div>
</div>

<div class="order-details">
    <h3>Shipping Information</h3>
    <div class="details-grid">
        <div class="detail-item">
            <div class="detail-label">Address</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['address']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">City</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['city']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">ZIP Code</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['zip_code']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Governorate</div>
            <div class="detail-value"><?php echo htmlspecialchars($order['governorate']); ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Coordinates</div>
            <div class="detail-value">
                Lat: <?php echo $order['latitude']; ?>, Lng: <?php echo $order['longitude']; ?>
                <a href="https://www.google.com/maps?q=<?php echo $order['latitude']; ?>,<?php echo $order['longitude']; ?>" target="_blank" style="margin-left: 10px; font-size: 13px;">
                    <i class="fas fa-map-marker-alt"></i> View on Map
                </a>
            </div>
        </div>
    </div>
</div>

<div class="order-details">
    <h3>Order Items</h3>
    <?php if ($itemsResult->num_rows > 0): ?>
        <table class="item-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $itemsResult->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($item['name'] ?? 'Product ID: ' . $item['product_id']); ?>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price_at_purchase'], 2); ?></td>
                        <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Subtotal:</strong></td>
                    <td>$<?php echo number_format($order['subtotal'], 2); ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Shipping:</strong></td>
                    <td>$<?php echo number_format($order['shipping'], 2); ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Tax:</strong></td>
                    <td>$<?php echo number_format($order['tax'], 2); ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong>$<?php echo number_format($order['total'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    <?php else: ?>
        <p>No items found for this order.</p>
    <?php endif; ?>
</div>

<!-- Update Order Status Form -->
<div class="status-form">
    <h3>Update Order</h3>
    <form action="admin_orders.php" method="post">
        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
        
        <div class="form-group">
            <label for="status">Order Status:</label>
            <select name="status" id="status" required>
                <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="completed" <?php echo ($order['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        
        <?php if ($order['payment_method'] == 'Cash on Delivery'): ?>
            <div class="form-group">
                <label for="payment_status">Payment Status:</label>
                <select name="payment_status" id="payment_status">
                    <option value="pending" <?php echo ($paymentStatusText == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="paid" <?php echo ($paymentStatusText == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                </select>
            </div>
        <?php endif; ?>
        
        <button type="submit" name="update_status" class="update-btn">Update Order</button>
    </form>
</div>

<?php
// Close database connection
$conn->close();
?>