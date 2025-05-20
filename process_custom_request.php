<?php
session_start();
require_once 'db_connect.php';

// Debug mode disabled for production - set to false to show normal confirmation page
$DEBUG_MODE = false;

// Always set content type to HTML since we're returning an HTML page in all cases
header('Content-Type: text/html; charset=UTF-8');

$response = [
    'success' => false,
    'message' => 'An error occurred while processing your request.',
    'debug_info' => []
];

// Debug function to store important debug info
function debug_log($message) {
    global $response;
    error_log($message);
    $response['debug_info'][] = $message;
}

// Start debug output
debug_log("== CUSTOM REQUEST DEBUG START: " . date('Y-m-d H:i:s') . " ==");

// Critical file upload test - direct inspection of the incoming request
debug_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
debug_log("Content-Type header: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set'));

// Check if any files were uploaded
if (empty($_FILES)) {
    debug_log("CRITICAL ERROR: No files in $_FILES array");
    
    // Check form enctype
    if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') === false) {
        debug_log("ERROR: Form is not using multipart/form-data enctype");
    }
} else {
    debug_log("Files found in request: " . count($_FILES));
    foreach ($_FILES as $key => $fileData) {
        debug_log("File input name: {$key}");
        debug_log("  - File name: {$fileData['name']}");
        debug_log("  - File size: {$fileData['size']}");
        debug_log("  - Error code: {$fileData['error']}");
    }
}

