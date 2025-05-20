<?php
// Test script for Gemini API integration
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define a test log file
$testLogFile = LOGS_DIR . 'gemini_api_test.log';

// Function to log messages
function testLog($message) {
    global $testLogFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($testLogFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

// Function to test the Gemini API
function testGeminiApi($imagePath, $prompt) {
    try {
        testLog("Starting Gemini API test...");
        testLog("Using model: " . GEMINI_MODEL);
        testLog("Using prompt: " . $prompt);
        
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
        
        testLog("Image loaded successfully. MIME type: " . $mimeType);
        
        // Prepare the request data
        $data = [
            'contents' => [
                [
                    'role' => 'user',
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
                'maxOutputTokens' => 4096,
                'responseModalities' => ['IMAGE', 'TEXT'],
                'responseMimeType' => 'text/plain'
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];
        
        // API URL with key
        $apiKey = GEMINI_API_KEY;
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . $apiKey;
        
        testLog("Sending request to: " . $apiUrl);
        
        // Initialize cURL
        $ch = curl_init($apiUrl);
        
        if ($ch === false) {
            throw new Exception('Failed to initialize cURL');
        }
        
        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 60
        ]);
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        // Get additional info for debugging
        $curlInfo = curl_getinfo($ch);
        testLog('cURL Info: ' . print_r($curlInfo, true));
        
        // Close cURL resource
        curl_close($ch);
        
        // Check for cURL errors
        if ($response === false) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        testLog('API Response (HTTP ' . $httpCode . '): ' . substr($response, 0, 500) . '...');
        
        // Check HTTP status code
        if ($httpCode !== 200) {
            $errorMsg = 'API request failed with HTTP code ' . $httpCode;
            
            if ($response) {
                $errorMsg .= '. Response: ' . $response;
            }
            throw new Exception($errorMsg);
        }
        
        // Decode the JSON response
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API: ' . json_last_error_msg());
        }
        
        // Extract content from response
        testLog("Response structure: " . print_r(array_keys($responseData), true));
        
        // Save the raw response for debugging
        file_put_contents(LOGS_DIR . 'last_api_response.json', json_encode($responseData, JSON_PRETTY_PRINT));
        testLog("Raw response saved to " . LOGS_DIR . 'last_api_response.json');
        
        // Extract image data if available
        $images = [];
        
        if (isset($responseData['candidates'][0]['content']['parts'])) {
            testLog("Found " . count($responseData['candidates'][0]['content']['parts']) . " parts in the response");
            
            foreach ($responseData['candidates'][0]['content']['parts'] as $index => $part) {
                testLog("Part $index type: " . json_encode(array_keys($part)));
                
                // Check for inline data (images)
                if (isset($part['inlineData']) && isset($part['inlineData']['data'])) {
                    testLog("Found image data in part $index");
                    $images[] = $part['inlineData']['data'];
                    
                    // Save this image to a file for inspection
                    $imageData = base64_decode($part['inlineData']['data']);
                    $outputFile = __DIR__ . '/test_output_' . time() . '_' . $index . '.jpg';
                    file_put_contents($outputFile, $imageData);
                    testLog("Saved test image to: $outputFile");
                }
                // Check for text that might contain base64 images
                else if (isset($part['text'])) {
                    testLog("Found text in part $index: " . substr($part['text'], 0, 100) . "...");
                    
                    // Check if it's a data URL
                    if (strpos($part['text'], 'data:image') === 0) {
                        testLog("Text appears to be a data URL");
                        $base64Data = explode(',', $part['text'])[1] ?? '';
                        if (!empty($base64Data)) {
                            $images[] = $base64Data;
                            
                            // Save this image to a file for inspection
                            $imageData = base64_decode($base64Data);
                            $outputFile = __DIR__ . '/test_output_' . time() . '_' . $index . '.jpg';
                            file_put_contents($outputFile, $imageData);
                            testLog("Saved test image to: $outputFile");
                        }
                    }
                    // Check if it's a raw base64 string
                    else if (preg_match('/^[a-zA-Z0-9+\/=]+$/', trim($part['text']))) {
                        testLog("Text appears to be a raw base64 string");
                        $base64Data = trim($part['text']);
                        $images[] = $base64Data;
                        
                        // Save this image to a file for inspection
                        $imageData = base64_decode($base64Data);
                        $outputFile = __DIR__ . '/test_output_' . time() . '_' . $index . '.jpg';
                        file_put_contents($outputFile, $imageData);
                        testLog("Saved test image to: $outputFile");
                    }
                }
            }
        }
        
        testLog("Test completed. Found " . count($images) . " images in the response.");
        
        return [
            'success' => true,
            'images' => $images,
            'raw_response' => $responseData
        ];
        
    } catch (Exception $e) {
        testLog("ERROR: " . $e->getMessage());
        testLog("Stack trace: " . $e->getTraceAsString());
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
}

// Main execution
// Check if an image path was provided as a command line argument
$imagePath = $argv[1] ?? null;
if (!$imagePath) {
    // Look for a test image in the uploads directory
    $testImages = glob(UPLOAD_DIR . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
    if (!empty($testImages)) {
        $imagePath = $testImages[0];
    } else {
        die("Please provide an image path as a command line argument or add test images to the uploads directory.\n");
    }
}

// Basic test prompt
$prompt = "This is a photo of a room. Please redesign it in a modern style with a blue color scheme. Keep the same layout and perspective but change the furniture, colors, and decorations to create a modern look.";

// Execute the test
testLog("Testing with image: $imagePath");
$result = testGeminiApi($imagePath, $prompt);

// Output the result
echo "Test completed with " . ($result['success'] ? "SUCCESS" : "FAILURE") . "\n";
if (!$result['success']) {
    echo "Error: " . $result['error'] . "\n";
}
