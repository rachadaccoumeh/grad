<?php
/**
 * Debug Template for Custom Request Processing
 * This template provides detailed debugging information for custom request processing
 * Only included when debug mode is enabled in process_custom_request.php
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Custom Request Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 25px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .highlight { background-color: yellow; padding: 2px 5px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .file-info { margin-top: 20px; padding: 15px; background: #e9f7fe; border-radius: 5px; }
        .action-buttons { margin-top: 30px; }
        .action-buttons a { display: inline-block; margin-right: 10px; padding: 10px 15px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
        .action-buttons a.secondary { background: #2196F3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Custom Request Processing Debug</h1>
        
        <!-- Main status section -->
        <div class="status">
            <h2>Request Status</h2>
            <p>Status: <span class="<?= $response['success'] ? 'success' : 'error' ?>">
                <?= $response['success'] ? 'SUCCESS' : 'FAILED' ?>
            </span></p>
            <p>Message: <?= htmlspecialchars($response['message']) ?></p>
        </div>
        
        <!-- File upload section -->
        <div class="file-info">
            <h2>File Upload Information</h2>
            
            <!-- Check if FILES data exists -->
            <?php if (isset($_FILES['product_image'])): ?>
            <h3>Uploaded File Details</h3>
            <table>
                <tr>
                    <th>Property</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Name</td>
                    <td><?= htmlspecialchars($_FILES['product_image']['name'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td>Type</td>
                    <td><?= htmlspecialchars($_FILES['product_image']['type'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td>Size</td>
                    <td><?= isset($_FILES['product_image']['size']) ? number_format($_FILES['product_image']['size']) . ' bytes' : 'N/A' ?></td>
                </tr>
                <tr>
                    <td>Temporary File</td>
                    <td><?= htmlspecialchars($_FILES['product_image']['tmp_name'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td>Error Code</td>
                    <td><?= isset($_FILES['product_image']['error']) ? $_FILES['product_image']['error'] . ' (' . get_upload_error_message($_FILES['product_image']['error']) . ')' : 'N/A' ?></td>
                </tr>
                <tr>
                    <td>Temp File Exists?</td>
                    <td><?= isset($_FILES['product_image']['tmp_name']) && file_exists($_FILES['product_image']['tmp_name']) ? 'YES' : 'NO' ?></td>
                </tr>
            </table>
            <?php else: ?>
            <p class="error">No file upload data found in the request.</p>
            <?php endif; ?>
            
            <!-- Display image_path information -->
            <h3>Image Path Information</h3>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Upload Flag Set</td>
                    <td><?= isset($_POST['image_uploaded']) && $_POST['image_uploaded'] === '1' ? 'YES' : 'NO' ?></td>
                </tr>
                <tr>
                    <td>Target Upload Directory</td>
                    <td><?= htmlspecialchars($upload_dir ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td>Directory Exists?</td>
                    <td><?= isset($upload_dir) && is_dir($upload_dir) ? 'YES' : 'NO' ?></td>
                </tr>
                <tr>
                    <td>Directory Writable?</td>
                    <td><?= isset($upload_dir) && is_writable($upload_dir) ? 'YES' : 'NO' ?></td>
                </tr>
                <tr>
                    <td>Final Image Path Used</td>
                    <td class="highlight"><?= htmlspecialchars($image_path_for_db ?? $image_path ?? 'NULL/EMPTY') ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Database section -->
        <div class="database-info">
            <h2>Database Operation</h2>
            <?php if (isset($response['debug_info']['insert_id'])): ?>
            <p>Insert ID: <span class="highlight"><?= $response['debug_info']['insert_id'] ?></span></p>
            <?php endif; ?>
            
            <?php if (isset($saved_image_path)): ?>
            <p>Saved Image Path: <span class="highlight"><?= htmlspecialchars($saved_image_path) ?></span></p>
            <?php endif; ?>
        </div>
        
        <!-- Debug output section -->
        <div class="debug-output">
            <h2>Debug Log</h2>
            <pre><?php 
            if (!empty($response['debug_info'])) {
                foreach ($response['debug_info'] as $log) {
                    echo htmlspecialchars($log) . "\n";
                }
            } else {
                echo "No debug information available.";
            }
            ?></pre>
        </div>
        
        <!-- POST data section -->
        <div class="post-data">
            <h2>POST Data</h2>
            <pre><?php print_r($_POST); ?></pre>
        </div>
        
        <!-- Action buttons -->
        <div class="action-buttons">
            <a href="customize.php">Return to Customization Form</a>
            <a href="#" class="secondary" onclick="window.history.back(); return false;">Go Back</a>
        </div>
    </div>
</body>
</html>
