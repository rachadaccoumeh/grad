<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') { 
  header("Location: index.php");
  exit; 
}

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
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
                    <a href="product.php" class="active">
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

        <div class="main">
            <div class="topbar">
                <div class="toggle">
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

    <script>
    // Toggle sidebar
    let toggle = document.querySelector('.toggle');
    let navigation = document.querySelector('.navigation');
    let main = document.querySelector('.main');

    toggle.onclick = function() {
        navigation.classList.toggle('active');
        main.classList.toggle('active');
    }
    </script>
</body>

</html>
