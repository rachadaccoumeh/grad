<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Set the current page for the sidebar
$current_page = 'orders';

// Database connection
require_once 'db_connect.php';
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Log database connection for debugging
error_log('Connected to database: ' . DB_NAME);

// Update order status if requested
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = intval($_POST['order_id']); // Ensure it's an integer
    $status = $conn->real_escape_string($_POST['status']); // Sanitize input
    
    // Update the status field
    $sql = "UPDATE orders SET status = '$status' WHERE id = $orderId";
    
    // Log the SQL for debugging
    error_log("Updating order status: $sql");
    
    // Execute the query and check for errors
    if ($conn->query($sql)) {
        // Success message
        $statusMessage = "Order status updated to $status";
    } else {
        error_log("Error updating order status: " . $conn->error);
    }
}

// Update payment status if requested
// Since there's no dedicated payment_status field, we'll update the payment_method field
// to indicate whether the payment has been received
if (isset($_POST['order_id']) && isset($_POST['payment_status'])) {
    $orderId = intval($_POST['order_id']); // Ensure it's an integer
    $paymentStatus = $conn->real_escape_string($_POST['payment_status']); // Sanitize input
    
    // Get the current payment method
    $getMethodSql = "SELECT payment_method FROM orders WHERE id = $orderId";
    $methodResult = $conn->query($getMethodSql);
    
    if ($methodResult && $methodResult->num_rows > 0) {
        $row = $methodResult->fetch_assoc();
        $currentMethod = $row['payment_method'];
        
        // Determine the new payment method based on the current one and the requested status
        $newMethod = $currentMethod;
        
        // If it's cash on delivery, we can mark it as paid or unpaid
        if (strpos($currentMethod, 'cash_on_delivery') !== false) {
            if ($paymentStatus === 'Paid') {
                $newMethod = 'cash_on_delivery_paid';
            } else {
                $newMethod = 'cash_on_delivery';
            }
        }
        // For credit card payments, they're already marked as paid
        else if (strpos($currentMethod, 'credit_card') !== false) {
            if ($paymentStatus === 'Unpaid') {
                $newMethod = 'credit_card_refunded';
            } else {
                $newMethod = 'credit_card';
            }
        }
        
        // Update the payment method to reflect the payment status
        $updateSql = "UPDATE orders SET payment_method = '$newMethod' WHERE id = $orderId";
        
        // Log the SQL for debugging
        error_log("Updating payment status: $updateSql");
        
        // Execute the query and check for errors
        if ($conn->query($updateSql)) {
            // Success message
            $paymentMessage = "Payment status updated to $paymentStatus";
        } else {
            error_log("Error updating payment status: " . $conn->error);
        }
    }
}

