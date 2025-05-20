<?php
/**
 * Comprehensive test script for image upload and Gemini API integration
 * This script tests the full flow from image upload to API response
 */

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
    
    file_put_contents(LOGS_DIR . 'integration_test.log', $logMessage, FILE_APPEND);
    echo "[LOG] $message\n";
}

/**
 * Function to generate image with Gemini API
 * This is a simplified version of the function in generate_design.php
 */
function generateImageWithGemini($imagePath, $prompt) {
    try {
        // Get API configuration from config.php
        $apiKey = GEMINI_API_KEY;
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . $apiKey;
        
        logMessage("Using model: " . GEMINI_MODEL);
        
        // Verify the image file exists and is readable
        if (!file_exists($imagePath) || !is_readable($imagePath)) {
            throw new Exception('Image file not found or not readable: ' . $imagePath);
        }
        
        // Read the image file
        $imageContent = file_get_contents($imagePath);
        if ($imageContent === false) {
            throw new Exception('Failed to read image file: ' . $imagePath);
        }
        
        $imageData = base64_encode($imageContent);
        $mimeType = mime_content_type($imagePath);
        
        if (!$mimeType) {
            throw new Exception('Could not determine MIME type of the image');
        }
        
        // Prepare the request data
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $imageData
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'topK' => 32,
                'topP' => 1,
                'maxOutputTokens' => 2048,
            ]
        ];
        
        // Log the request data (without the actual image data)
        $logData = $data;
        $logData['contents'][0]['parts'][1]['inlineData']['data'] = '[BASE64_IMAGE_DATA]';
        logMessage('Sending request to Gemini API: ' . json_encode($logData, JSON_PRETTY_PRINT));
        
        // Initialize cURL
        $ch = curl_init($apiUrl);
        
        if ($ch === false) {
            throw new Exception('Failed to initialize cURL');
        }
        
        // Set cURL options
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_VERBOSE => true
        ];
        
        curl_setopt_array($ch, $options);
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        // Close cURL resource
        curl_close($ch);
        
        // Check for cURL errors
        if ($response === false) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        logMessage('API Response (HTTP ' . $httpCode . '): ' . substr($response, 0, 300) . '...');
        
        // Decode the JSON response
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API: ' . json_last_error_msg() . '. Response: ' . $response);
        }
        
        // Check for API errors
        if (isset($responseData['error'])) {
            $errorCode = $responseData['error']['code'] ?? 0;
            $errorMessage = $responseData['error']['message'] ?? 'Unknown error';
            
            // Handle quota limit errors specifically
            if ($errorCode == 429) {
                logMessage('Quota limit exceeded: ' . $errorMessage);
                if (isset($responseData['error']['details'])) {
                    logMessage('Error details: ' . print_r($responseData['error']['details'], true));
                }
                
                throw new Exception('API rate limit exceeded. Please try again later or contact support.', 429);
            } else {
                // Handle other API errors
                $errorMsg = 'API Error: ' . $errorMessage;
                if (isset($responseData['error']['details'])) {
                    $errorMsg .= '. Details: ' . print_r($responseData['error']['details'], true);
                }
                throw new Exception($errorMsg, $errorCode);
            }
        }
        
        // Extract the generated text
        $generatedText = '';
        if (isset($responseData['candidates'][0]['content']['parts'])) {
            foreach ($responseData['candidates'][0]['content']['parts'] as $part) {
                if (isset($part['text'])) {
                    $generatedText .= $part['text'];
                }
            }
        }
        
        return [
            'success' => true,
            'generated_text' => $generatedText,
            'raw_response' => $responseData,
            'model_used' => GEMINI_MODEL,
            'http_code' => $httpCode
        ];
    } catch (Exception $e) {
        logMessage('Error in generateImageWithGemini: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'model_attempted' => GEMINI_MODEL
        ];
    }
}

/**
 * Function to test file upload validation
 */
