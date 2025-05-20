<?php
// Google Gemini API Configuration
define('GEMINI_API_KEY', 'AIzaSyCRqMuWYw4y5FM3VPEOAiiCY2xTV9hqbNA'); // Replace with your actual Gemini API key
define('GEMINI_MODEL', 'gemini-2.0-flash-preview-image-generation'); // Using a model that supports multimodal inputs
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent');

// File upload settings
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Logs directory
define('LOGS_DIR', __DIR__ . '/logs/');

// Ensure uploads and logs directories exist and are writable
$requiredDirs = [UPLOAD_DIR, LOGS_DIR];
foreach ($requiredDirs as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0755, true)) {
            die("Failed to create directory: $dir");
        }
    }
    if (!is_writable($dir)) {
        if (!chmod($dir, 0755)) {
            die("Directory is not writable: $dir");
        }
    }
}

// Allowed file types
$ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Beirut');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to generate a unique filename
function generateUniqueFilename($extension) {
    return uniqid('img_') . '_' . time() . '.' . $extension;
}

// Function to validate uploaded file
function validateUploadedFile($file) {
    global $ALLOWED_TYPES;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Error uploading file.'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File is too large. Maximum size is 5MB.'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $ALLOWED_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and WebP are allowed.'];
    }
    
    return ['success' => true, 'mimeType' => $mimeType];
}
?>
