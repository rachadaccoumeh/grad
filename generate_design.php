<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json; charset=utf-8');

// Include config file
require_once 'config.php';

// Define log file path
$logFile = LOGS_DIR . 'debug.log';

// Function to log messages with timestamp
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    
    // Ensure the logs directory exists
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to send JSON response and exit
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

// Handle CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Function to send request to Gemini API
function generateImageWithGemini($imagePath, $prompt, $count = 1) {
    try {
        $apiKey = GEMINI_API_KEY;
        // Use streamGenerateContent endpoint for image generation with Gemini 2.0
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . $apiKey;
        
        // Check if we've already hit the rate limit recently to avoid unnecessary API calls
        $rateLimitFile = LOGS_DIR . 'rate_limit.txt';
        if (file_exists($rateLimitFile)) {
            $rateLimitData = json_decode(file_get_contents($rateLimitFile), true);
            $rateLimitExpiry = $rateLimitData['expires'] ?? 0;
            
            if (time() < $rateLimitExpiry) {
                // We're still in the rate limit window
                $minutesRemaining = ceil(($rateLimitExpiry - time()) / 60);
                throw new Exception("API rate limit exceeded. Please try again in approximately {$minutesRemaining} minutes.", 429);
            }
        }
        
        // Enhanced verification of image file with detailed logging
        logMessage("Verifying image at path: " . $imagePath);
        
        if (!file_exists($imagePath)) {
            logMessage("ERROR: Image file does not exist: " . $imagePath);
            throw new Exception('Image file not found: ' . $imagePath);
        }
        
        if (!is_readable($imagePath)) {
            logMessage("ERROR: Image file is not readable: " . $imagePath);
            throw new Exception('Image file not readable: ' . $imagePath);
        }
        
        $fileSize = filesize($imagePath);
        logMessage("Image file verified: exists=true, readable=true, size=" . $fileSize . " bytes");
        
        // Read the image file with error checking
        logMessage("Reading image file contents...");
        $imageContent = @file_get_contents($imagePath);
        if ($imageContent === false) {
            $errorMessage = error_get_last()['message'] ?? 'Unknown error';
            logMessage("ERROR: Failed to read image file: " . $errorMessage);
            throw new Exception('Failed to read image file: ' . $errorMessage);
        }
        
        logMessage("Successfully read image file. Content length: " . strlen($imageContent) . " bytes");
        
        // Base64 encode the image
        logMessage("Base64 encoding image data...");
        $imageData = base64_encode($imageContent);
        logMessage("Base64 encoding complete. Encoded length: " . strlen($imageData) . " characters");
        
        // Get MIME type with error checking
        logMessage("Determining MIME type...");
        $mimeType = @mime_content_type($imagePath);
        
        if (!$mimeType) {
            logMessage("ERROR: Could not determine MIME type of the image");
            throw new Exception('Could not determine MIME type of the image');
        }
        
        logMessage("MIME type determined: " . $mimeType);
        
        // Prepare the request data for Gemini 2.0 Flash Preview Image Generation
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
                        // The Gemini 2.0 Flash Preview model supports both text and image input
                    ]
                ]
            ],
            'generationConfig' => [
                // Reduce temperature for more consistent and deterministic results
                'temperature' => 0.2,
                'topK' => 32,
                'topP' => 1,
                'maxOutputTokens' => 4096,
                // Define that we want both image and text in the response
                'responseModalities' => ["IMAGE", "TEXT"],
                // IMPORTANT: API only allows specific MIME types for responseMimeType
                // Using text/plain as required by the API (not image/png which caused errors)
                'responseMimeType' => "text/plain"
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
        
        // Log that we're using the image generation model with verbose debugging
        logMessage('Using Gemini 2.0 Flash Preview Image Generation model with prompt: ' . substr($prompt, 0, 100) . '...');
        
        // Verify all components of the API request are properly set
        logMessage('VERIFICATION: Request parts check:');
        logMessage('- Text prompt: ' . (isset($data['contents'][0]['parts'][0]['text']) ? 'YES (' . strlen($data['contents'][0]['parts'][0]['text']) . ' chars)' : 'NO'));
        logMessage('- Image data: ' . (isset($data['contents'][0]['parts'][1]['inlineData']['data']) ? 'YES (' . strlen($data['contents'][0]['parts'][1]['inlineData']['data']) . ' chars)' : 'NO'));
        logMessage('- Image MIME type: ' . (isset($data['contents'][0]['parts'][1]['inlineData']['mimeType']) ? 'YES (' . $data['contents'][0]['parts'][1]['inlineData']['mimeType'] . ')' : 'NO'));
        logMessage('- Role specified: ' . (isset($data['contents'][0]['role']) ? 'YES (' . $data['contents'][0]['role'] . ')' : 'NO'));
        logMessage('- Response modalities: ' . (isset($data['generationConfig']['responseModalities']) ? 'YES (' . implode(',', $data['generationConfig']['responseModalities']) . ')' : 'NO'));
        logMessage('- Response MIME type: ' . (isset($data['generationConfig']['responseMimeType']) ? 'YES (' . $data['generationConfig']['responseMimeType'] . ')' : 'NO'));
        
        // Added comment to explain improvements made to the AI design generation:
        // 1. Reduced temperature from 0.4 to 0.2 for more consistent results
        // 2. Enhanced prompt to strictly maintain room dimensions and structure
        // 3. Added specific instructions about preserving architectural elements
        // 4. Improved output quality requirements in the prompt
        // 5. Fixed responseMimeType to use text/plain as required by the API
        // These changes ensure the AI respects the original room structure and dimensions
        // while only changing style elements, furniture, and colors.
        
        // Log the request data (without the actual image data)
        $logData = $data;
        if (isset($logData['contents'][0]['parts'][1]['inlineData']['data'])) {
            $logData['contents'][0]['parts'][1]['inlineData']['data'] = '[BASE64_IMAGE_DATA]';
        }
        logMessage('Sending request to Gemini API: ' . print_r($logData, true));
        
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
            CURLOPT_TIMEOUT => 120, // 2 minutes timeout
            CURLOPT_VERBOSE => true,
            CURLOPT_STDERR => fopen('curl_debug.log', 'w+')
        ];
        
        curl_setopt_array($ch, $options);
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        // Log cURL info for debugging
        $curlInfo = curl_getinfo($ch);
        logMessage('cURL Info: ' . print_r($curlInfo, true));
        
        // Close cURL resource
        curl_close($ch);
        
        // Check for cURL errors
        if ($response === false) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        logMessage('API Response (HTTP ' . $httpCode . '): ' . substr($response, 0, 1000) . '...');
        
        // Check HTTP status code
        if ($httpCode !== 200) {
            $errorMsg = 'API request failed with HTTP code ' . $httpCode;
            
            // Handle rate limit errors specially (429)
            if ($httpCode === 429) {
                // Save the rate limit information for future requests
                $rateLimitFile = LOGS_DIR . 'rate_limit.txt';
                $expiryTime = time() + (15 * 60); // Default to 15 minutes cooldown
                
                // Parse the response for more details
                $responseData = @json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($responseData['error'])) {
                    logMessage('Received rate limit error from API: ' . $responseData['error']['message']);
                }
                
                // Store the rate limit information
                file_put_contents($rateLimitFile, json_encode([
                    'expires' => $expiryTime,
                    'timestamp' => time(),
                    'message' => 'Rate limit hit at ' . date('Y-m-d H:i:s'),
                ]));
                
                // Throw a more user-friendly error
                throw new Exception("The AI design service is temporarily unavailable due to high demand. Please try again in approximately 15 minutes.", 429);
            }
            
            if ($response) {
                $errorMsg .= '. Response: ' . $response;
            }
            throw new Exception($errorMsg);
        }
        
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
                // Log detailed quota error for debugging
                logMessage('Quota limit exceeded: ' . $errorMessage);
                if (isset($responseData['error']['details'])) {
                    logMessage('Error details: ' . print_r($responseData['error']['details'], true));
                }
                
                // Provide a more user-friendly message
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
        
        // Extract the generated images from Gemini 2.0 Flash Preview model response - with enhanced debugging
        $images = [];
        
        // Start detailed response analysis
        logMessage('RESPONSE ANALYSIS BEGIN ========================');
        logMessage('HTTP Status Code: ' . $httpCode);
        
        // Get response structure overview for debugging
        $responseKeys = isset($responseData) && is_array($responseData) ? array_keys($responseData) : [];
        logMessage('Response top-level keys: ' . implode(', ', $responseKeys));
        
        // Try to get candidates info
        if (isset($responseData['candidates']) && is_array($responseData['candidates'])) {
            logMessage('Candidates count: ' . count($responseData['candidates']));
            
            if (count($responseData['candidates']) > 0 && isset($responseData['candidates'][0]['content'])) {
                $contentKeys = is_array($responseData['candidates'][0]['content']) ? array_keys($responseData['candidates'][0]['content']) : [];
                logMessage('First candidate content keys: ' . implode(', ', $contentKeys));
                
                if (isset($responseData['candidates'][0]['content']['parts']) && is_array($responseData['candidates'][0]['content']['parts'])) {
                    logMessage('Parts count: ' . count($responseData['candidates'][0]['content']['parts']));
                    
                    // Log details about each part
                    foreach($responseData['candidates'][0]['content']['parts'] as $index => $part) {
                        $partKeys = is_array($part) ? array_keys($part) : [];
                        logMessage('Part ' . $index . ' keys: ' . implode(', ', $partKeys));
                        
                        if (isset($part['text'])) {
                            logMessage('Part ' . $index . ' has text: ' . substr($part['text'], 0, 100) . '...');
                        }
                        
                        if (isset($part['inlineData'])) {
                            $inlineDataKeys = is_array($part['inlineData']) ? array_keys($part['inlineData']) : [];
                            logMessage('Part ' . $index . ' has inlineData with keys: ' . implode(', ', $inlineDataKeys));
                            
                            if (isset($part['inlineData']['mimeType'])) {
                                logMessage('Part ' . $index . ' inlineData mimeType: ' . $part['inlineData']['mimeType']);
                            }
                            
                            if (isset($part['inlineData']['data'])) {
                                logMessage('Part ' . $index . ' has inlineData with data length: ' . strlen($part['inlineData']['data']) . ' chars');
                            }
                        }
                    }
                } else {
                    logMessage('No parts found in the response or parts is not an array');
                }
            } else {
                logMessage('No content found in the first candidate or candidate is empty');
            }
        } else {
            logMessage('No candidates found in the response or candidates is not an array');
        }
        
        // Check if there were any errors or warnings in the response
        if (isset($responseData['error'])) {
            logMessage('RESPONSE ERROR: ' . ($responseData['error']['message'] ?? 'Unknown error'));
        }
        
        // Save a truncated version of the response for debugging
        logMessage('Saving first 1000 characters of response for debugging');
        
        // The new model returns base64 images directly in the parts array
        if (isset($responseData['candidates'][0]['content']['parts'])) {
            foreach ($responseData['candidates'][0]['content']['parts'] as $part) {
                // For Gemini 2.0 Flash Preview, image data will be in the 'inlineData' field
                if (isset($part['inlineData']) && isset($part['inlineData']['data'])) {
                    // This is the base64 encoded image data
                    $images[] = $part['inlineData']['data'];
                }
                // Also check if images are delivered as text (base64 encoded)
                else if (isset($part['text']) && strpos($part['text'], 'data:image') === 0) {
                    // Extract base64 data from data URL
                    $base64Data = explode(',', $part['text'])[1] ?? '';
                    if (!empty($base64Data)) {
                        $images[] = $base64Data;
                    }
                }
                // Plain base64 strings without data URL prefix
                else if (isset($part['text']) && preg_match('/^[a-zA-Z0-9+\/=]+$/', trim($part['text']))) {
                    $images[] = trim($part['text']);
                }
            }
        }
        
        // Log how many images were extracted
        logMessage('Extracted ' . count($images) . ' images from the response');
        
        return [
            'success' => !empty($images),
            'images' => array_slice($images, 0, $count),
            'raw_response' => $responseData // For debugging
        ];
    } catch (Exception $e) {
        // Log the error
        logMessage('Error in generateImageWithGemini: ' . $e->getMessage());
        throw $e; // Re-throw to be handled by the main try-catch
    }
}