try {
    // Debugging output - show all POST and FILES data at the start
    error_log("==== CUSTOM REQUEST DEBUG START =====");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    
    // Check if the request is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // Get form data
    $product_type = $_POST['product_type'] ?? '';
    $style = $_POST['style'] ?? '';
    $material = $_POST['material'] ?? '';
    $wood_type = $_POST['wood_type'] ?? null;
    $fabric_type = $_POST['fabric_type'] ?? null;
    $color = $_POST['color'] ?? '';
    $finish_type = $_POST['finish_type'] ?? '';
    $dimensions = $_POST['dimensions'] ?? '';
    $add_ons = $_POST['add_ons'] ?? null;
    $special_requests = $_POST['special_requests'] ?? null;
    $budget = floatval($_POST['budget'] ?? 0);
    $estimated_price = floatval($_POST['estimated_price'] ?? 0);
    
    // Get user ID if logged in
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Handle file upload if exists
    // This is a completely rewritten image upload handler with better error handling
    $image_path = null;
    
    // Check if image upload was indicated by the form
    $image_uploaded = isset($_POST['image_uploaded']) && $_POST['image_uploaded'] === '1';
    error_log("Image uploaded flag from form: " . ($image_uploaded ? 'YES' : 'NO'));
    
    // Only process image if the upload flag is set
    if ($image_uploaded) {
        error_log("Image upload flag is set to YES");
        // Check if we have a file upload
        if (isset($_FILES['product_image'])) {
            error_log("Product image found in request with error code: " . $_FILES['product_image']['error']);
            error_log("Image details: name={$_FILES['product_image']['name']}, size={$_FILES['product_image']['size']}, type={$_FILES['product_image']['type']}");
            
            // Check for upload errors
            if ($_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                // Create upload directory with proper permissions
                $upload_dir = 'uploads/custom_requests/';
                error_log("Upload directory path: " . realpath(dirname(__FILE__)) . '/' . $upload_dir);
                
                // Check if parent directory exists and is writable
                $parent_dir = dirname($upload_dir);
                if (!is_dir($parent_dir)) {
                    error_log("Parent directory does not exist: {$parent_dir}");
                    if (!mkdir($parent_dir, 0777, true)) {
                        error_log("Failed to create parent directory: {$parent_dir}");
                    } else {
                        chmod($parent_dir, 0777);
                        error_log("Created parent directory with permissions: {$parent_dir}");
                    }
                }
                
                // Check if upload directory exists
                if (!is_dir($upload_dir)) {
                    error_log("Upload directory does not exist. Attempting to create: {$upload_dir}");
                    // Create directory with full permissions
                    if (!mkdir($upload_dir, 0777, true)) {
                        error_log("Failed to create upload directory: {$upload_dir}");
                        error_log("Directory creation error: " . print_r(error_get_last(), true));
                    } else {
                        // Set directory permissions explicitly
                        chmod($upload_dir, 0777);
                        error_log("Created upload directory with permissions: {$upload_dir}");
                    }
                } else {
                    error_log("Upload directory already exists: {$upload_dir}");
                    error_log("Directory writable: " . (is_writable($upload_dir) ? 'YES' : 'NO'));
                    error_log("Directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4));
                }
                
                // Generate a unique filename that's web-friendly
                $original_filename = $_FILES['product_image']['name'];
                $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
                $filename = 'custom_' . time() . '_' . rand(1000, 9999) . '.' . strtolower($file_extension);
                $target_path = $upload_dir . $filename;
                
                error_log("Attempting to move uploaded file to: {$target_path}");
                
                // Ensure the temporary file actually exists and has content before attempting to move it
                error_log("Checking temporary file: {$_FILES['product_image']['tmp_name']}");
                error_log("Temp file exists: " . (file_exists($_FILES['product_image']['tmp_name']) ? 'YES' : 'NO'));
                error_log("Temp file size: " . (file_exists($_FILES['product_image']['tmp_name']) ? filesize($_FILES['product_image']['tmp_name']) . ' bytes' : 'N/A'));
                
                // Create the target path with absolute path to prevent path issues
                $absolute_upload_dir = realpath(dirname(__FILE__)) . '/' . $upload_dir;
                error_log("Absolute upload directory: {$absolute_upload_dir}");
                
                // Make sure absolute directory exists
                if (!is_dir($absolute_upload_dir)) {
                    if (!mkdir($absolute_upload_dir, 0777, true)) {
                        error_log("Failed to create absolute upload directory: {$absolute_upload_dir}");
                    } else {
                        chmod($absolute_upload_dir, 0777);
                        error_log("Created absolute upload directory with permissions: {$absolute_upload_dir}");
                    }
                }
                
                // Use the absolute path for the target
                $absolute_target_path = $absolute_upload_dir . $filename;
                error_log("Absolute target path: {$absolute_target_path}");
                
                // Store the relative path for web access
                $relative_image_path = $upload_dir . $filename;
                
                // Move the uploaded file with extensive logging
                error_log("Attempting to move file: {$_FILES['product_image']['tmp_name']} -> {$absolute_target_path}");
                
                // Make an explicit verification that our directory is writable
                if (!is_writable(dirname($absolute_target_path))) {
                    error_log("CRITICAL ERROR: Upload directory is not writable: " . dirname($absolute_target_path));
                    // Try to fix permissions one more time
                    chmod(dirname($absolute_target_path), 0777);
                }
                
                /* Attempt to move the uploaded file to the target destination.
                   This is a critical step for the image upload functionality.
                   The temporary file will be checked and then moved to the uploads directory. */
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $absolute_target_path)) {
                    // Set file permissions to ensure it's readable
                    chmod($absolute_target_path, 0644);
                    
                    // Store the path in the database - use relative path for web access
                    $image_path = $relative_image_path;
                    
                    // Debug output
                    error_log("Image uploaded successfully: {$image_path}");
                    error_log("File exists check: " . (file_exists($absolute_target_path) ? 'YES' : 'NO'));
                    error_log("File permissions: " . substr(sprintf('%o', fileperms($absolute_target_path)), -4));
                    error_log("File size: " . (file_exists($absolute_target_path) ? filesize($absolute_target_path) . ' bytes' : 'N/A'));
                    
                    // Add the image path to the response for the client
                    $response['image_path'] = $image_path;
                    $response['image_url'] = $image_path; // Also provide an image URL
                } else {
                    error_log("CRITICAL ERROR: Failed to move uploaded file from {$_FILES['product_image']['tmp_name']} to {$absolute_target_path}");
                    error_log("Upload error details: " . print_r(error_get_last(), true));
                    error_log("Temp file exists: " . (file_exists($_FILES['product_image']['tmp_name']) ? 'YES' : 'NO'));
                    error_log("Target directory writable: " . (is_writable(dirname($absolute_target_path)) ? 'YES' : 'NO'));
                    error_log("PHP file upload settings from php.ini:");
                    error_log("upload_max_filesize: " . ini_get('upload_max_filesize'));
                    error_log("post_max_size: " . ini_get('post_max_size'));
                    error_log("max_file_uploads: " . ini_get('max_file_uploads'));
                }
            } else {
                // Handle specific upload error codes
                $error_messages = [
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
                ];
                
                $error_code = $_FILES['product_image']['error'];
                $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Unknown upload error';
                
                error_log("File upload error: {$error_message} (Code: {$error_code})");
            }
        } else {
            error_log("Image upload flag was set, but no product_image found in the request");
        }
    } else {
        error_log("No image upload indicated by the form");
    }
    
    // Simplified debug output
    $response['image_path'] = $image_path;
    
    // Log the image path for debugging
    if (!empty($image_path)) {
        error_log("Image path for database: {$image_path}");
    } else {
        error_log("No image path to save to database");
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO custom_requests 
        (user_id, product_type, style, material, wood_type, fabric_type, color, 
         finish_type, dimensions, add_ons, special_requests, budget, estimated_price, image_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
    // Using the new image_path_for_db variable to ensure the reference is not lost
    // This was a critical fix for the issue with null image paths in the database
    $stmt->bind_param(
        'issssssssssdds', 
        $user_id, 
        $product_type, 
        $style, 
        $material, 
        $wood_type, 
        $fabric_type, 
        $color, 
        $finish_type, 
        $dimensions, 
        $add_ons, 
        $special_requests, 
        $budget, 
        $estimated_price,
        $image_path_for_db  // Using the new variable reference here
    );
    
    // Double-check that our parameter is correctly set right before execution
    error_log("Final check - image_path_for_db right before execution: '{$image_path_for_db}'");
    
    // Debug the SQL parameters before execution
    error_log("About to execute SQL with image_path: {$image_path}");
    
    // Make sure image_path is properly set for the SQL query
    // This is a critical fix to ensure the image path is saved to the database
    if ($image_path === null) {
        error_log("Image path is null, setting to empty string for database insertion");
        $image_path = ''; // Ensure it's not null for the database
    }
    
    // Add thorough validation of the image path before SQL insertion
    error_log("CRITICAL CHECK - Final image_path value before SQL bind: '{$image_path}'");
    error_log("image_path variable type: " . gettype($image_path));
    
    // Check if the parameter binding has the correct types
    // String 's' for image_path in the binding: 'issssssssssdds'
    //                                                       ^ This position for image_path
    error_log("SQL binding parameter string: 'issssssssssdds' - ensure last 's' is for image_path");
    
    /* This is a critical bug check to ensure the image path gets properly inserted
       Adding a commented output of all parameters in the exact order they're sent to the database.
       Any mismatch here could cause the image_path to be null or improperly saved. */
    error_log("Parameter binding order check: 
        1. user_id: {$user_id} (i)
        2. product_type: {$product_type} (s)
        3. style: {$style} (s) 
        4. material: {$material} (s)
        5. wood_type: {$wood_type} (s)
        6. fabric_type: {$fabric_type} (s)
        7. color: {$color} (s)
        8. finish_type: {$finish_type} (s) 
        9. dimensions: {$dimensions} (s)
        10. add_ons: [JSON data] (s)
        11. special_requests: {$special_requests} (s)
        12. budget: {$budget} (d)
        13. estimated_price: {$estimated_price} (d)
        14. image_path: {$image_path} (s)");
        
    // Make sure to refresh the $image_path variable for the last parameter by re-assigning it
    // in case the variable reference was lost somewhere
    $image_path_for_db = $image_path; // Create a new reference to ensure it's not lost
    
    // Verify all parameters are correctly set before executing the query
    // Fixed the deprecated substr warning by adding proper null checks
    $add_ons_preview = $add_ons ? substr($add_ons, 0, 50) . '...' : 'NULL';
    error_log("SQL Parameters: user_id={$user_id}, product_type={$product_type}, dimensions={$dimensions}, add_ons={$add_ons_preview}, image_path={$image_path}");
    
    // Add detailed debugging for SQL execution
    if ($stmt->execute()) {
        // Get the inserted ID for reference
        $insert_id = $conn->insert_id;
        error_log("Database insert successful. New ID: {$insert_id}");
        
        // Double-check that the image path was saved correctly
        $check_stmt = $conn->prepare("SELECT image_path FROM custom_requests WHERE id = ?");
        $check_stmt->bind_param('i', $insert_id);
        $check_stmt->execute();
        $check_stmt->bind_result($saved_image_path);
        $check_stmt->fetch();
        $check_stmt->close();
        
        error_log("Saved image path in database: " . ($saved_image_path ?? 'NULL'));
        
        // If image path wasn't saved correctly, try to update it directly
        if ((!empty($image_path) && empty($saved_image_path)) || $saved_image_path !== $image_path) {
            error_log("Image path mismatch, attempting direct update");
            $update_stmt = $conn->prepare("UPDATE custom_requests SET image_path = ? WHERE id = ?");
            $update_stmt->bind_param('si', $image_path, $insert_id);
            $update_stmt->execute();
            $update_stmt->close();
            error_log("Direct update of image_path attempted");
        }
        
        $response = [
            'success' => true,
            'message' => 'Your custom product request has been submitted successfully!',
            'debug_info' => [
                'insert_id' => $insert_id,
                'image_path_sent' => $image_path,
                'image_path_saved' => $saved_image_path
            ]
        ];
    } else {
        // Log the SQL error
        error_log("SQL Error: " . $stmt->error);
        throw new Exception('Failed to save your request to the database: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Set response data for output
if (empty($response)) {
    $response = [
        'success' => $success,
        'message' => $message,
        'debug_info' => $debug_info
    ];
}

// Important: Clean any previous output buffers to prevent header issues
// This ensures a clean slate for our HTML output
while (ob_get_level()) {
    ob_end_clean();
}

// Set proper content-type header for HTML output - crucial for browser rendering
// Without this, browsers might interpret the output as plain text
header('Content-Type: text/html; charset=UTF-8');

// Output response based on debug mode
if ($DEBUG_MODE) {
    // Debug HTML output with detailed information (hidden for production)
    include 'debug_template.php'; // Include separate debug template if needed for future debugging
} else {
    // Production confirmation page with print functionality
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Custom Request Confirmation - RoomGenius</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="customize.css">
        <style>
            /* Confirmation page specific styles */
            body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; }
            .confirmation-container { max-width: 800px; margin: 30px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .success-icon { font-size: 64px; color: #4CAF50; text-align: center; margin-bottom: 20px; }
            h1 { color: #333; text-align: center; margin-bottom: 30px; }
            h2 { color: #555; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 30px; }
            .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .details-table th, .details-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
            .details-table th { background-color: #f5f5f5; font-weight: bold; }
            .image-preview { margin: 20px 0; text-align: center; }
            .image-preview img { max-width: 100%; max-height: 300px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            .no-image { padding: 30px; background: #f5f5f5; border-radius: 4px; text-align: center; color: #777; }
            .action-buttons { display: flex; justify-content: space-between; margin-top: 40px; }
            .action-buttons a, .action-buttons button { padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: bold; cursor: pointer; transition: all 0.3s; border: none; }
            .primary-button { background: #4CAF50; color: white; }
            .secondary-button { background: #f5f5f5; color: #333; border: 1px solid #ddd; }
            .primary-button:hover { background: #45a049; }
            .secondary-button:hover { background: #e9e9e9; }
            .print-section { display: flex; align-items: center; }
            .print-section button { margin-left: 10px; }
            .order-number { font-weight: bold; color: #4CAF50; }
            
            /* Print-specific styles */
            @media print {
                .no-print { display: none !important; }
                body { background: white; }
                .confirmation-container { box-shadow: none; margin: 0; padding: 20px; }
                .action-buttons { display: none; }
                .image-preview img { max-height: 250px; }
            }
        </style>
    </head>
    <body>
        <div class="confirmation-container">
            <div class="success-icon"><i class="fas fa-check-circle"></i></div>
            <h1>Custom Request Submitted Successfully</h1>
            
            <p>Thank you for submitting your custom product request. Our team will review your specifications and get back to you with a detailed quote and timeline shortly.</p>
            
            <div class="order-info">
                <h2>Request Details</h2>
                <p>Request ID: <span class="order-number"><?= isset($insert_id) ? $insert_id : 'N/A' ?></span></p>
                <p>Submitted on: <?= date('F j, Y, g:i a') ?></p>
            </div>
            
            <div class="product-details">
                <h2>Product Specifications</h2>
                <table class="details-table">
                    <tr>
                        <th>Product Type</th>
                        <td><?= htmlspecialchars($product_type ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Style</th>
                        <td><?= htmlspecialchars($style ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Primary Material</th>
                        <td><?= htmlspecialchars($material ?? 'N/A') ?></td>
                    </tr>
                    <?php if(!empty($wood_type)): ?>
                    <tr>
                        <th>Wood Type</th>
                        <td><?= htmlspecialchars($wood_type) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(!empty($fabric_type)): ?>
                    <tr>
                        <th>Fabric Type</th>
                        <td><?= htmlspecialchars($fabric_type) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Color</th>
                        <td><?= htmlspecialchars($color ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Finish Type</th>
                        <td><?= htmlspecialchars($finish_type ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Dimensions</th>
                        <td><?= htmlspecialchars($dimensions ?? 'N/A') ?></td>
                    </tr>
                    <?php if(!empty($special_requests)): ?>
                    <tr>
                        <th>Special Requests</th>
                        <td><?= nl2br(htmlspecialchars($special_requests)) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Estimated Budget</th>
                        <td>$<?= number_format($budget, 2) ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Image preview section -->
            <?php if(!empty($image_path) && file_exists($image_path)): ?>
            <div class="image-section">
                <h2>Reference Image</h2>
                <div class="image-preview">
                    <img src="<?= htmlspecialchars($image_path) ?>" alt="Custom Product Reference Image">
                </div>
            </div>
            <?php else: ?>
            <div class="image-section">
                <h2>Reference Image</h2>
                <div class="no-image">No reference image provided</div>
            </div>
            <?php endif; ?>
            
            <div class="next-steps">
                <h2>What Happens Next?</h2>
                <ol>
                    <li>Our design team will review your request within 1-2 business days</li>
                    <li>We'll send you a detailed quote and design proposal</li>
                    <li>Once you approve, production will begin with an estimated timeline</li>
                    <li>We'll keep you updated throughout the creation process</li>
                </ol>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="secondary-button no-print"><i class="fas fa-home"></i> Return to Home</a>
                
                <div class="print-section no-print">
                    <button onclick="window.print()" class="primary-button"><i class="fas fa-print"></i> Print Confirmation</button>
                </div>
            </div>
        </div>
        
        <script>
            // Add any JavaScript needed for the confirmation page here
            document.addEventListener('DOMContentLoaded', function() {
                // Print auto-trigger option (commented out by default)
                // setTimeout(function() { window.print(); }, 1000);
            });
        </script>
    </body>
    </html>
    <?php
}

/**
 * Helper function to get a human-readable error message for upload error codes
 * @param int $error_code The PHP upload error code
 * @return string Human-readable error message
 */
function get_upload_error_message($error_code) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];
    
    return isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Unknown error';
}
?>
