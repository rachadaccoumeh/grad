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

// Initialize variables for form data and error messages
$product_id = $name = $description = $price = $style = $category = $stock_quantity = $is_featured = "";
$product_id_err = $name_err = $description_err = $price_err = $style_err = $category_err = $stock_err = $image_err = "";
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
      empty($style_err) && empty($category_err) && empty($stock_err) && empty($image_err)) {
    
    // Prepare an insert statement
    $sql = "INSERT INTO products (product_id, name, description, price, style, category, image_path, date_added, stock_quantity, is_featured) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
      // Bind variables to the prepared statement as parameters
      $stmt->bind_param("sssdsssii", $param_product_id, $param_name, $param_description, $param_price, 
                        $param_style, $param_category, $param_image_path, $param_stock_quantity, $param_is_featured);
      
      // Set parameters
      $param_product_id = $product_id;
      $param_name = $name;
      $param_description = $description;
      $param_price = $price;
      $param_style = $style;
      $param_category = $category;
      $param_image_path = $image_path;
      $param_stock_quantity = $stock_quantity;
      $param_is_featured = $is_featured;
      
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
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="product.css">
    <title>Product Management</title>
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
                <div class="search" id="search-container">
                    <form id="search-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
                        <label>
                            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <input type="hidden" name="tab" value="manage-products">
                            <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                        </label>
                    </form>
                </div>
                <div class="user">
                    <img src="images/admin.png" alt="Admin">
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
                                    </select>
                                    <span class="error"><?php echo $style_err; ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select id="category" name="category">
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
                                <label for="product_image">Product Image</label>
                                <div class="file-upload">
                                    <input type="file" id="product_image" name="product_image" accept="image/*">
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