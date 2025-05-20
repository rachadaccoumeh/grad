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
    
    file_put_contents(LOGS_DIR . 'test_image_generation.log', $logMessage, FILE_APPEND);
    echo "[LOG] $message\n";
}

// Function to generate image with Gemini (simplified version for testing)
function generateImageWithGemini($imagePath, $prompt) {
    try {
        $apiKey = GEMINI_API_KEY;
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . $apiKey;
        
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
            $errorMsg = 'API Error: ' . ($responseData['error']['message'] ?? 'Unknown error');
            if (isset($responseData['error']['details'])) {
                $errorMsg .= '. Details: ' . print_r($responseData['error']['details'], true);
            }
            throw new Exception($errorMsg);
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
            'raw_response' => $responseData
        ];
    } catch (Exception $e) {
        logMessage('Error in generateImageWithGemini: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Main test execution
try {
    logMessage("Starting image generation test");
    
    // Create a test room image
    $testImagePath = UPLOAD_DIR . 'test_room.png';
    $testImage = imagecreatetruecolor(400, 300);
    
    // Create a simple room-like image
    $wallColor = imagecolorallocate($testImage, 240, 240, 220); // Off-white
    $floorColor = imagecolorallocate($testImage, 180, 140, 100); // Brown
    $windowColor = imagecolorallocate($testImage, 135, 206, 235); // Sky blue
    
    // Draw walls
    imagefilledrectangle($testImage, 0, 0, 400, 200, $wallColor);
    
    // Draw floor
    imagefilledrectangle($testImage, 0, 200, 400, 300, $floorColor);
    
    // Draw window
    imagefilledrectangle($testImage, 50, 50, 150, 150, $windowColor);
    
    // Save the test image
    imagepng($testImage, $testImagePath);
    imagedestroy($testImage);
    
    if (!file_exists($testImagePath)) {
        throw new Exception('Failed to create test room image');
    }
    
    logMessage("Created test room image at: $testImagePath");
    
    // Test prompt for interior design
    $prompt = "Generate interior design ideas for this room in a modern style with a detailed description of furniture placement, color scheme, and decorative elements.";
    
    logMessage("Sending request to Gemini API with prompt: '$prompt'");
    
    // Call the Gemini API
    $result = generateImageWithGemini($testImagePath, $prompt);
    
    // Output the result
    echo json_encode([
        'success' => $result['success'],
        'generated_text' => $result['success'] ? $result['generated_text'] : null,
        'error' => $result['success'] ? null : $result['error']
    ], JSON_PRETTY_PRINT) . "\n";
    
    // Clean up
    unlink($testImagePath);
    
} catch (Exception $e) {
    $error = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    echo json_encode($error, JSON_PRETTY_PRINT) . "\n";
    logMessage("Test failed: " . $e->getMessage());
}
