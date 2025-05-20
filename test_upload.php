<?php
// Include config file
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Function to log messages with timestamp
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    
    // Ensure the logs directory exists
    if (!file_exists(LOGS_DIR)) {
        mkdir(LOGS_DIR, 0755, true);
    }
    
    file_put_contents(LOGS_DIR . 'test_upload.log', $logMessage, FILE_APPEND);
}

// Test file upload
try {
    logMessage("Starting upload test");
    
    // Create a test image
    $testImage = imagecreatetruecolor(100, 100);
    $bgColor = imagecolorallocate($testImage, 255, 0, 0);
    imagefill($testImage, 0, 0, $bgColor);
    
    // Save the test image
    $testImagePath = UPLOAD_DIR . 'test_image.png';
    imagepng($testImage, $testImagePath);
    imagedestroy($testImage);
    
    if (!file_exists($testImagePath)) {
        throw new Exception('Failed to create test image');
    }
    
    logMessage("Test image created at: $testImagePath");
    
    // Test the validateUploadedFile function
    $testFile = [
        'name' => 'test_image.png',
        'type' => 'image/png',
        'tmp_name' => $testImagePath,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($testImagePath)
    ];
    
    $validation = validateUploadedFile($testFile);
    logMessage("Validation result: " . print_r($validation, true));
    
    if (!$validation['success']) {
        throw new Exception('File validation failed: ' . ($validation['error'] ?? 'Unknown error'));
    }
    
    // Test the generateUniqueFilename function
    $uniqueName = generateUniqueFilename('png');
    logMessage("Generated unique filename: $uniqueName");
    
    // Clean up
    unlink($testImagePath);
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Upload test completed successfully',
        'test_image_path' => $testImagePath,
        'validation' => $validation,
        'unique_filename' => $uniqueName
    ]);
    
} catch (Exception $e) {
    logMessage("Error in test: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
