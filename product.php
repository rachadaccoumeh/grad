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
require_once 'db_connect.php';
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for form data and error messages
$product_id = $name = $description = $price = $style = $category = $size = $stock_quantity = $is_featured = "";
$product_id_err = $name_err = $description_err = $price_err = $style_err = $category_err = $size_err = $stock_err = $image_err = "";
$success_message = $error_message = "";

// Set active tab - default to add-product unless manage-products is specifically requested
$active_tab = "add-product";
if (isset($_GET['tab']) && $_GET['tab'] == "manage-products") {
  $active_tab = "manage-products";
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  // Validate product ID
  if (empty(trim($_POST["product_id"]))) {
    $product_id_err = "Please enter a product ID.";
  } else {
    // Check if product ID already exists
    $sql = "SELECT id FROM products WHERE product_id = ?";
    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("s", $param_product_id);
      $param_product_id = trim($_POST["product_id"]);
      
      if ($stmt->execute()) {
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
          $product_id_err = "This product ID is already taken.";
        } else {
          $product_id = trim($_POST["product_id"]);
        }
      } else {
        $error_message = "Oops! Something went wrong. Please try again later.";
      }
      $stmt->close();
    }
  }
  
  // Validate name
  if (empty(trim($_POST["name"]))) {
    $name_err = "Please enter a product name.";
  } else {
    $name = trim($_POST["name"]);
  }
  
  // Validate description
  if (empty(trim($_POST["description"]))) {
    $description_err = "Please enter a product description.";
  } else {
    $description = trim($_POST["description"]);
  }
  
  // Validate price
  if (empty(trim($_POST["price"]))) {
    $price_err = "Please enter a price.";
  } elseif (!is_numeric(trim($_POST["price"])) || floatval(trim($_POST["price"])) <= 0) {
    $price_err = "Please enter a valid price.";
  } else {
    $price = trim($_POST["price"]);
  }
  
  // Validate style
  if (empty($_POST["style"])) {
    $style_err = "Please select a style.";
  } else {
    $style = $_POST["style"];
  }
  
  // Validate category
  if (empty($_POST["category"])) {
    $category_err = "Please select a category.";
  } else {
    $category = $_POST["category"];
  }
  
  // Validate size
  if (empty($_POST["size"])) {
    $size_err = "Please select a size.";
  } else {
    $size = $_POST["size"];
  }
  
  // Validate stock quantity
  if (empty(trim($_POST["stock_quantity"]))) {
    $stock_err = "Please enter stock quantity.";
  } elseif (!is_numeric(trim($_POST["stock_quantity"])) || intval(trim($_POST["stock_quantity"])) < 0) {
    $stock_err = "Please enter a valid stock quantity.";
  } else {
    $stock_quantity = trim($_POST["stock_quantity"]);
  }
  
  // Set featured status
  $is_featured = isset($_POST["is_featured"]) ? 1 : 0;
  
  // Process image upload
  $image_path = "";
  $upload_dir = "photos/";
  
  if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
    $allowed_types = ["image/jpeg", "image/png", "image/jpg"];
    $file_type = $_FILES["product_image"]["type"];
    
    if (in_array($file_type, $allowed_types)) {
      $file_name = time() . '_' . basename($_FILES["product_image"]["name"]);
      $target_file = $upload_dir . $file_name;
      
      if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        $image_path = $target_file;
      } else {
        $image_err = "Failed to upload image.";
      }
    } else {
      $image_err = "Only JPG, JPEG, and PNG files are allowed.";
    }
  } else {
    $image_err = "Please select an image.";
  }
  
  // Check input errors before inserting in database
  if (empty($product_id_err) && empty($name_err) && empty($description_err) && empty($price_err) && 
      empty($style_err) && empty($category_err) && empty($size_err) && empty($stock_err) && empty($image_err)) {
    
    // Prepare an insert statement with current timestamp
    $sql = "INSERT INTO products (product_id, name, description, price, style, category, size, image_path, stock_quantity, is_featured, date_added) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    if ($stmt = $conn->prepare($sql)) {
      // Set parameters
      $param_product_id = $product_id;
      $param_name = $name;
      $param_description = $description;
      $param_price = $price;
      $param_style = $style;
      $param_category = $category;
      $param_size = $size;
      $param_image_path = $image_path;
      $param_stock_quantity = $stock_quantity;
      $param_is_featured = $is_featured;
      
      // Bind variables to the prepared statement as parameters
      $stmt->bind_param("sssdssssii", 
          $param_product_id, 
          $param_name, 
          $param_description, 
          $param_price,
          $param_style, 
          $param_category, 
          $param_size, 
          $param_image_path, 
          $param_stock_quantity, 
          $param_is_featured
      );
      
      // Attempt to execute the prepared statement
      if ($stmt->execute()) {
        $success_message = "Product added successfully!";
        // Clear form fields after successful submission
        $product_id = $name = $description = $price = $style = $category = $stock_quantity = $is_featured = "";
        
        // Redirect to manage products tab after successful addition
        $active_tab = "manage-products";
      } else {
        $error_message = "Something went wrong. Please try again later.";
      }
      
      // Close statement
      $stmt->close();
    }
  } else {
    $error_message = "Please correct the errors and try again.";
  }
}

