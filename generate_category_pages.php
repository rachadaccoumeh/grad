<?php
/**
 * Enhanced Category Page Generator - RoomGenius
 * 
 * This script generates category pages for all defined categories and offers additional
 * features such as:
 * - Database synchronization to detect missing categories
 * - Category validation to ensure all required files exist
 * - Ability to force regeneration of specific category pages
 * - Visual preview of generated category structure
 * - Status logging for troubleshooting
 */

// Start session for potential admin checks
session_start();

// Check if user is logged in as admin (optional security)
// Uncomment this if you want to restrict this script to admins only
/*
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') { 
    die("Access denied. Admin privileges required.");
} 
*/

// Configuration
$config = [
    'template_file' => 'category_template.php',
    'output_dir' => './', // Current directory, change if needed
    'log_file' => 'category_generator.log',
    'force_regenerate' => isset($_GET['force']) ? explode(',', $_GET['force']) : [],
    'verify_database' => isset($_GET['db_sync']),
    'create_missing' => isset($_GET['create_missing']),
    'debug_mode' => isset($_GET['debug'])
];

// Initialize counters and logs
$stats = [
    'created' => 0,
    'skipped' => 0,
    'errors' => 0,
    'updated' => 0,
    'missing_db' => []
];

$log_messages = [];

// Log function
function logMessage($message, $type = 'info') {
    global $log_messages, $config;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp][$type] $message";
    $log_messages[] = ['message' => $message, 'type' => $type];
    
    if ($config['debug_mode']) {
        echo "$log_entry<br>";
    }
    
    // Also write to log file
    file_put_contents($config['log_file'], $log_entry . PHP_EOL, FILE_APPEND);
}

// Check if template file exists
if (!file_exists($config['template_file'])) {
    die("Error: {$config['template_file']} not found. This file is required.");
}

// Define all categories with their details
$categories = [
    // Main room categories
    "Kitchen" => [
        "title" => "Kitchen Collection",
        "description" => "Transform your kitchen with our collection of modern appliances, stylish cabinets, and functional accessories for the heart of your home.",
        "icon" => "bx-bowl-hot" // BoxIcons class for kitchen
    ],
    "Living Room" => [
        "title" => "Living Room Collection",
        "description" => "Create a comfortable and stylish living space with our diverse range of sofas, coffee tables, entertainment units, and decorative elements.",
        "icon" => "bx-sofa" // BoxIcons class for living room
    ],
    "Bedroom" => [
        "title" => "Bedroom Collection",
        "description" => "Design your perfect sleep sanctuary with our range of beds, mattresses, nightstands, and bedroom accessories for ultimate comfort and relaxation.",
        "icon" => "bx-bed" // BoxIcons class for bedroom
    ],
    "Office" => [
        "title" => "Home Office Collection",
        "description" => "Set up a productive workspace with our ergonomic office furniture, storage solutions, and accessories designed for functionality and comfort.",
        "icon" => "bx-briefcase" // BoxIcons class for office
    ],
    "Dining Room" => [
        "title" => "Dining Room Collection",
        "description" => "Enhance your dining experience with our elegant dining tables, chairs, sideboards, and decorative pieces perfect for family meals and entertaining guests.",
        "icon" => "bx-restaurant" // BoxIcons class for dining
    ],
    "Bathroom" => [
        "title" => "Bathroom Collection",
        "description" => "Revitalize your bathroom with our selection of modern fixtures, vanities, storage solutions, and accessories for a refreshing personal space.",
        "icon" => "bx-bath" // BoxIcons class for bathroom
    ],
    "Game Room" => [
        "title" => "Game Room Collection",
        "description" => "Create the ultimate entertainment space with our gaming furniture, media centers, and recreational accessories for hours of fun and relaxation.",
        "icon" => "bx-game" // BoxIcons class for game room
    ],
    "Gym" => [
        "title" => "Home Gym Collection",
        "description" => "Build your personal fitness space with our quality exercise equipment, storage solutions, and accessories designed for effective home workouts.",
        "icon" => "bx-dumbbell" // BoxIcons class for gym
    ],
    "Prayer Room" => [
        "title" => "Prayer Room Collection", 
        "description" => "Design a peaceful and reverent prayer space with our carefully selected furniture, storage options, and spiritual decor elements.",
        "icon" => "bx-church" // BoxIcons class for prayer room
    ],
    "Garden" => [
        "title" => "Garden Collection",
        "description" => "Transform your outdoor space with our weather-resistant furniture, planters, and decorative elements for a beautiful and functional garden.",
        "icon" => "bx-tree" // BoxIcons class for garden
    ],
    "Workshop" => [
        "title" => "Workshop Collection",
        "description" => "Equip your workshop with durable workbenches, storage systems, and organizational tools for all your DIY and craft projects.",
        "icon" => "bx-wrench" // BoxIcons class for workshop
    ],
    "Closet" => [
        "title" => "Closet Collection",
        "description" => "Maximize your storage space with our innovative closet systems, organizers, and accessories for a tidy and efficient wardrobe.",
        "icon" => "bx-closet" // BoxIcons class for closet
    ]
];