function testFileUpload($filePath, $fileName, $fileType, $fileSize) {
    // Create a mock file array similar to $_FILES
    $mockFile = [
        'name' => $fileName,
        'type' => $fileType,
        'tmp_name' => $filePath,
        'error' => UPLOAD_ERR_OK,
        'size' => $fileSize
    ];
    
    logMessage("Testing file upload validation for: $fileName ($fileType, " . round($fileSize / 1024, 2) . " KB)");
    
    // Validate the file using the function from config.php
    $validationResult = validateUploadedFile($mockFile);
    
    logMessage("Validation result: " . ($validationResult['success'] ? 'PASSED' : 'FAILED - ' . ($validationResult['error'] ?? 'Unknown error')));
    
    return $validationResult;
}

// Main test execution
try {
    logMessage("=== STARTING FULL INTEGRATION TEST ===");
    logMessage("Current model: " . GEMINI_MODEL);
    
    // Create a test room image
    $testImagePath = UPLOAD_DIR . 'test_integration_room.png';
    $testImage = imagecreatetruecolor(400, 300);
    
    // Create a simple room-like image
    $wallColor = imagecolorallocate($testImage, 240, 240, 220); // Off-white
    $floorColor = imagecolorallocate($testImage, 180, 140, 100); // Brown
    $windowColor = imagecolorallocate($testImage, 135, 206, 235); // Sky blue
    $textColor = imagecolorallocate($testImage, 50, 50, 50); // Dark gray
    
    // Draw walls
    imagefilledrectangle($testImage, 0, 0, 400, 200, $wallColor);
    
    // Draw floor
    imagefilledrectangle($testImage, 0, 200, 400, 300, $floorColor);
    
    // Draw window
    imagefilledrectangle($testImage, 50, 50, 150, 150, $windowColor);
    
    // Add text
    imagestring($testImage, 5, 200, 100, 'Test Room', $textColor);
    
    // Save the test image
    imagepng($testImage, $testImagePath);
    imagedestroy($testImage);
    
    if (!file_exists($testImagePath)) {
        throw new Exception('Failed to create test room image');
    }
    
    $fileSize = filesize($testImagePath);
    logMessage("Created test room image at: $testImagePath (Size: " . round($fileSize / 1024, 2) . " KB)");
    
    // Test 1: File upload validation
    $uploadTest = testFileUpload($testImagePath, 'test_room.png', 'image/png', $fileSize);
    
    if (!$uploadTest['success']) {
        throw new Exception('File upload validation failed: ' . ($uploadTest['error'] ?? 'Unknown error'));
    }
    
    // Test 2: Gemini API integration
    logMessage("Testing Gemini API integration");
    
    // Test prompt for interior design
    $prompt = "Generate interior design ideas for this room in a modern style. Describe furniture placement, color scheme, and decorative elements.";
    
    logMessage("Sending request to Gemini API with prompt: '$prompt'");
    
    // Call the Gemini API
    $apiResult = generateImageWithGemini($testImagePath, $prompt);
    
    // Output the result
    echo json_encode([
        'test_results' => [
            'file_upload_test' => $uploadTest,
            'api_integration_test' => [
                'success' => $apiResult['success'],
                'model_used' => $apiResult['success'] ? $apiResult['model_used'] : $apiResult['model_attempted'],
                'http_code' => $apiResult['success'] ? $apiResult['http_code'] : null,
                'error' => $apiResult['success'] ? null : $apiResult['error'],
                'generated_text_sample' => $apiResult['success'] ? substr($apiResult['generated_text'], 0, 200) . '...' : null
            ]
        ]
    ], JSON_PRETTY_PRINT) . "\n";
    
    // Clean up
    unlink($testImagePath);
    logMessage("Test completed and test image removed");
    
} catch (Exception $e) {
    $error = [
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
    echo json_encode($error, JSON_PRETTY_PRINT) . "\n";
    logMessage("Test failed: " . $e->getMessage());
}
