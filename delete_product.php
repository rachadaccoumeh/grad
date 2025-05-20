<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') { 
  header("Location: index.php");
  exit; 
}

// Set the current page for the sidebar
$current_page = 'product';

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = "root123"; // Replace with your database password
$dbname = "roomgenius_db"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$product_id = $name = $image_path = "";

// Process form data when form is submitted for deletion
if (isset($_POST["submit"])) {
    $product_id = $_POST["product_id"];
    
    if (!empty($product_id)) {
        // First, get the image path to delete the image file
        $image_path = $_POST["image_path"];
        
        // Delete the product from database
        $sql = "DELETE FROM products WHERE id='$product_id'";
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            // Delete the image file if it exists
            if (!empty($image_path) && file_exists($image_path)) {
                unlink($image_path);
            }
            
            // Set success message and redirect
            $_SESSION['success_message'] = "Product deleted successfully!";
            header("Location: product.php");
            exit;
        } else {
            echo 'Error: ' . mysqli_error($conn);
        }
    }
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="product.css">
    <title>Delete Product</title>
    <style>
        /* Define CSS variables for dynamic values */
        :root {
            --admin-content-margin: 250px;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }
        
        /* Fix for admin wrapper layout */
        #admin-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Navigation styles */
        .navigation {
            width: var(--sidebar-width);
            transition: width 0.3s ease;
            position: fixed;
            height: 100%;
            z-index: 1000;
        }
        
        /* Collapsed navigation */
        .navigation.active {
            width: var(--sidebar-collapsed-width) !important;
        }
        
        /* Hide text in navigation when collapsed */
        .navigation.active .title {
            display: none;
        }
        
        #admin-content {
            flex: 1;
            margin-left: var(--admin-content-margin); /* Match the width of the navigation */
            transition: margin-left 0.3s ease, width 0.3s ease;
            width: calc(100% - var(--admin-content-margin));
            overflow-x: hidden;
        }
        
        /* When navigation is active (collapsed) */
        .navigation.active + #admin-content {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }
        
        /* Override any absolute positioning in main */
        .main {
            position: relative !important;
            left: 0 !important;
            width: 100% !important;
            margin-left: 0 !important;
        }
        
        .delete-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .product-preview {
            margin-bottom: 20px;
            text-align: center;
        }
        .product-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            object-fit: cover;
        }
        .confirm-text {
            color: #dc3545;
            font-weight: 500;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            margin-right: 10px;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .action-buttons a, .action-buttons button {
            padding: 8px 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .action-buttons a:hover, .action-buttons button:hover {
            opacity: 0.9;
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
                    <div class="toggle" onclick="toggleSidebar()">
                        <i class='bx bx-menu'></i>
                    </div>
                </div>
                
                <div class="delete-container">
                    <h2>Delete Product</h2>
                    
                    <?php
                    if (isset($_GET['id'])) {
                        $product_id = $_GET['id'];
                        $sql = "SELECT * FROM products WHERE id='$product_id'";
                        $result = mysqli_query($conn, $sql);
                        
                        if (mysqli_num_rows($result) > 0) {
                            $product = mysqli_fetch_array($result);
                    ?>
                <div class="product-preview">
                    <img src="<?= $product["image_path"]; ?>" alt="<?= $product["name"]; ?>">
                    <h3><?= $product["name"]; ?></h3>
                    <p>Category: <?= $product["category"]; ?></p>
                    <p>Price: $<?= number_format($product["price"], 2); ?></p>
                </div>
                
                <p class="confirm-text">Are you sure you want to delete this product? This action cannot be undone.</p>
                
                <form action="delete_product.php" method="post">
                    <input type="hidden" name="product_id" value="<?= $product["id"]; ?>">
                    <input type="hidden" name="image_path" value="<?= $product["image_path"]; ?>">
                    
                    <div class="action-buttons">
                        <a href="product.php" class="btn-cancel">Cancel</a>
                        <button type="submit" name="submit" class="btn-delete">Delete Product</button>
                    </div>
                </form>
                <?php
                    } else {
                        echo '<p class="confirm-text">No product found with this ID.</p>';
                        echo '<div class="action-buttons"><a href="product.php" class="btn-cancel">Back to Products</a></div>';
                    }
                } else {
                    echo '<p class="confirm-text">No product ID provided.</p>';
                    echo '<div class="action-buttons"><a href="product.php" class="btn-cancel">Back to Products</a></div>';
                }
                ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Toggle sidebar function for the burger menu
    function toggleSidebar() {
        const navigation = document.querySelector('.navigation');
        const main = document.querySelector('.main');
        const adminContent = document.querySelector('#admin-content');
        
        if (navigation) navigation.classList.toggle('active');
        if (main) main.classList.toggle('active');
        
        // Force immediate style update for admin content
        if (adminContent) {
            // Apply transition for smooth animation
            adminContent.style.transition = 'margin-left 0.3s ease, width 0.3s ease';
            
            if (navigation && navigation.classList.contains('active')) {
                // When sidebar is collapsed
                adminContent.style.marginLeft = '70px';
                adminContent.style.width = 'calc(100% - 70px)';
                document.documentElement.style.setProperty('--admin-content-margin', '70px');
            } else {
                // When sidebar is expanded
                adminContent.style.marginLeft = '250px';
                adminContent.style.width = 'calc(100% - 250px)';
                document.documentElement.style.setProperty('--admin-content-margin', '250px');
            }
        }
        
        return false;
    }
    </script>
</body>

</html>