// Synchronize with database if requested
if ($config['verify_database']) {
    $db_categories = syncCategoriesWithDatabase();
    
    // Find categories in DB but not in our list
    if (!empty($db_categories)) {
        $missing_categories = array_diff($db_categories, array_keys($categories));
        
        if (!empty($missing_categories)) {
            logMessage("Found " . count($missing_categories) . " categories in database not in our configuration", "warning");
            
            // Add missing categories with default values if create_missing is enabled
            if ($config['create_missing']) {
                foreach ($missing_categories as $cat) {
                    $title = str_replace('_', ' ', $cat) . " Collection";
                    $categories[$cat] = [
                        "title" => $title,
                        "description" => "Explore our " . strtolower($cat) . " collection featuring quality products selected for style and functionality.",
                        "icon" => "bx-category" // Default icon
                    ];
                    logMessage("Added missing category from database: $cat", "info");
                }
            } else {
                $stats['missing_db'] = $missing_categories;
                foreach ($missing_categories as $cat) {
                    logMessage("Missing category configuration: $cat", "warning");
                }
            }
        }
    }
}

// Generate category pages
foreach ($categories as $category => $details) {
    // Generate a clean filename from the category name
    $filename = strtolower(str_replace(' ', '_', $category)) . '.php';
    $filepath = $config['output_dir'] . $filename;
    
    // Determine if we should create/update this file
    $should_create = !file_exists($filepath);
    $should_update = in_array($category, $config['force_regenerate']) || in_array('all', $config['force_regenerate']);
    
    if (!$should_create && !$should_update) {
        logMessage("Skipping $filename - File already exists. Use ?force=$category to regenerate.", "info");
        $stats['skipped']++;
        continue;
    }
    
    // Extract icon (default if not specified)
    $icon = isset($details['icon']) ? $details['icon'] : 'bx-box';
    
    // Template for the category page content
    $content = <<<EOT
<?php



session_start();
require_once('category_template.php');

// Define category specific variables
\$category_name = "{$category}";
\$page_title = "{$details['title']}";
\$category_description = "{$details['description']}";
\$category_icon = "{$icon}"; // BoxIcons class

// Display the {$category} category page
displayCategoryPage(\$category_name, \$page_title, \$category_description);
?>
EOT;
    
    // Write the content to a file
    if (file_put_contents($filepath, $content) !== false) {
        if ($should_create) {
            logMessage("Created $filename successfully.", "success");
            $stats['created']++;
        } else {
            logMessage("Updated $filename successfully.", "success");
            $stats['updated']++;
        }
    } else {
        logMessage("Error creating $filename. Check directory permissions.", "error");
        $stats['errors']++;
    }
}

/**
 * Function to check database for categories
 * @return array Array of category names from the database
 */
function syncCategoriesWithDatabase() {
    global $stats;
    
    // Database connection details
    $servername = "localhost";
    $username = "root";
    $password = "root123";
    $dbname = "roomgenius_db";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        logMessage("Database connection failed: " . $conn->connect_error, "error");
        return [];
    }
    
    // Query to get unique categories from products table
    $sql = "SELECT DISTINCT category FROM products";
    $result = $conn->query($sql);
    
    $db_categories = [];
    
    if ($result) {
        // Fetch categories from database
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['category'])) {
                $db_categories[] = $row['category'];
            }
        }
        
        logMessage("Found " . count($db_categories) . " categories in database", "info");
    } else {
        logMessage("Error querying database: " . $conn->error, "error");
    }
    
    $conn->close();
    return $db_categories;
}

/**
 * Check which category pages have products
 * @return array Array with category name as key and product count as value
 */
function getCategoryProductCounts() {
    // Database connection details
    $servername = "localhost";
    $username = "root";
    $password = "root123";
    $dbname = "roomgenius_db";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        logMessage("Database connection failed: " . $conn->connect_error, "error");
        return [];
    }
    
    $counts = [];
    
    // Query to get category counts
    $sql = "SELECT category, COUNT(*) as count FROM products GROUP BY category";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $counts[$row['category']] = $row['count'];
        }
    }
    
    $conn->close();
    return $counts;
}

// Get product counts per category if database sync is enabled
$product_counts = $config['verify_database'] ? getCategoryProductCounts() : [];