// Initialize search variables
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
  $search_query = trim($_GET['search']);
  // If search is being performed, set active tab to manage-products
  $active_tab = "manage-products";
}

// Fetch products with search functionality
if (!empty($search_query)) {
  // Prepare the search query
  $sql = "SELECT * FROM products WHERE 
          product_id LIKE ? OR 
          name LIKE ? OR 
          description LIKE ? OR 
          category LIKE ? OR 
          style LIKE ? 
          ORDER BY created_at DESC";
          
  if ($stmt = $conn->prepare($sql)) {
    $search_param = "%$search_query%";
    $stmt->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
  }
} else {
  // Fetch all products for display
  $sql = "SELECT * FROM products ORDER BY created_at DESC";
  $result = $conn->query($sql);
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Admin Panel</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="product.css">
    <style>
        /* Ensure icons are properly sized and aligned */
        .navigation ul li a .icon {
            min-width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
        }
        .navigation ul li a .icon i {
            font-size: 1.25em;
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
                <div class="search" id="search-container">
                    <form id="search-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
                        <label>
                            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <input type="hidden" name="tab" value="manage-products">
                        </label>
                    </form>
                </div>
                <div class="user">
                    <img src="photos/adminphoto.JPG" alt="Admin">
                </div>
            </div>

            <div class="product-content">
                <div class="product-header">
                    <h2>Product Management</h2>
                    <div class="tabs">
                        <button class="tab-btn <?php echo $active_tab == 'add-product' ? 'active' : ''; ?>" data-tab="add-product">Add Product</button>
                        <button class="tab-btn <?php echo $active_tab == 'manage-products' ? 'active' : ''; ?>" data-tab="manage-products">Manage Products</button>
                    </div>
                </div>

                <?php if(!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($search_query)): ?>
                    <div class="search-results">
                        <h3>Search Results for: "<?php echo htmlspecialchars($search_query); ?>"</h3>
                        <a href="product.php?tab=manage-products" class="clear-search">Clear Search</a>
                    </div>
                <?php endif; ?>

                <div class="tab-content <?php echo $active_tab == 'add-product' ? 'active' : ''; ?>" id="add-product">
                    <div class="form-card">
                        <h3>Add New Product</h3>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="product_id">Product ID</label>
                                <input type="text" id="product_id" name="product_id" value="<?php echo $product_id; ?>" placeholder="Enter product ID">
                                <span class="error"><?php echo $product_id_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="name">Product Name</label>
                                <input type="text" id="name" name="name" value="<?php echo $name; ?>" placeholder="Enter product name">
                                <span class="error"><?php echo $name_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" placeholder="Enter product description"><?php echo $description; ?></textarea>
                                <span class="error"><?php echo $description_err; ?></span>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="price">Price ($)</label>
                                    <input type="number" id="price" name="price" value="<?php echo $price; ?>" placeholder="Enter price" step="0.01">
                                    <span class="error"><?php echo $price_err; ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="stock_quantity">Stock Quantity</label>
                                    <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo $stock_quantity; ?>" placeholder="Enter stock quantity">
                                    <span class="error"><?php echo $stock_err; ?></span>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="style">Style</label>
                                    <select id="style" name="style">
                                        <option value="" <?php echo empty($style) ? 'selected' : ''; ?>>Select Style</option>
                                        <option value="Modern" <?php echo $style == 'Modern' ? 'selected' : ''; ?>>Modern</option>
                                        <option value="Traditional" <?php echo $style == 'Traditional' ? 'selected' : ''; ?>>Traditional</option>
                                        <option value="Rustic" <?php echo $style == 'Rustic' ? 'selected' : ''; ?>>Rustic</option>
                                        <option value="Minimalist" <?php echo $style == 'Minimalist' ? 'selected' : ''; ?>>Minimalist</option>
                                        <option value="Industrial" <?php echo $style == 'Industrial' ? 'selected' : ''; ?>>Industrial</option>
                                        <option value="Classic" <?php echo $style == 'Classic' ? 'selected' : ''; ?>>Classic</option>
                                        <option value="Luxury" <?php echo $style == 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                                        <option value="Bohemian" <?php echo $style == 'Bohemina' ? 'selected' : ''; ?>>Bohemian</option>
                                        <option value="Scandinavian" <?php echo $style == 'Scandinavian' ? 'selected' : ''; ?>>Scandinavian</option>
                                         <option value="Executive" <?php echo $style == 'Executive' ? 'selected' : ''; ?>>Executive</option>
                                    </select>
                                    <span class="error"><?php echo $style_err; ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select id="category" name="category" onchange="updateSizeOptions()">
                                        <option value="" <?php echo empty($category) ? 'selected' : ''; ?>>Select Category</option>
                                        <option value="Kitchen" <?php echo $category == 'Kitchen' ? 'selected' : ''; ?>>Kitchen</option>
                                        <option value="Living Room" <?php echo $category == 'Living Room' ? 'selected' : ''; ?>>Living Room</option>
                                        <option value="Bedroom" <?php echo $category == 'Bedroom' ? 'selected' : ''; ?>>Bedroom</option>
                                        <option value="Office" <?php echo $category == 'Office' ? 'selected' : ''; ?>>Office</option>
                                        <option value="Dining Room" <?php echo $category == 'Dining Room' ? 'selected' : ''; ?>>Dining Room</option>
                                        <option value="Bathroom" <?php echo $category == 'Bathroom' ? 'selected' : ''; ?>>Bathroom</option>
                                        <option value="Game Room" <?php echo $category == 'Game Room' ? 'selected' : ''; ?>>Game Room</option>
                                        <option value="Gym" <?php echo $category == 'Gym' ? 'selected' : ''; ?>>Gym</option>
                                        <option value="Prayer Room" <?php echo $category == 'Prayer Room' ? 'selected' : ''; ?>>Prayer Room</option>
                                        <option value="Garden" <?php echo $category == 'Garden' ? 'selected' : ''; ?>>Garden</option>
                                        <option value="Workshop" <?php echo $category == 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
                                        <option value="Closet" <?php echo $category == 'Closet' ? 'selected' : ''; ?>>Closet</option>
                                    </select>
                                    <span class="error"><?php echo $category_err; ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="size">Size</label>
                                <select id="size" name="size">
                                    <option value="">Select a category first</option>
                                </select>
                                <span class="error"><?php echo $size_err; ?></span>
                            </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="product_image">Product Image</label>
                                <div id="image-preview" style="margin-bottom: 10px; display: none; position: relative; display: inline-block;">
                                    <img id="preview" src="#" alt="Preview" style="max-width: 200px; max-height: 200px; display: none;">
                                    <button type="button" class="remove-image-btn" style="display: none;" onclick="removeImage()">
                                        <i class='bx bx-x'></i>
                                    </button>
                                </div>
                                <div class="file-upload">
                                    <input type="file" id="product_image" name="product_image" accept="image/*" onchange="previewImage(this)">
                                    <label for="product_image"><i class='bx bx-upload'></i> Choose File</label>
                                    <span id="file-chosen">No file chosen</span>
                                </div>
                                <span class="error"><?php echo $image_err; ?></span>
                            </div>
                            
                            <div class="form-group checkbox">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo $is_featured ? 'checked' : ''; ?>>
                                <label for="is_featured">Featured Product</label>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" class="btn-submit">Add Product</button>
                                <button type="reset" class="btn-reset">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-content <?php echo $active_tab == 'manage-products' ? 'active' : ''; ?>" id="manage-products">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Featured</th>
                                    <th>Date Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row["product_id"] . "</td>";
                                        echo "<td class='product-image'><img src='" . $row["image_path"] . "' alt='" . $row["name"] . "'></td>";
                                        echo "<td>" . $row["name"] . "</td>";
                                        echo "<td>" . $row["category"] . "</td>";
                                        echo "<td>$" . number_format($row["price"], 2) . "</td>";
                                        echo "<td>" . $row["stock_quantity"] . "</td>";
                                        echo "<td>" . ($row["is_featured"] ? "Yes" : "No") . "</td>";
                                        echo "<td>" . date("M d, Y", strtotime($row["date_added"])) . "</td>";
                                        echo "<td class='actions'>";
                                        echo "<button class='edit' onclick=\"editProduct(" . $row["id"].")\"><i class='bx bx-edit'></i></button>";
                                        echo "<button class='delete'onclick=\"deleteProduct(" . $row["id"].")\"><i class='bx bx-trash'></i></button>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='no-data'>No products found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Close the main div -->
            </div>
        </div>
    </div>

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
        
        /* Override any existing styles */
        .main, .container {
            all: unset !important;
            display: contents !important;
        }
        
        /* Make sure the toggle button works */
        .toggle {
            cursor: pointer !important;
            font-size: 24px !important;
        }
        
        /* Content container */
        .product-content {
            padding: 20px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .navigation.active ~ .main,
        .navigation.active + .main {
            left: 70px;
            width: calc(100% - 70px);
        }
        
        .product-content {
            width: 100%;
            padding: 20px;
            margin: 0;
        }
        
        .table-container {
            width: 100%;
            overflow-x: auto;
        }
        
        .table-container table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }
        
        .remove-image-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 24px;
            height: 24px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
            font-size: 14px;
            line-height: 1;
            opacity: 0.9;
            transition: opacity 0.2s;
        }
        .remove-image-btn:hover {
            opacity: 1;
            background: #ff4757;
        }
        #image-preview {
            margin-bottom: 10px;
            display: inline-block;
            position: relative;
        }
        #preview {
            max-width: 200px;
            max-height: 200px;
            display: none;
            border-radius: 4px;
        }
    </style>
    <script>
        // Image preview and removal functions
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const fileChosen = document.getElementById('file-chosen');
            const removeBtn = document.querySelector('.remove-image-btn');
            const imagePreview = document.getElementById('image-preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    fileChosen.textContent = input.files[0].name;
                    removeBtn.style.display = 'block';
                    imagePreview.style.display = 'inline-block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function removeImage() {
            const input = document.getElementById('product_image');
            const preview = document.getElementById('preview');
            const fileChosen = document.getElementById('file-chosen');
            const removeBtn = document.querySelector('.remove-image-btn');
            const imagePreview = document.getElementById('image-preview');
            
            // Reset the file input
            input.value = '';
            
            // Hide preview and reset UI
            preview.style.display = 'none';
            fileChosen.textContent = 'No file chosen';
            removeBtn.style.display = 'none';
            imagePreview.style.display = 'none';
        }
        
        // Size options mapping
        const sizeOptions = {
            'Kitchen': ['Small', 'Medium', 'Large'],
            'Living Room': ['Compact', 'Standard', 'Spacious'],
            'Bedroom': ['Single', 'Double', 'Queen', 'King'],
            'Office': ['Small', 'Medium', 'Large', 'Executive'],
            'Dining Room': ['2-4 Seating', '4-6 Seating', '6-8 Seating', '8+ Seating'],
            'Bathroom': ['Small', 'Medium', 'Large', 'Master'],
            'Game Room': ['Compact', 'Standard', 'Deluxe'],
            'Gym': ['Home', 'Professional', 'Commercial'],
            'Prayer Room': ['Individual', 'Family', 'Community'],
            'Garden': ['Small', 'Medium', 'Large'],
            'Workshop': ['Basic', 'Standard', 'Professional'],
            'Closet': ['Walk-in', 'Reach-in', 'Wardrobe']
        };

        // Update size options based on selected category
        function updateSizeOptions() {
            const categorySelect = document.getElementById('category');
            const sizeSelect = document.getElementById('size');
            const selectedCategory = categorySelect.value;
            
            // Clear existing options
            sizeSelect.innerHTML = '<option value="">Select size</option>';
            
            if (selectedCategory && sizeOptions[selectedCategory]) {
                // Add size options for the selected category
                sizeOptions[selectedCategory].forEach(size => {
                    const option = document.createElement('option');
                    option.value = size;
                    option.textContent = size;
                    sizeSelect.appendChild(option);
                });
            } else {
                sizeSelect.innerHTML = '<option value="">Select a category first</option>';
            }
        }
        
        // Initialize size options if category is already selected
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category');
            if (categorySelect.value) {
                updateSizeOptions();
                // Set the previously selected size if it exists
                const sizeSelect = document.getElementById('size');
                if ('<?php echo $size; ?>') {
                    sizeSelect.value = '<?php echo $size; ?>';
                }
            }
        });

        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get DOM elements
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
                    navigation.classList.contains('collapsed')) {
                    toggleSidebar();
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    // Reset styles on desktop
                    navigation.classList.remove('collapsed');
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
        
        // Tab functionality
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all buttons and contents
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked button and corresponding content
                btn.classList.add('active');
                document.getElementById(btn.getAttribute('data-tab')).classList.add('active');
                
                // Update URL to maintain tab state
                const tabName = btn.getAttribute('data-tab');
                if (tabName === 'manage-products') {
                    // Only add the tab parameter for manage-products
                    history.replaceState(null, null, '?tab=manage-products');
                } else {
                    // For add-product, remove the tab parameter
                    history.replaceState(null, null, 'product.php');
                }
                
                // Show/hide search box based on active tab
                const searchContainer = document.getElementById('search-container');
                if (tabName === 'manage-products') {
                    searchContainer.style.display = 'block';
                } else {
                    searchContainer.style.display = 'none';
                }
            });
        });
        
        // File upload input
        const fileInput = document.getElementById('product_image');
        const fileChosen = document.getElementById('file-chosen');
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileChosen.textContent = this.files[0].name;
            } else {
                fileChosen.textContent = 'No file chosen';
            }
        });
        
        // Function to edit product
        function editProduct(id) {
            // Make sure we're using the correct path
            window.location.href = "edit_product.php?id="+id;
        }
        
        // Function to delete product
        function deleteProduct(id) {
            if (confirm("Are you sure you want to delete this product?")) {
                // Redirect to delete product script with the product ID
                window.location.href = "delete_product.php?id=" + id;
            }
        }
        
        // Hide or show search box based on active tab on page load
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = document.querySelector('.tab-btn.active').getAttribute('data-tab');
            const searchContainer = document.getElementById('search-container');
            
            if (activeTab === 'manage-products') {
                searchContainer.style.display = 'block';
            } else {
                searchContainer.style.display = 'none';
            }
        });
    </script>
</body>
</html>