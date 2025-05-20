<?php 
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') { 
    header("Location: index.php");
    exit;
} 

// Set the current page for the sidebar
$current_page = 'dashboard';

// Database connection
require_once 'db.php'; // Include the database connection file

// Get total users count
$usersQuery = "SELECT COUNT(*) as total_users FROM users WHERE role = 'customer'";  
$usersResult = $conn->query($usersQuery);
$totalUsers = $usersResult->fetch_assoc()['total_users'];

// Get total products count
$productsQuery = "SELECT COUNT(*) as total_products FROM products";
$productsResult = $conn->query($productsQuery);
$totalProducts = $productsResult->fetch_assoc()['total_products'];

// Get total orders count
$ordersQuery = "SELECT COUNT(*) as total_orders FROM orders";
$ordersResult = $conn->query($ordersQuery);
$totalOrders = $ordersResult->fetch_assoc()['total_orders'];

// Get total custom requests count
$requestsQuery = "SELECT COUNT(*) as total_requests FROM custom_requests";
$requestsResult = $conn->query($requestsQuery);
$totalRequests = $requestsResult->fetch_assoc()['total_requests'];

// Removed unread messages count as per request

// Get total revenue
$revenueQuery = "SELECT SUM(total) as total_revenue FROM orders";
$revenueResult = $conn->query($revenueQuery);
$totalRevenue = $revenueResult->fetch_assoc()['total_revenue'] ?? 0;

// Get products by category
$categoryQuery = "SELECT category, COUNT(*) as count FROM products GROUP BY category";
$categoryResult = $conn->query($categoryQuery);
$categoryData = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categoryData[$row['category']] = $row['count'];
}
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
    <title>Admin Dashboard</title>
    <style>
        /* Reset all styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body, html {
            width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
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
        .content {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
            padding: 20px !important;
            box-sizing: border-box !important;
        }
        
        /* Override any existing styles */
        .main, .container {
            all: unset !important;
            display: contents !important;
        }
    </style>
</head>
<body>
    <!-- Custom wrapper for admin layout -->
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
            
            <!-- Main content area -->
            <div class="content">
                <h1>Dashboard Overview</h1>
                
                <!-- Dashboard stats cards - First row (3 cards) -->
                <div class="dashboard-stats">
                    <!-- Total Customers Card -->
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class='bx bx-user'></i>
                        </div>
                        <div class="stat-card-info">
                            <h3><?php echo $totalUsers; ?></h3>
                            <p>Total Customers</p>
                        </div>
                    </div>
                    
                    <!-- Total Products Card -->
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class='bx bx-box'></i>
                        </div>
                        <div class="stat-card-info">
                            <h3><?php echo $totalProducts; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    
                    <!-- Total Orders Card -->
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class='bx bx-package'></i>
                        </div>
                        <div class="stat-card-info">
                            <h3><?php echo $totalOrders; ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                </div>
                
                <!-- Second row of stats (2 cards) -->
                <div class="dashboard-stats">
                    <!-- Total Revenue Card -->
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class='bx bx-dollar'></i>
                        </div>
                        <div class="stat-card-info">
                            <h3>$<?php echo number_format($totalRevenue, 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    
                    <!-- Custom Requests Card -->
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class='bx bx-customize'></i>
                        </div>
                        <div class="stat-card-info">
                            <h3><?php echo $totalRequests; ?></h3>
                            <p>Custom Requests</p>
                        </div>
                    </div>
                </div>
                
                <!-- Products by Category -->
                <div class="category-distribution">
                    <h2>Products by Category</h2>
                    <div class="category-bars">
                        <?php foreach ($categoryData as $category => $count): ?>
                        <div class="category-item">
                            <div class="category-label"><?php echo $category; ?></div>
                            <div class="category-bar-container">
                                <div class="category-bar" style="width: <?php echo min(100, ($count / $totalProducts) * 100); ?>%">
                                    <?php echo $count; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.querySelector('.toggle');
            const navigation = document.querySelector('.navigation');
            const main = document.querySelector('.main');
            const icon = toggle ? toggle.querySelector('i') : null;
            
            // Toggle sidebar function
            function toggleSidebar() {
                // Toggle active class on navigation
                navigation.classList.toggle('active');
                
                // Toggle expanded class on content container
                const content = document.getElementById('admin-content');
                if (content) {
                    content.classList.toggle('expanded');
                }
                
                // Toggle the menu icon between menu and x
                if (icon) {
                    if (navigation.classList.contains('active')) {
                        icon.classList.remove('bx-menu');
                        icon.classList.add('bx-x');
                        // Hide text in navigation items
                        document.querySelectorAll('.navigation .title').forEach(title => {
                            title.style.display = 'none';
                        });
                    } else {
                        icon.classList.remove('bx-x');
                        icon.classList.add('bx-menu');
                        // Show text in navigation items
                        document.querySelectorAll('.navigation .title').forEach(title => {
                            title.style.display = 'block';
                        });
                    }
                }
            }
            
            // Add click event to toggle button
            if (toggle) {
                toggle.addEventListener('click', toggleSidebar);
            }
            
            // Add hovered class to selected list item
            const list = document.querySelectorAll('.navigation li:not(:first-child)');
            
            function activeLink() {
                list.forEach((item) => {
                    item.classList.remove('hovered');
                });
                this.classList.add('hovered');
            }
            
            // Handle list item interactions
            list.forEach((item) => {
                item.addEventListener('mouseover', activeLink);
                
                // Handle click on mobile
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        toggleSidebar();
                    }
                });
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992 && 
                    !navigation.contains(event.target) && 
                    !toggle.contains(event.target) &&
                    navigation.classList.contains('active')) {
                    toggleSidebar();
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    // Reset styles on desktop
                    navigation.classList.remove('active');
                    main.classList.remove('expanded');
                    if (icon) {
                        icon.classList.remove('bx-x');
                        icon.classList.add('bx-menu');
                        document.querySelectorAll('.navigation .title').forEach(title => {
                            title.style.display = 'block';
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>