// Display output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoomGenius Category Generator</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #24424c;
            border-bottom: 2px solid #24424c;
            padding-bottom: 10px;
        }
        .summary {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #fff8e3;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin-top: 0;
            color: #24424c;
        }
        .stat-card .count {
            font-size: 2rem;
            font-weight: bold;
            color: #24424c;
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .category-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }
        .category-card h3 {
            margin-top: 0;
            color: #24424c;
            display: flex;
            align-items: center;
        }
        .category-card h3 i {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        .category-card p {
            margin-bottom: 30px;
            color: #666;
        }
        .status {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 0.9rem;
        }
        .status.success {
            color: #388e3c;
        }
        .status.warning {
            color: #f57c00;
        }
        .product-count {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background-color: #24424c;
            color: white;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 0.9rem;
        }
        .empty-count {
            background-color: #f57c00;
        }
        .logs {
            margin-top: 30px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .log-entry {
            margin-bottom: 8px;
            padding: 8px;
            border-radius: 4px;
        }
        .log-entry.info {
            background-color: #e3f2fd;
        }
        .log-entry.success {
            background-color: #e8f5e9;
        }
        .log-entry.warning {
            background-color: #fff3e0;
        }
        .log-entry.error {
            background-color: #ffebee;
        }
        .actions {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            background-color: #24424c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #1c343d;
        }
        .btn-secondary {
            background-color: #80827b;
        }
        .btn-secondary:hover {
            background-color: #6a6c66;
        }
    </style>
</head>
<body>
    <h1><i class='bx bx-brain'></i><i class='bx bx-couch'></i> RoomGenius Category Generator</h1>
    
    <div class="actions">
        <a href="generate_category_pages.php" class="btn">Generate All Pages</a>
        <a href="generate_category_pages.php?force=all" class="btn">Force Regenerate All</a>
        <a href="generate_category_pages.php?db_sync=1" class="btn">Sync with Database</a>
        <a href="generate_category_pages.php?db_sync=1&create_missing=1" class="btn">Sync & Create Missing</a>
        <a href="generate_category_pages.php?debug=1" class="btn btn-secondary">Debug Mode</a>
        <a href="index.php" class="btn btn-secondary">Back to Home</a>
    </div>
    
    <div class="summary">
        <h2>Generation Summary</h2>
        <div class="stats">
            <div class="stat-card">
                <h3>Categories</h3>
                <div class="count"><?php echo count($categories); ?></div>
            </div>
            <div class="stat-card">
                <h3>Created</h3>
                <div class="count"><?php echo $stats['created']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Updated</h3>
                <div class="count"><?php echo $stats['updated']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Skipped</h3>
                <div class="count"><?php echo $stats['skipped']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Errors</h3>
                <div class="count"><?php echo $stats['errors']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Missing in Config</h3>
                <div class="count"><?php echo count($stats['missing_db']); ?></div>
            </div>
        </div>
        
        <?php if (!empty($stats['missing_db'])): ?>
        <div class="warning">
            <h3>⚠ Categories found in database but missing in configuration:</h3>
            <ul>
                <?php foreach ($stats['missing_db'] as $missing): ?>
                <li><?php echo htmlspecialchars($missing); ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Consider adding these to the $categories array or use the "Sync & Create Missing" option.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <h2>Category Overview</h2>
    <div class="category-grid">
        <?php foreach ($categories as $category => $details): ?>
        <?php 
            $filename = strtolower(str_replace(' ', '_', $category)) . '.php';
            $file_exists = file_exists($config['output_dir'] . $filename);
            $icon = isset($details['icon']) ? $details['icon'] : 'bx-box';
            $product_count = isset($product_counts[$category]) ? $product_counts[$category] : '?';
        ?>
        <div class="category-card">
            <h3><i class='bx <?php echo $icon; ?>'></i> <?php echo htmlspecialchars($category); ?></h3>
            <p><?php echo htmlspecialchars(substr($details['description'], 0, 100) . '...'); ?></p>
            <div class="status <?php echo $file_exists ? 'success' : 'warning'; ?>">
                <?php echo $file_exists ? '✓ File created' : '⚠ File not created'; ?>
            </div>
            <?php if (isset($product_counts[$category])): ?>
            <div class="product-count <?php echo $product_counts[$category] == 0 ? 'empty-count' : ''; ?>">
                <?php echo $product_counts[$category]; ?> products
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (!empty($log_messages)): ?>
    <div class="logs">
        <h2>Log Messages</h2>
        <?php foreach ($log_messages as $log): ?>
        <div class="log-entry <?php echo $log['type']; ?>">
            <?php echo htmlspecialchars($log['message']); ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <script>
        // Simple script to automatically hide the success message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const successMessages = document.querySelectorAll('.alert-success');
                successMessages.forEach(function(msg) {
                    msg.style.opacity = '0';
                    setTimeout(function() {
                        msg.style.display = 'none';
                    }, 500);
                });
            }, 5000);
        });
    </script>
</body>
</html>