// Get all orders
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Panel</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="orders.css">
    <style>
        /* Fix spacing issues while preserving admin styles */
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            overflow-x: hidden;
        }
        
        /* Custom layout structure */
        #admin-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
        }
        
        /* Preserve navigation styles from admin.css but fix positioning */
        .navigation {
            position: fixed !important;
            width: 250px !important;
            height: 100% !important;
            z-index: 1000 !important;
            transition: 0.5s !important;
            left: 0 !important;
        }
        
        /* Fix the main content area */
        #admin-content {
            margin-left: 250px !important;
            width: calc(100% - 250px) !important;
            min-height: 100vh !important;
            transition: margin-left 0.3s ease, width 0.3s ease !important;
            position: relative !important;
            overflow-x: hidden !important;
        }
        
        /* Handle sidebar toggle states */
        .navigation.active {
            width: 70px !important;
        }
        
        #admin-content.expanded {
            margin-left: 70px !important;
            width: calc(100% - 70px) !important;
        }
        
        /* Fix topbar styling */
        .topbar {
            width: 100% !important;
            padding: 10px 20px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1) !important;
        }
        
        /* User image styling */
        .user {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .user img {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            cursor: pointer !important;
        }
        
        /* Make sure the toggle button works */
        .toggle {
            cursor: pointer !important;
            font-size: 24px !important;
        }
        
        /* Content container */
        .orders-container {
            padding: 20px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        /* Fix any other potential spacing issues */
        .main {
            all: unset !important;
            display: contents !important;
        }
        
        .container {
            all: unset !important;
            display: contents !important;
        }
        
        /* Status update message styles */
        .status-updated, .payment-updated {
            padding: 5px 10px;
            margin: 5px 0;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            animation: fadeOut 5s forwards;
        }
        
        .status-updated {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .payment-updated {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0.5; }
        }
        
        .orders-container {
            width: 100%;
            padding: 20px;
            margin: 0;
            box-sizing: border-box;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .orders-table th, .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .orders-table th {
            background-color: #24424c;
            color: white;
            font-weight: 600;
        }
        
        .orders-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-btn, .payment-btn, .view-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .status-pending {
            background-color: #f0ad4e;
            color: white;
        }
        
        .status-delivered {
            background-color: #4CAF50;
            color: white;
        }
        
        .status-cancelled {
            background-color: #f44336;
            color: white;
        }
        
        .payment-paid {
            background-color: #5cb85c;
            color: white;
        }
        
        .payment-unpaid {
            background-color: #d9534f;
            color: white;
        }
        
        .view-btn {
            background-color: #24424c;
            color: white;
        }
        
        .view-btn:hover {
            background-color: #1b323a;
        }
        
        /* Order Details Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff8e3;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            position: relative;
        }
        
        .close-btn {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .order-details-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .customer-info, .order-items, .order-summary {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #24424c;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
        }
        
        .info-item {
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: 600;
            color: #24424c;
        }
        
        .status-text-pending {
            color: #ff9800;
            font-weight: 600;
        }
        
        .status-text-delivered {
            color: #4CAF50;
            font-weight: 600;
        }
        
        .status-text-cancelled {
            color: #f44336;
            font-weight: 600;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .items-table th, .items-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f5f5f5;
            font-weight: 600;
        }
        
        .price-details {
            margin-top: 15px;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        
        .price-row.total {
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        #mapContainer {
            height: 300px;
            width: 100%;
            margin-top: 15px;
            border-radius: 5px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- Custom wrapper structure with original elements -->
    <div id="admin-wrapper">
        <!-- Include the original sidebar -->
        <?php include 'admin_sidebar.php'; ?>
        
        <!-- Custom content container -->
        <div id="admin-content">
            <!-- Original main div for compatibility -->
            <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <i class='bx bx-menu'></i>
                </div>
                <div class="user">
                    <img src="photos/adminphoto.JPG" alt="Admin">
                </div>
            </div>


            <div class="orders-container">
                <div class="header">
                    <h2>Orders Dashboard</h2>
                </div>
            
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Order Status</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['order_id']; ?></td>
                                <td><?php echo $row['customer_name']; ?></td>
                                <td><?php 
                                    // Use created_at instead of order_date, and add a fallback
                                    $dateStr = isset($row['created_at']) ? $row['created_at'] : null;
                                    echo $dateStr ? date('M d, Y', strtotime($dateStr)) : 'N/A'; 
                                ?></td>
                                <td>$<?php echo $row['total']; ?></td>
                                <td><?php echo $row['payment_method']; ?></td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <?php 
                                        // Use status instead of order_status, and add a fallback
                                        $orderStatus = isset($row['status']) ? $row['status'] : 'pending';
                                        // Normalize status to title case for display
                                        $displayStatus = ucfirst($orderStatus);
                                        // Add a special label for cancelled orders
                                        if (strtolower($orderStatus) === 'cancelled') {
                                            $displayStatus = '<span style="color: white; background-color: #f44336; padding: 3px 6px; border-radius: 3px;">Cancelled</span>';
                                        }
                                        // Determine button class based on status
                                        if (strtolower($orderStatus) === 'pending') {
                                            $statusClass = 'status-pending';
                                            $nextStatus = 'delivered';
                                        } else if (strtolower($orderStatus) === 'delivered') {
                                            $statusClass = 'status-delivered';
                                            $nextStatus = 'cancelled';
                                        } else if (strtolower($orderStatus) === 'cancelled') {
                                            $statusClass = 'status-cancelled';
                                            $nextStatus = 'pending';
                                        } else {
                                            $statusClass = 'status-pending';
                                            $nextStatus = 'delivered';
                                        }
                                        
                                        // Add success message if status was updated
                                        if (isset($statusMessage) && isset($_POST['order_id']) && $_POST['order_id'] == $row['id']) {
                                            echo "<div class='status-updated'>$statusMessage</div>";
                                        }
                                        ?>
                                        <button type="submit" 
                                            class="status-btn <?php echo $statusClass; ?>"
                                            name="status" 
                                            value="<?php echo $nextStatus; ?>">
                                            <?php echo $displayStatus; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <?php
                                        // Derive payment status from payment_method
                                        $paymentMethod = isset($row['payment_method']) ? $row['payment_method'] : '';
                                        
                                        // Determine payment status based on payment method
                                        if (strpos($paymentMethod, 'credit_card') !== false && strpos($paymentMethod, 'refunded') === false) {
                                            $paymentStatus = 'Paid';
                                        } else if (strpos($paymentMethod, 'cash_on_delivery_paid') !== false) {
                                            $paymentStatus = 'Paid';
                                        } else {
                                            $paymentStatus = 'Unpaid';
                                        }
                                        
                                        $paymentClass = $paymentStatus === 'Paid' ? 'payment-paid' : 'payment-unpaid';
                                        $nextPaymentStatus = $paymentStatus === 'Paid' ? 'Unpaid' : 'Paid';
                                        
                                        // Add success message if payment status was updated
                                        if (isset($paymentMessage) && isset($_POST['order_id']) && $_POST['order_id'] == $row['id']) {
                                            echo "<div class='payment-updated'>$paymentMessage</div>";
                                        }
                                        ?>
                                        <button type="submit" 
                                            class="payment-btn <?php echo $paymentClass; ?>"
                                            name="payment_status" 
                                            value="<?php echo $nextPaymentStatus; ?>">
                                            <?php echo $paymentStatus; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <button class="view-btn" onclick="viewOrderDetails(<?php echo $row['id']; ?>)">View Details</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No orders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                </table>
            </div>
            <!-- Close the main div -->
            </div>
        </div>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
    <script>
        // View order details
        function viewOrderDetails(orderId) {
            // Use the new get_order_details_new.php file
            fetch('get_order_details_new.php?id=' + orderId)
                .then(response => {
                    // Log the raw response for debugging
                    console.log('Response status:', response.status);
                    return response.text().then(text => {
                        try {
                            // Try to parse as JSON
                            console.log('Raw response:', text);
                            return JSON.parse(text);
                        } catch (e) {
                            // If not valid JSON, log the raw response and throw error
                            console.error('Invalid JSON response:', text);
                            throw new Error('Server returned invalid JSON: ' + text);
                        }
                    });
                })
                .then(data => {
                    console.log('Parsed data:', data);
                    if (data.success) {
                        displayOrderDetails(data.order, data.items);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching order details: ' + error.message);
                });
        }
        
        // Display order details in modal
        function displayOrderDetails(order, items) {
            const modal = document.getElementById('orderDetailsModal');
            const content = document.getElementById('orderDetailsContent');
            
            // Format the order date
            const orderDate = new Date(order.order_date);
            const formattedDate = orderDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Build order details HTML
            let html = `
                <div class="order-details-header">
                    <div>
                        <h2>Order #${order.order_id}</h2>
                        <p>Placed on ${formattedDate}</p>
                    </div>
                    <div>
                        <h3>Total: $${order.total}</h3>
                        <p>Payment Method: ${order.payment_method}</p>
                        <p>Status: <span class="${order.status === 'cancelled' ? 'status-text-cancelled' : (order.status === 'delivered' ? 'status-text-delivered' : 'status-text-pending')}">${order.status ? order.status.charAt(0).toUpperCase() + order.status.slice(1) : 'Pending'}</span></p>
                    </div>
                </div>
                
                <div class="order-details-content">
                    <div class="customer-info">
                        <h3 class="section-title">Customer Information</h3>
                        <div class="info-grid">
                            <div>
                                <p class="info-item"><span class="info-label">Name:</span> ${order.customer_name}</p>
                                <p class="info-item"><span class="info-label">Email:</span> ${order.email}</p>
                                <p class="info-item"><span class="info-label">Phone:</span> ${order.phone}</p>
                            </div>
                            <div>
                                <p class="info-item"><span class="info-label">Address:</span> ${order.address}</p>
                                <p class="info-item"><span class="info-label">City:</span> ${order.city}, ${order.zip_code}</p>
                                <p class="info-item"><span class="info-label">Governorate:</span> ${order.governorate}</p>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 15px;">Delivery Location</h4>
                        <div id="mapContainer"></div>
                    </div>
                    
                    <div class="order-items">
                        <h3 class="section-title">Order Items</h3>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>`;
            
            // Add order items with improved price handling
            items.forEach(item => {
                // Add detailed logging to debug price issues
                console.log('Item data:', item);
                
                // Use price_at_purchase from order_items table, with fallbacks to handle missing data
                const price = item.price ? parseFloat(item.price) : 
                             (item.price_at_purchase ? parseFloat(item.price_at_purchase) : 
                             (item.product_price ? parseFloat(item.product_price) : 0));
                
                // Ensure quantity is a valid number
                const quantity = parseInt(item.quantity) || 1;
                
                // Calculate total with safeguards against NaN
                const itemTotal = price * quantity;
                
                // Format the HTML with proper price display and fallbacks for missing images
                html += `
                    <tr>
                        <td>
                            <div class="item-image">
                                ${item.image ? `<img src="${item.image}" alt="${item.product_name || 'Product'}" onerror="this.src='images/placeholder.jpg';">` : '<div class="no-image">No Image</div>'}
                            </div>
                        </td>
                        <td>${item.product_name || 'Unknown Product'}</td>
                        <td>$${isNaN(price) ? '0.00' : price.toFixed(2)}</td>
                        <td>${quantity}</td>
                        <td>$${isNaN(itemTotal) ? '0.00' : itemTotal.toFixed(2)}</td>
                    </tr>`;
            });
            
            html += `
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="order-summary">
                        <h3 class="section-title">Order Summary</h3>
                        <div class="price-details">
                            <div class="price-row">
                                <div>Subtotal</div>
                                <div>$${order.subtotal}</div>
                            </div>
                            <div class="price-row">
                                <div>Shipping</div>
                                <div>$${order.shipping}</div>
                            </div>
                            <div class="price-row">
                                <div>Tax</div>
                                <div>$${order.tax}</div>
                            </div>
                            <div class="price-row total">
                                <div>Total</div>
                                <div>$${order.total}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            content.innerHTML = html;
            modal.style.display = 'block';
            
            // Initialize map after modal is displayed
            setTimeout(() => {
                initMap(parseFloat(order.latitude), parseFloat(order.longitude));
            }, 300);
        }
        
        // Initialize map with customer location
        function initMap(lat, lng) {
            const mapContainer = document.getElementById('mapContainer');
            if (!mapContainer) return;
            
            // Create map
            const map = L.map('mapContainer').setView([lat, lng], 15);
            
            // Add tile layer
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);
            
            // Add marker
            L.marker([lat, lng]).addTo(map)
                .bindPopup('Delivery Location')
                .openPopup();
            
            // Force map to refresh
            setTimeout(() => map.invalidateSize(), 100);
        }
        
        // Close modal
        function closeModal() {
            document.getElementById('orderDetailsModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderDetailsModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
    <script>
        // Toggle sidebar with restored structure
        document.addEventListener('DOMContentLoaded', function() {
            let toggle = document.querySelector('.toggle');
            let navigation = document.querySelector('.navigation');
            let content = document.getElementById('admin-content');
            let main = document.querySelector('.main');
            
            if (toggle && navigation && content) {
                toggle.onclick = function() {
                    // Toggle active class on navigation
                    navigation.classList.toggle('active');
                    // Toggle expanded class on content
                    content.classList.toggle('expanded');
                    
                    // Toggle icon if it exists
                    const icon = toggle.querySelector('i');
                    if (icon) {
                        if (navigation.classList.contains('active')) {
                            icon.classList.remove('bx-menu');
                            icon.classList.add('bx-x');
                        } else {
                            icon.classList.remove('bx-x');
                            icon.classList.add('bx-menu');
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>