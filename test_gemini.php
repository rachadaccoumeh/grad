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
    
    file_put_contents(LOGS_DIR . 'test_gemini.log', $logMessage, FILE_APPEND);
    echo "[LOG] $message\n";
}

// Function to test Gemini API
function testGeminiAPI($imagePath, $prompt) {
    try {
        $apiKey = GEMINI_API_KEY;
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent' . '?key=' . $apiKey;
        
        // Read the image file
        $imageContent = file_get_contents($imagePath);
        if ($imageContent === false) {
            throw new Exception('Failed to read image file');
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
                        ['text' => $prompt],
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
        
        logMessage('Sending request to Gemini API...');
        
        // Initialize cURL
        $ch = curl_init($apiUrl);
        
        if ($ch === false) {
            throw new Exception('Failed to initialize cURL');
        }
        
        // Set cURL options
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 60,
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
        
        logMessage("API Response (HTTP $httpCode)");
        
        // Decode the JSON response
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API: ' . json_last_error_msg());
        }
        
        // Check for API errors
        if (isset($responseData['error'])) {
            $errorMsg = 'API Error: ' . ($responseData['error']['message'] ?? 'Unknown error');
            if (isset($responseData['error']['details'])) {
                $errorMsg .= '. Details: ' . print_r($responseData['error']['details'], true);
            }
            throw new Exception($errorMsg);
        }
        
        // Return the response data
        return [
            'success' => true,
            'http_code' => $httpCode,
            'response' => $responseData
        ];
        
    } catch (Exception $e) {
        logMessage('Error in testGeminiAPI: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
}

// Main test execution
try {
    logMessage("Starting Gemini API test");
    
    // Create a test image
    $testImagePath = UPLOAD_DIR . 'test_gemini_image.png';
    $testImage = imagecreatetruecolor(200, 200);
    $bgColor = imagecolorallocate($testImage, 0, 100, 200);
    $textColor = imagecolorallocate($testImage, 255, 255, 255);
    imagefill($testImage, 0, 0, $bgColor);
    imagestring($testImage, 5, 20, 90, 'Test Image for Gemini', $textColor);
    imagepng($testImage, $testImagePath);
    imagedestroy($testImage);
    
    if (!file_exists($testImagePath)) {
        throw new Exception('Failed to create test image');
    }
    
    logMessage("Created test image at: $testImagePath");
    
    // Test prompt
    $prompt = "Describe this image in detail";
    
    logMessage("Sending request to Gemini API with prompt: '$prompt'");
    
    // Call the Gemini API
    $result = testGeminiAPI($testImagePath, $prompt);
    
    // Clean up
    unlink($testImagePath);
    
    // Output the result
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    
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
