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
$password = "root123"; // Replace with your database password
$dbname = "roomgenius_db"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = "";
$product_id = "";
$name = "";
$description = "";
$price = "";
$style = "";
$category = "";
$size = "";
$image_path = "";
$stock_quantity = "";
$is_featured = 0;
$success_message = "";
$error_message = "";
$current_image = "";

// Check if ID is provided in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: product.php");
    exit;
}

$id = $_GET['id'];

// Get existing product data
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $product_id = $product['product_id'];
    $name = $product['name'];
    $description = $product['description'];
    $price = $product['price'];
    $style = $product['style'];
    $category = $product['category'];
    $image_path = $product['image_path'];
    $size = $product['size'];
    $stock_quantity = $product['stock_quantity'];
    $is_featured = $product['is_featured'];
    $current_image = $image_path;
} else {
    header("Location: product.php");
    exit;
}

// Process form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    // Get form data
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $style = $_POST['style'];
    $category = $_POST['category'];
    $size = $_POST['size'];
    $stock_quantity = $_POST['stock_quantity'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Handle image operations
    $remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] == 1;
    $new_image_uploaded = isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0;
    
    // If remove image is checked or a new image is uploaded
    if ($remove_image || $new_image_uploaded) {
        // Remove the current image if it exists and is not a default image
        if (!empty($current_image) && file_exists($current_image) && strpos($current_image, 'default') === false) {
            unlink($current_image);
        }
        $image_path = ''; // Clear the current image path
    }
    
    // Handle new image upload if provided
    if ($new_image_uploaded) {
        $upload_dir = "photos/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES["product_image"]["name"]);
        $target_file = $upload_dir . $file_name;
        
        $allowed_types = ["image/jpeg", "image/png", "image/jpg"];
        $file_type = $_FILES["product_image"]["type"];
        
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
                
                // Remove old image if it exists and is not the default image
                if (!empty($current_image) && file_exists($current_image) && strpos($current_image, 'default') === false) {
                    unlink($current_image);
                }
            } else {
                $error_message = "Failed to upload image.";
            }
        } else {
            $error_message = "Only JPG, JPEG, and PNG files are allowed.";
        }
    } else {
        // Keep existing image
        $image_path = $current_image;
    }
    
    // If no errors, update the database
    if (empty($error_message)) {
        // Prepare an update statement
        $sql = "UPDATE products SET 
                product_id = ?, 
                name = ?, 
                description = ?, 
                price = ?, 
                style = ?, 
                category = ?,
                size = ?,
                image_path = ?, 
                stock_quantity = ?, 
                is_featured = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssdssssiii", 
            $product_id, 
            $name, 
            $description, 
            $price, 
            $style, 
            $category,
            $size,
            $image_path, 
            $stock_quantity, 
            $is_featured,
            $id
        );
        
        if ($stmt->execute()) {
            $success_message = "Product updated successfully!";
        } else {
            $error_message = "Error updating product: " . $stmt->error;
        }
        
        $stmt->close();
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="product.css">
    <title>Edit Product - RoomGenius Admin</title>
    <style>
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
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 4px;
        }
    </style>
    <style>
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            margin: 10px 0;
            border-radius: 4px;
            object-fit: cover;
        }
        
        .back-link {
            color: #24424c;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .back-link i {
            margin-right: 5px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
               
                <div class="user">
                    <img src="photos/adminphoto.JPG" alt="Admin">
                </div>
            </div>

            <div class="product-content">
                <div class="product-header">
                    <h2>Edit Product</h2>
                </div>
                
                <a href="product.php" class="back-link">
                    <i class='bx bx-arrow-back'></i> Back to Products
                </a>
                
                <?php if(!empty($success_message)): ?>
                <div class="alert success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if(!empty($error_message)): ?>
                <div class="alert error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="form-card">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="product_id">Product ID</label>
                            <input type="text" id="product_id" name="product_id" value="<?php echo $product_id; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" required><?php echo $description; ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Price ($)</label>
                                <input type="number" id="price" name="price" value="<?php echo $price; ?>" step="0.01" required>
                            </div>

                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo $stock_quantity; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="style">Style</label>
                                <select id="style" name="style" required>
                                    <option value="">Select Style</option>
                                    <option value="Modern" <?php echo $style == 'Modern' ? 'selected' : ''; ?>>Modern</option>
                                    <option value="Traditional" <?php echo $style == 'Traditional' ? 'selected' : ''; ?>>Traditional</option>
                                    <option value="Rustic" <?php echo $style == 'Rustic' ? 'selected' : ''; ?>>Rustic</option>
                                    <option value="Minimalist" <?php echo $style == 'Minimalist' ? 'selected' : ''; ?>>Minimalist</option>
                                    <option value="Industrial" <?php echo $style == 'Industrial' ? 'selected' : ''; ?>>Industrial</option>
                                    <option value="Luxury" <?php echo $style == 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                                     <option value="Classic" <?php echo $style == 'Classic' ? 'selected' : ''; ?>>Classic</option>
                                     <option value="Scandinavian" <?php echo $style == 'Scandinavian' ? 'selected' : ''; ?>>Scandinavian</option>
                                      <option value="Bohemian" <?php echo $style == 'Bohemian' ? 'selected' : ''; ?>>Bohemian</option>
                                      <option value="Executive" <?php echo $style == 'Executive' ? 'selected' : ''; ?>>Executive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="category">Category</label>
                                <select id="category" name="category" onchange="updateSizeOptions()" required>
                                    <option value="">Select Category</option>
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
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="size">Size</label>
                                <select id="size" name="size" required>
                                    <option value="">Select a category first</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Current Image</label>
                            <div id="current-image-container" style="margin-bottom: 10px;<?php echo empty($image_path) ? ' display: none;' : ''; ?>">
                                <div style="position: relative; display: inline-block;">
                                    <img id="current-image" src="<?php echo !empty($image_path) ? $image_path : ''; ?>" alt="Current Product Image" class="preview-image" style="max-width: 200px; max-height: 200px;">
                                    <button type="button" class="remove-image-btn" onclick="removeCurrentImage()">
                                        <i class='bx bx-x'></i>
                                    </button>
                                </div>
                                <input type="hidden" id="remove_image" name="remove_image" value="0">
                            </div>
                            <div id="no-image-message" style="display: <?php echo empty($image_path) ? 'block' : 'none'; ?>; color: #666; margin-bottom: 10px;">
                                No image selected
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="product_image">Upload New Image</label>
                            <div class="file-upload">
                                <input type="file" id="product_image" name="product_image" accept="image/*" onchange="previewNewImage(this)">
                                <label for="product_image"><i class='bx bx-upload'></i> Choose File</label>
                                <span id="file-chosen">No file chosen</span>
                            </div>
                            <div id="new-image-preview" style="margin-top: 10px; display: none;">
                                <div style="position: relative; display: inline-block;">
                                    <img id="preview" src="#" alt="New Image Preview" style="max-width: 200px; max-height: 200px; border-radius: 4px;">
                                    <button type="button" class="remove-image-btn" style="top: 5px; right: 5px;" onclick="removeNewImage()">
                                        <i class='bx bx-x'></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group checkbox">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo $is_featured ? 'checked' : ''; ?>>
                            <label for="is_featured">Featured Product</label>
                        </div>

                        <div class="form-buttons">
                            <button type="submit" name="update_product" class="btn-submit">Update Product</button>
                            <a href="product.php" class="btn-reset">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Image handling functions
        function removeCurrentImage() {
            document.getElementById('remove_image').value = '1';
            document.getElementById('current-image-container').style.display = 'none';
            document.getElementById('no-image-message').style.display = 'block';
            // Clear the file input if it has a value
            document.getElementById('product_image').value = '';
            // Hide new image preview if shown
            document.getElementById('new-image-preview').style.display = 'none';
        }
        
        function previewNewImage(input) {
            const preview = document.getElementById('preview');
            const fileChosen = document.getElementById('file-chosen');
            const newImagePreview = document.getElementById('new-image-preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    newImagePreview.style.display = 'block';
                    fileChosen.textContent = input.files[0].name;
                }
                
                reader.readAsDataURL(input.files[0]);
                
                // Uncheck remove image if a new image is selected
                document.getElementById('remove_image').value = '0';
            }
        }
        
        function removeNewImage() {
            const input = document.getElementById('product_image');
            input.value = '';
            document.getElementById('new-image-preview').style.display = 'none';
            document.getElementById('file-chosen').textContent = 'No file chosen';
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
                    // Small delay to ensure options are populated
                    setTimeout(() => {
                        sizeSelect.value = '<?php echo $size; ?>';
                    }, 100);
                }
            }
        });

        // Toggle sidebar
        let toggle = document.querySelector('.toggle');
        let navigation = document.querySelector('.navigation');
        let main = document.querySelector('.main');

        toggle.onclick = function() {
            navigation.classList.toggle('active');
            main.classList.toggle('active');
        }

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

        // Hide success message after 3 seconds
        setTimeout(function() {
            let successAlert = document.querySelector('.alert.success');
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 3000);
    </script>
</body>
</html>
