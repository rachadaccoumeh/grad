<?php
// Direct file upload test script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Show initial page with form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<!DOCTYPE html>
<html>
<head>
    <title>File Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>File Upload Test</h1>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="test_file">Select a test image:</label>
                <input type="file" name="test_file" id="test_file" required>
            </div>
            
            <button type="submit">Test Upload</button>
        </form>
    </div>
</body>
</html>';
    exit;
}

// Process the uploaded file
echo '<!DOCTYPE html>
<html>
<head>
    <title>File Upload Test Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>File Upload Test Results</h1>';

// Debug information section
echo '<h2>Debug Information</h2>';
echo '<pre>';

// Show $_FILES array
echo "<strong>Files Data:</strong>\n";
print_r($_FILES);
echo "\n\n";

// Check upload directory
$upload_dir = 'uploads/test/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
    echo "Created directory: $upload_dir\n";
} else {
    echo "Directory exists: $upload_dir\n";
}

echo "Directory writable: " . (is_writable($upload_dir) ? 'YES' : 'NO') . "\n";

// Test server environment
echo "\n<strong>Server Environment:</strong>\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Max Upload Size: " . ini_get('upload_max_filesize') . "\n";
echo "Max Post Size: " . ini_get('post_max_filesize') . "\n";
echo "Upload Temp Dir: " . ini_get('upload_tmp_dir') . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";

// Process file if it exists
if (isset($_FILES['test_file']) && $_FILES['test_file']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['test_file'];
    
    echo "\n<strong>File Information:</strong>\n";
    echo "Name: " . $file['name'] . "\n";
    echo "Type: " . $file['type'] . "\n";
    echo "Size: " . $file['size'] . " bytes\n";
    echo "Temp Path: " . $file['tmp_name'] . "\n";
    echo "Temp File Exists: " . (file_exists($file['tmp_name']) ? 'YES' : 'NO') . "\n";
    
    // Try to move file
    $filename = uniqid('test_') . '_' . $file['name'];
    $target_path = $upload_dir . $filename;
    
    echo "\nAttempting to move file to: $target_path\n";
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        echo "SUCCESS: File uploaded and moved successfully!\n";
        echo "File stored at: $target_path\n";
        echo "File exists check: " . (file_exists($target_path) ? 'YES' : 'NO') . "\n";
        echo "File size: " . filesize($target_path) . " bytes\n";
    } else {
        echo "ERROR: Failed to move uploaded file!\n";
        echo "Error details: " . error_get_last()['message'] . "\n";
    }
} else {
    if (isset($_FILES['test_file'])) {
        $error_code = $_FILES['test_file']['error'];
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Unknown error code: ' . $error_code;
        echo "\nERROR: " . $error_message . "\n";
    } else {
        echo "\nERROR: No file upload data received\n";
    }
}

echo '</pre>';

echo '<p><a href="' . $_SERVER['PHP_SELF'] . '">Try another upload</a></p>';
echo '</div></body></html>';
?>