// Main execution
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Only POST is allowed.', 405);
    }
    
    // Log the incoming request for debugging
    logMessage("\n" . str_repeat('=', 80));
    logMessage("New request");
    logMessage('POST data: ' . print_r($_POST, true));
    logMessage('FILES data: ' . print_r($_FILES, true));
    
    // Validate file upload - Add detailed logging
    logMessage("Checking file upload...");
    if (!isset($_FILES['imageUpload'])) {
        logMessage("ERROR: No file uploaded - imageUpload not in _FILES array");
        throw new Exception('Please upload an image file. No file was received.');
    }
    
    if ($_FILES['imageUpload']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $errorMsg = isset($uploadErrors[$_FILES['imageUpload']['error']]) ? 
            $uploadErrors[$_FILES['imageUpload']['error']] : 
            'Unknown upload error';
        logMessage("ERROR: File upload error: " . $errorMsg);
        throw new Exception('Image upload failed: ' . $errorMsg);
    }
    
    $file = $_FILES['imageUpload'];
    logMessage("File received: " . $file['name'] . " (" . $file['size'] . " bytes, type: " . $file['type'] . ")");
    
    // Validate the file
    $validation = validateUploadedFile($file);
    if (!$validation['success']) {
        logMessage("ERROR: File validation failed: " . $validation['error']);
        throw new Exception($validation['error']);
    }
    logMessage("File validation successful. MIME type: " . $validation['mimeType']);
    
    // Generate a unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = generateUniqueFilename($extension);
    $targetPath = UPLOAD_DIR . $filename;
    logMessage("Generated target path: " . $targetPath);
    
    // Move the uploaded file
    logMessage("Moving file from " . $file['tmp_name'] . " to " . $targetPath);
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        logMessage("ERROR: Failed to move uploaded file. Check permissions and path.");
        throw new Exception('Failed to move uploaded file to target location.');
    }
    logMessage("File successfully moved to target location.");
    
    // Verify the file exists and is readable
    if (!file_exists($targetPath)) {
        logMessage("ERROR: File does not exist at target path after move.");
        throw new Exception('File not found at target path after move.');
    }
    if (!is_readable($targetPath)) {
        logMessage("ERROR: File is not readable at target path.");
        throw new Exception('File is not readable at target path.');
    }
    $fileSize = filesize($targetPath);
    logMessage("File is ready for processing: exists=true, readable=true, size=" . $fileSize . " bytes");
    
    // Construct the prompt from form data - extract user preferences
    $roomType = $_POST['roomType'] ?? '';
    $designStyle = $_POST['designStyle'] ?? '';
    $colorScheme = $_POST['colorScheme'] ?? '';
    $styleIntensity = $_POST['styleIntensity'] ?? '2';
    $additionalInstructions = $_POST['additionalInstructions'] ?? '';
    $variationCount = min(4, max(1, intval($_POST['variationCount'] ?? 1)));
    
    // Map style intensity slider values to descriptive text
    $intensityText = '';
    switch($styleIntensity) {
        case '1': $intensityText = 'subtle'; break;
        case '2': $intensityText = 'moderate'; break;
        case '3': $intensityText = 'strong'; break;
        case '4': $intensityText = 'bold'; break;
        default: $intensityText = 'moderate';
    }
    
    // Build a comprehensive prompt optimized for Gemini 2.0 Flash Preview model
    // The prompt structure follows best practices for AI image generation:
    // 1. Clear instruction about what to generate
    // 2. Specific details about style and design elements
    // 3. Color scheme information
    // 4. Additional context and requirements
    // 5. Specific output format instructions
    $prompt = "I'm uploading a photo of my $roomType. Create a realistic redesign of this exact room using $designStyle style.";
    $prompt .= " IMPORTANT: Strictly maintain the exact same dimensions, proportions, wall structure, doorways, windows, and architectural elements as the original photo.";
    $prompt .= " Only change the decoration, furniture, colors, and finishes while preserving the exact room structure, layout, and perspective.";
    $prompt .= " Transform the room with $intensityText $designStyle elements and use a color scheme of $colorScheme.";
    $prompt .= " The output image must have exactly the same aspect ratio and composition as the input image.";
    
    // Add specific design guidance based on style selected
    switch($designStyle) {
        case 'scandinavian':
            $prompt .= " Include light woods, minimal furnishings, neutral colors with pops of muted tones, and natural textures.";
            break;
        case 'modern':
            $prompt .= " Feature clean lines, minimal ornamentation, bold statement pieces, and a mix of materials like glass, metal, and wood.";
            break;
        case 'minimalist':
            $prompt .= " Focus on essential elements, open space, neutral colors, and hidden storage solutions to reduce visual clutter.";
            break;
        case 'industrial':
            $prompt .= " Incorporate exposed brick or concrete, metal fixtures, vintage or repurposed furniture, and an unfinished, raw aesthetic.";
            break;
        case 'traditional':
            $prompt .= " Include classic furniture pieces, symmetrical arrangements, rich color palettes, and decorative details like crown molding or wainscoting.";
            break;
        case 'rustic':
            $prompt .= " Feature natural wood elements, stone accents, vintage accessories, and a warm, cozy atmosphere with textural elements.";
            break;
    }
    
    // Add any additional user instructions if provided
    if (!empty($additionalInstructions)) {
        $prompt .= " Additional specifications: $additionalInstructions.";
    }
    
    // Add specific output instructions for the Gemini model
    $prompt .= " Generate a photorealistic, high-resolution image of this redesign that precisely matches the dimensions and composition of the input image.";
    $prompt .= " The image should be highly detailed, with realistic lighting, shadows, and textures that enhance the $designStyle aesthetic.";
    $prompt .= " Ensure the image has proper proportions with no distortion and maintains a consistent perspective with the original photo.";
    $prompt .= " The final result should be a high-quality image with no text overlays, watermarks, or artifacts.";
    
    // Log the final prompt for debugging
    logMessage("Generated prompt: $prompt");
    
    // Log before generating image
    logMessage("Attempting to generate image with path: " . $targetPath);
    
    // Process the image and generate design
    $result = generateImageWithGemini($targetPath, $prompt, $variationCount);
    
    // Log successful generation
    logMessage('Generation successful. ' . count($result['images'] ?? []) . ' images generated.');
    
    // Clean up the uploaded file
    if (file_exists($targetPath)) {
        @unlink($targetPath);
    }
    
    // Return the result
    sendJsonResponse([
        'success' => true,
        'images' => $result['images'] ?? [],
        'message' => 'Design generated successfully.'
    ]);
    
} catch (Exception $e) {
    // Clean up uploaded file if it exists
    if (isset($targetPath) && file_exists($targetPath)) {
        @unlink($targetPath);
    }
    
    // Log the error
    $errorMessage = 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
    logMessage($errorMessage);
    
    // Send error response
    // Get the error code from the exception, default to 500 if not set
    $statusCode = $e->getCode();
    // Make sure it's a valid HTTP status code, otherwise default to 500
    $statusCode = ($statusCode >= 100 && $statusCode < 600) ? $statusCode : 500;
    
    // For rate limit errors, use the exception message directly
    if ($statusCode === 429) {
        sendJsonResponse([
            'success' => false,
            'error' => $e->getMessage()
        ], 429);
    } else {
        // For other errors, include more details
        sendJsonResponse([
            'success' => false,
            'error' => $e->getMessage()
        ], $statusCode);
    }
} catch (Throwable $t) {
    // Clean up uploaded file if it exists
    if (isset($targetPath) && file_exists($targetPath)) {
        @unlink($targetPath);
    }
    
    // Catch any other throwable (like parse errors)
    $errorMessage = 'Fatal Error: ' . $t->getMessage() . ' in ' . $t->getFile() . ' on line ' . $t->getLine();
    logMessage($errorMessage);
    
    // Send error response
    sendJsonResponse([
        'success' => false,
        'error' => 'A server error occurred. Please try again later.'
    ], 500);
}
