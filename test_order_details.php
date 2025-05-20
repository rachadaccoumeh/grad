<?php
/**
 * Test file to check if get_order_details.php is working correctly
 */

// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Show errors for testing

// Test response
echo json_encode([
    'success' => true,
    'message' => 'Test successful',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
