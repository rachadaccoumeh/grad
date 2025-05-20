<?php
// Start the session to maintain admin login status
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not admin
    header('Location: login.php');
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Adjust if needed
$dbname = "roomgenius_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = $conn->real_escape_string($_POST['order_id']);
    $newStatus = $conn->real_escape_string($_POST['status']);
    $newPaymentStatus = isset($_POST['payment_status']) ? $conn->real_escape_string($_POST['payment_status']) : null;
    
    // Update order status
    $updateSql = "UPDATE orders SET status = '$newStatus'";
    
    // Only update payment status if it was provided
    if ($newPaymentStatus) {
        $updateSql .= ", payment_status = '$newPaymentStatus'";
    }
    
    $updateSql .= " WHERE order_id = '$orderId'";
    
    if ($conn->query($updateSql) === TRUE) {
        $statusMessage = "Order status updated successfully!";
    } else {
        $statusMessage = "Error updating status: " . $conn->error;
    }
}

// Get all orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Number of orders per page
$offset = ($page - 1) * $perPage;

// Get total number of orders for pagination
$countSql = "SELECT COUNT(*) as total FROM orders";
$countResult = $conn->query($countSql);
$totalOrders = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $perPage);

// Get orders for current page with newest first
$sql = "SELECT * FROM orders ORDER BY created_at DESC LIMIT $offset, $perPage";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Order Management - RoomGenius</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: #c9b99e;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        
        .logo {
            font-weight: bold;
            font-size: 24px;
            color: #24424c;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 10px;
        }
        
        .admin-nav {
            display: flex;
            gap: 20px;
        }
        
        .admin-nav a {
            color: #24424c;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background-color: #24424c;
            color: white;
        }
        
        .page-title {
            font-size: 28px;
            margin-bottom: 20px;
            color: #24424c;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            margin-right: 15px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .orders-table th, .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .orders-table th {
            background-color: #24424c;
            color: white;
            font-weight: bold;
        }
        
        .orders-table tr:last-child td {
            border-bottom: none;
        }
        
        .orders-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-pending {
            padding: 5px 10px;
            background-color: #ffeeba;
            color: #856404;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        
        .status-completed {
            padding: 5px 10px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        
        .status-cancelled {
            padding: 5px 10px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        
        .payment-pending {
            padding: 5px 10px;
            background-color: #ffeeba;
            color: #856404;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        
        .payment-completed {
            padding: 5px 10px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        
        .view-btn {
            background-color: #24424c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
        }
        
        .view-btn:hover {
            background-color: #1b323a;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            gap: 5px;
        }
        
        .pagination li a, .pagination li span {
            padding: 8px 16px;
            text-decoration: none;
            background-color: white;
            border: 1px solid #ddd;
            color: #24424c;
            border-radius: 5px;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .pagination li.active span {
            background-color: #24424c;
            color: white;
            border-color: #24424c;
        }
        
        .pagination li a:hover {
            background-color: #f5f5f5;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .modal-title {
            font-size: 22px;
            color: #24424c;
        }
        
        .close-btn {
            font-size: 24px;
            cursor: pointer;
            color: #666;
            background: none;
            border: none;
        }
        
        .order-details {
            margin-bottom: 25px;
        }
        
        .order-details h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #24424c;
            padding-bottom: 8px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 3px;
            font-size: 14px;
        }
        
        .detail-value {
            color: #333;
        }
        
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .item-table th, .item-table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }
        
        .item-table th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: bold;
        }
        
        .status-form {
            margin-top: 25px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .update-btn {
            background-color: #24424c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .update-btn:hover {
            background-color: #1b323a;
        }
        
        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .orders-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <header>
        <a href="index.html" class="logo">
            <i class="fas fa-brain"></i>
            <i class="fas fa-couch"></i>
            RoomGenius Admin
        </a>
        <nav class="admin-nav">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_products.php">Products</a>
            <a href="admin_orders.php" class="active">Orders</a>
            <a href="admin_customers.php">Customers</a>
            <a href="admin_messages.php">Messages</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    
    <div class="container">
        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Order Management</h1>
        
        <?php if (isset($statusMessage)): ?>
            <div class="alert <?php echo strpos($statusMessage, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?>">
                <?php echo $statusMessage; ?>
            </div>
        <?php endif; ?>
        
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td>$<?php echo number_format($row['total'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <span class="status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['payment_method'] == 'Cash on Delivery'): ?>
                                    <?php if (isset($row['payment_status']) && $row['payment_status'] == 'paid'): ?>
                                        <span class="payment-completed">Paid</span>
                                    <?php else: ?>
                                        <span class="payment-pending">Pending</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="payment-completed">Paid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="view-btn" onclick="showOrderDetails('<?php echo $row['order_id']; ?>')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No orders found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li><a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a></li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                        <?php if ($i == $page): ?>
                            <span><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li><a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Order Details</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div id="orderModalContent">
                <!-- Order details will be loaded here via AJAX -->
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 30px; color: #24424c;"></i>
                    <p>Loading order details...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Function to show order details modal
        function showOrderDetails(orderId) {
            // Show the modal
            document.getElementById('orderModal').style.display = 'block';
            
            // Fetch order details using AJAX
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('orderModalContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('orderModalContent').innerHTML = `
                        <div class="alert alert-danger">
                            Error loading order details: ${error}
                        </div>
                    `;
                });
        }
        
        // Function to close the modal
        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
