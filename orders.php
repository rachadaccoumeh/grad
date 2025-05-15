<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root"; // Change as needed
$password = ""; // Change as needed
$dbname = "roomgenius_db"; // Change as needed

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update order status if requested
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];
    
    $sql = "UPDATE orders SET order_status = '$status' WHERE id = $orderId";
    $conn->query($sql);
}

// Update payment status if requested
if (isset($_POST['order_id']) && isset($_POST['payment_status'])) {
    $orderId = $_POST['order_id'];
    $paymentStatus = $_POST['payment_status'];
    
    $sql = "UPDATE orders SET payment_status = '$paymentStatus' WHERE id = $orderId";
    $conn->query($sql);
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
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <title>Admin Orders Dashboard</title>
    <style>
        .orders-container {
            width: 100%;
            padding: 20px;
            margin-left: 300px;
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
            background-color: #5cb85c;
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
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="">
                        <span class="icon"><i class="fas fa-brain"></i> <i class="fas fa-couch"></i></span>
                        <span class="title">RoomGenius</span>
                    </a>
                </li>
                <li>
                    <a href="users.php">
                        <span class="icon"><i class='bx bx-group'></i></span>
                        <span class="title">Users</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <span class="icon"><i class='bx bx-buildings'></i></span>
                        <span class="title">Companies</span>
                    </a>
                </li>
                <li>
                    <a href="message.php">
                        <span class="icon"><i class='bx bx-message'></i></span>
                        <span class="title">Messages</span>
                    </a>
                </li>
                <li>
                    <a href="category_item.php">
                        <span class="icon"><i class='bx bx-basket'></i></span>
                        <span class="title">Category items</span>
                    </a>
                </li>
                <li>
                    <a href="product.php">
                        <span class="icon"><i class='bx bx-box'></i></span>
                        <span class="title">Product</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php">
                        <span class="icon"><i class='bx bx-receipt'></i></span>
                        <span class="title">Orders</span>
                    </a>
                </li>
                <li>
                    <a href="adminLogout.php">
                        <span class="icon"><i class='bx bx-log-out'></i></span>
                        <span class="title">Sign out</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="orders-container">
            <h2>Orders Dashboard</h2>
            
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
                                <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                                <td>$<?php echo $row['total']; ?></td>
                                <td><?php echo $row['payment_method']; ?></td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" 
                                            class="status-btn <?php echo $row['order_status'] === 'Pending' ? 'status-pending' : 'status-delivered'; ?>"
                                            name="status" 
                                            value="<?php echo $row['order_status'] === 'Pending' ? 'Delivered' : 'Pending'; ?>">
                                            <?php echo $row['order_status']; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" 
                                            class="payment-btn <?php echo $row['payment_status'] === 'Paid' ? 'payment-paid' : 'payment-unpaid'; ?>"
                                            name="payment_status" 
                                            value="<?php echo $row['payment_status'] === 'Paid' ? 'Unpaid' : 'Paid'; ?>">
                                            <?php echo $row['payment_status']; ?>
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
    
    <script>
        // View order details
        function viewOrderDetails(orderId) {
            fetch('get_order_details.php?id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrderDetails(data.order, data.items);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching order details.');
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
            
            // Add order items
            items.forEach(item => {
                const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
                html += `
                    <tr>
                        <td>
                            <div class="item-image">
                                <img src="${item.image}" alt="${item.product_name}">
                            </div>
                        </td>
                        <td>${item.product_name}</td>
                        <td>$${parseFloat(item.price).toFixed(2)}</td>
                        <td>${item.quantity}</td>
                        <td>$${itemTotal.toFixed(2)}</td>
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
</body>
</html>