<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') { 
    header("Location: index.php");
    exit;
}

// Set the current page for the sidebar
$current_page = 'custom_builds';

require_once 'db_connect.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    
    // Check if payment_status column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM custom_requests LIKE 'payment_status'");
    $paymentStatusExists = $checkColumn->num_rows > 0;
    
    try {
        if ($paymentStatusExists) {
            $payment_status = $_POST['payment_status'];
            $stmt = $conn->prepare("UPDATE custom_requests SET status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssi", $status, $payment_status, $id);
        } else {
            $stmt = $conn->prepare("UPDATE custom_requests SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
        }
        
        if ($stmt->execute()) {
            $success = "Custom build request updated successfully";
            // If payment_status column doesn't exist, add it
            if (!$paymentStatusExists && isset($_POST['payment_status'])) {
                $conn->query("ALTER TABLE custom_requests ADD COLUMN payment_status ENUM('pending', 'paid') NOT NULL DEFAULT 'pending' AFTER status");
                // Update the payment status for this record
                $conn->query("UPDATE custom_requests SET payment_status = '{$_POST['payment_status']}' WHERE id = $id");
            }
        } else {
            throw new Exception($conn->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $error = "Error updating custom build request: " . $e->getMessage();
    }
    
    // Refresh the page to get updated data
    header("Location: " . $_SERVER['PHP_SELF'] . (isset($success) ? '?success=1' : '?error=1'));
    exit();
}

// Check for success/error in URL
if (isset($_GET['success'])) {
    $success = "Custom build request updated successfully";
} elseif (isset($_GET['error'])) {
    $error = "Error updating custom build request";
}

// First, check if payment_status column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM custom_requests LIKE 'payment_status'");
$paymentStatusExists = $checkColumn->num_rows > 0;

// Get all custom build requests with user information
$query = "SELECT cr.*, u.name as user_name, u.email as user_email, 
          " . ($paymentStatusExists ? "cr.payment_status" : "'pending' as payment_status") . " 
          FROM custom_requests cr 
          LEFT JOIN users u ON cr.user_id = u.id 
          ORDER BY cr.created_at DESC";
$result = $conn->query($query);

$custom_requests = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $custom_requests[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Custom Builds - Admin Panel</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Reset all styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body, html {
            width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        
        /* Custom layout structure */
        #admin-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
        }
        
        /* Preserve navigation styles from admin.css but fix positioning */
        .navigation {
            position: fixed !important;
            width: 250px !important;
            height: 100% !important;
            z-index: 1000 !important;
            transition: 0.5s !important;
            left: 0 !important;
        }
        
        /* Fix the main content area */
        #admin-content {
            margin-left: 250px !important;
            width: calc(100% - 250px) !important;
            min-height: 100vh !important;
            transition: margin-left 0.3s ease, width 0.3s ease !important;
            position: relative !important;
            overflow-x: hidden !important;
        }
        
        /* Handle sidebar toggle states */
        .navigation.active {
            width: 70px !important;
        }
        
        #admin-content.expanded {
            margin-left: 70px !important;
            width: calc(100% - 70px) !important;
        }
        
        /* Fix topbar styling */
        .topbar {
            width: 100% !important;
            padding: 10px 20px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1) !important;
        }
        
        /* User image styling */
        .user {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .user img {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            cursor: pointer !important;
        }
        
        /* Make sure the toggle button works */
        .toggle {
            cursor: pointer !important;
            font-size: 24px !important;
        }
        
        /* Content container */
        .content {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
            padding: 20px !important;
            box-sizing: border-box !important;
        }
        
        /* Override any existing styles */
        .main, .container {
            all: unset !important;
            display: contents !important;
        }
        
        .custom-builds-container {
            width: 100%;
            max-width: 100%;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            overflow-x: hidden;
        }
        
        .custom-builds-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .custom-builds-header h1 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }
        
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            table-layout: auto;
            max-width: 100%;
        }
        
        .details-table th,
        .details-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            white-space: normal;
            word-wrap: break-word;
            vertical-align: top;
            max-width: 200px; /* Adjust as needed */
        }
        
        .details-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        .custom-builds-table th,
        .custom-builds-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .custom-builds-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .custom-builds-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-in_progress {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .payment-pending {
            color: #856404;
        }
        
        .payment-paid {
            color: #155724;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #138496;
        }
        
        .build-details {
            display: block;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            width: 100%;
            box-sizing: border-box;
            overflow: visible;
        }
        
        .details-cell {
            padding: 0 !important;
            border-top: none !important;
        }
        
        .details-row {
            background-color: #f8f9fa !important;
        }
        
        .details-row td {
            background-color: #f8f9fa !important;
        }
        
        .details-row > td {
            padding: 0 !important;
            border: none !important;
            background-color: #f8f9fa;
        }
        
        .details-cell {
            padding: 20px !important;
            background-color: #f8f9fa !important;
            white-space: normal !important;
            max-width: 100%;
            overflow: hidden;
        }
        
        .details-cell .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .details-row > td {
            padding: 0 !important;
            border: none !important;
            background-color: #f8f9fa;
        }
        
        .details-cell {
            padding: 20px;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        
        .build-details.show {
            display: block;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
            width: 100%;
            overflow: visible;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            display: block;
        }
        
        .detail-value {
            color: #6c757d;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .no-requests {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .no-requests i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        
        .main {
            position: relative;
            width: calc(100% - 300px);
            margin-left: 300px;
            min-height: 100vh;
            background: #f5f5f5;
            transition: 0.5s;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            overflow: visible;
        }
        
        .main-content {
            flex: 1;
            width: 100%;
            max-width: 100%;
            overflow: visible;
        }
        
        .table-wrapper {
            width: 100%;
            margin-top: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        @media (max-width: 1200px) {
            .main {
                width: 100% !important;
                margin-left: 0 !important;
                padding: 15px !important;
            }
            .navigation.active + .main {
                margin-left: 300px !important;
                width: calc(100% - 300px) !important;
            }
            
            .details-table td, 
            .details-table th {
                white-space: normal;
                padding: 8px 10px;
                display: block;
                width: 100% !important;
                box-sizing: border-box;
            }
            
            .details-cell .details-grid {
                grid-template-columns: 1fr;
            }
            
            .details-table thead {
                display: none;
            }
            
            .details-table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                overflow: hidden;
                width: 100%;
                box-sizing: border-box;
            }
            
            .details-table td::before {
                content: attr(data-label);
                font-weight: 600;
                display: inline-block;
                width: 120px;
                color: #6c757d;
            }
            
            .details-cell {
                padding: 15px !important;
            }
        }
    </style>
</head>
<body>
    <!-- Custom wrapper for admin layout -->
    <div id="admin-wrapper">
        <!-- Include the original sidebar -->
        <?php include 'admin_sidebar.php'; ?>
        
        <!-- Custom content container -->
        <div id="admin-content">
            <!-- Original main div for compatibility -->
            <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <i class='bx bx-menu'></i>
                </div>
                <div class="user">
                    <img src="photos/adminphoto.JPG" alt="Admin">
                </div>
            </div>

            <div class="content">
            
            <div class="custom-builds-container">
                <div class="custom-builds-header">
                    <h1><i class='bx bx-customize'></i> Custom Builds</h1>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if (empty($custom_requests)): ?>
                    <div class="no-requests">
                        <i class='bx bx-package'></i>
                        <h3>No custom build requests found</h3>
                        <p>There are no custom build requests at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="main-content">
                        <div class="table-wrapper">
                            <div class="table-responsive">
                                <table class="details-table">
                                    <colgroup>
                                        <col style="width: 60px;">
                                        <col style="width: 200px;">
                                        <col style="width: 150px;">
                                        <col style="width: 150px;">
                                        <col style="width: 100px;">
                                        <col style="width: 120px;">
                                        <col style="width: 100px;">
                                    </colgroup>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Product Type</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($custom_requests as $request): ?>
                                    <tr class="build-row" id="row-<?php echo $request['id']; ?>">
                                        <td data-label="ID">#<?php echo htmlspecialchars($request['id']); ?></td>
                                        <td data-label="Customer">
                                            <?php 
                                            echo htmlspecialchars($request['user_name'] ?? 'Guest'); 
                                            if (isset($request['user_email'])) {
                                                echo '<br><small class="text-muted">' . htmlspecialchars($request['user_email']) . '</small>';
                                            }
                                            ?>
                                        </td>
                                        <td data-label="Product Type"><?php echo htmlspecialchars(ucfirst($request['product_type'])); ?></td>
                                        <td data-label="Status">
                                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $request['status'])); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="payment-<?php echo isset($request['payment_status']) && $request['payment_status'] === 'paid' ? 'paid' : 'pending'; ?>" data-label="Payment">
                                            <?php echo isset($request['payment_status']) ? ucfirst($request['payment_status']) : 'Pending'; ?>
                                        </td>
                                        <td data-label="Date"><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                        <td data-label="Actions">
                                            <button type="button" class="btn btn-edit view-details" data-id="<?php echo $request['id']; ?>">
                                                <i class='bx bx-show'></i> <span class="btn-text">View</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="details-row" id="details-<?php echo $request['id']; ?>" style="display: none;">
                                        <td colspan="7" class="details-cell">
                                            <div class="build-details">
                                                <div class="details-grid">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Style:</span>
                                                        <span class="detail-value"><?php echo htmlspecialchars(ucfirst($request['style'])); ?></span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Material:</span>
                                                        <span class="detail-value"><?php echo htmlspecialchars(ucfirst($request['material'])); ?></span>
                                                    </div>
                                                    <?php if ($request['wood_type']): ?>
                                                        <div class="detail-item">
                                                            <span class="detail-label">Wood Type:</span>
                                                            <span class="detail-value"><?php echo htmlspecialchars(ucfirst($request['wood_type'])); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($request['fabric_type']): ?>
                                                        <div class="detail-item">
                                                            <span class="detail-label">Fabric Type:</span>
                                                            <span class="detail-value"><?php echo htmlspecialchars(ucfirst($request['fabric_type'])); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Color:</span>
                                                        <span class="detail-value"><?php echo htmlspecialchars(ucfirst($request['color'])); ?></span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Finish Type:</span>
                                                        <span class="detail-value"><?php echo htmlspecialchars(ucfirst($request['finish_type'])); ?></span>
                                                    </div>
                                                    <?php 
                                                    // Format dimensions display
                                                    if (!empty($request['dimensions'])) {
                                                        $dims = explode('x', $request['dimensions']);
                                                        if (count($dims) === 3): ?>
                                                            <div class="detail-item">
                                                                <span class="detail-label">Dimensions:</span>
                                                                <div class="detail-value">
                                                                    <div>Width: <?php echo htmlspecialchars($dims[0]); ?> cm</div>
                                                                    <div>Depth: <?php echo htmlspecialchars($dims[1]); ?> cm</div>
                                                                    <div>Height: <?php echo htmlspecialchars($dims[2]); ?> cm</div>
                                                                    <div class="text-muted mt-1">
                                                                        (<?php echo htmlspecialchars($request['dimensions']); ?>)
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="detail-item">
                                                                <span class="detail-label">Dimensions:</span>
                                                                <span class="detail-value"><?php echo htmlspecialchars($request['dimensions']); ?></span>
                                                            </div>
                                                        <?php endif;
                                                    }
                                                    
                                                    // Handle add-ons display - decode JSON and handle different possible formats
                                                    $add_ons = [];
                                                    if (!empty($request['add_ons']) && $request['add_ons'] !== '[]') {
                                                        // Try to decode JSON
                                                        $add_ons = json_decode($request['add_ons'], true);
                                                        
                                                        // Check for JSON errors
                                                        if (json_last_error() !== JSON_ERROR_NONE) {
                                                            // Not valid JSON, try to handle as string
                                                            $add_ons = [['name' => $request['add_ons'], 'price' => '']];
                                                        } elseif (empty($add_ons)) {
                                                            // Valid JSON but empty array
                                                            $add_ons = [];
                                                        } elseif (is_array($add_ons) && !isset($add_ons[0]['name'])) {
                                                            // Handle old format (simple array of strings)
                                                            $temp_addons = [];
                                                            foreach ($add_ons as $addon) {
                                                                $temp_addons[] = ['name' => $addon, 'price' => ''];
                                                            }
                                                            $add_ons = $temp_addons;
                                                        }
                                                        
                                                        // Debug add-ons display
                                                        //echo '<pre>'.htmlspecialchars(print_r($add_ons, true)).'</pre>';
                                                    }
                                                    
                                                    if (!empty($add_ons)): ?>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Add-ons:</span>
                                                        <div class="detail-value">
                                                            <?php foreach ($add_ons as $add_on): 
                                                                $addon_name = is_array($add_on) ? ($add_on['name'] ?? '') : $add_on;
                                                                $addon_price = (is_array($add_on) && isset($add_on['price'])) ? $add_on['price'] : '';
                                                                $display_text = $addon_name . ($addon_price ? ' ($' . $addon_price . ')' : '');
                                                            ?>
                                                                <span class="badge bg-primary me-1 mb-1">
                                                                    <?php echo htmlspecialchars(ucfirst($display_text)); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($request['special_requests'])): ?>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Special Requests:</span>
                                                        <span class="detail-value"><?php echo nl2br(htmlspecialchars($request['special_requests'])); ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($request['image_path'])): ?>
                                                    <div class="detail-item" style="margin-top: 15px;">
                                                        <span class="detail-label">Reference Image:</span>
                                                        <div class="image-preview">
                                                            <img src="<?php echo htmlspecialchars($request['image_path']); ?>" alt="Reference Image" class="preview-image" onerror="console.log('Image failed to load: ' + this.src); this.src='placeholder.jpg'; this.onerror=null;">
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Budget:</span>
                                                        <span class="detail-value">$<?php echo number_format($request['budget'], 2); ?></span>
                                                    </div>
                                                     <div class="detail-item">
                                                         <span class="detail-label">Estimated Price:</span>
                                                         <span class="detail-value">$<?php echo number_format($request['estimated_price'], 2); ?></span>
                                                     </div>
                                                 </div>
                                                 
                                                 <!-- New section: Checkout Information -->
                                                 <?php if (!empty($request['order_id']) || !empty($request['address']) || !empty($request['payment_method'])): ?>
                                                 <div class="build-details" style="margin-top: 20px;">
                                                     <h3 style="font-size: 16px; color: #24424c; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 1px solid #eee;">
                                                         <i class="fas fa-shipping-fast" style="margin-right: 8px;"></i> Checkout Information
                                                     </h3>
                                                     
                                                     <div class="details-grid">
                                                         <?php if (!empty($request['order_id'])): ?>
                                                         <div class="detail-item">
                                                             <span class="detail-label">Order ID:</span>
                                                             <span class="detail-value"><?php echo htmlspecialchars($request['order_id']); ?></span>
                                                         </div>
                                                         <?php endif; ?>
                                                         
                                                         <?php if (!empty($request['address'])): ?>
                                                         <div class="detail-item">
                                                             <span class="detail-label">Shipping Address:</span>
                                                             <span class="detail-value"><?php echo htmlspecialchars($request['address']); ?></span>
                                                         </div>
                                                         <?php endif; ?>
                                                         
                                                         <?php if (!empty($request['city']) || !empty($request['zip_code'])): ?>
                                                         <div class="detail-item">
                                                             <span class="detail-label">City/ZIP:</span>
                                                             <span class="detail-value">
                                                                 <?php if (!empty($request['city'])): ?>
                                                                     <?php echo htmlspecialchars($request['city']); ?>
                                                                 <?php endif; ?>
                                                                 <?php if (!empty($request['city']) && !empty($request['zip_code'])): ?>, <?php endif; ?>
                                                                 <?php if (!empty($request['zip_code'])): ?>
                                                                     <?php echo htmlspecialchars($request['zip_code']); ?>
                                                                 <?php endif; ?>
                                                             </span>
                                                         </div>
                                                         <?php endif; ?>
                                                         
                                                         <?php if (!empty($request['governorate'])): ?>
                                                         <div class="detail-item">
                                                             <span class="detail-label">Governorate/State:</span>
                                                             <span class="detail-value"><?php echo htmlspecialchars($request['governorate']); ?></span>
                                                         </div>
                                                         <?php endif; ?>
                                                         
                                                         <?php if (!empty($request['phone'])): ?>
                                                         <div class="detail-item">
                                                             <span class="detail-label">Phone:</span>
                                                             <span class="detail-value"><?php echo htmlspecialchars($request['phone']); ?></span>
                                                         </div>
                                                         <?php endif; ?>
                                                         
                                                         <?php if (!empty($request['payment_method'])): ?>
                                                         <div class="detail-item">
                                                             <span class="detail-label">Payment Method:</span>
                                                             <span class="detail-value">
                                                                 <?php if ($request['payment_method'] === 'cash_on_delivery'): ?>
                                                                     <i class="fas fa-money-bill-wave" style="color: #28a745;"></i> Cash on Delivery
                                                                 <?php elseif ($request['payment_method'] === 'credit_card'): ?>
                                                                     <i class="fas fa-credit-card" style="color: #007bff;"></i> Credit Card
                                                                     <?php if (!empty($request['card_number'])): ?>
                                                                     <br>
                                                                     <small style="color: #6c757d; display: block; margin-top: 5px;">
                                                                         <!-- Show only last 4 digits for security -->
                                                                         Card ending in: <?php echo htmlspecialchars(substr(str_replace(' ', '', $request['card_number']), -4)); ?>
                                                                     </small>
                                                                     <?php endif; ?>
                                                                 <?php else: ?>
                                                                     <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $request['payment_method']))); ?>
                                                                 <?php endif; ?>
                                                             </span>
                                                         </div>
                                                         <?php endif; ?>
                                                         
                                                         <?php if (!empty($request['latitude']) && !empty($request['longitude'])): ?>
                                                         <div class="detail-item">
                                                             <span class="detail-label">Delivery Location:</span>
                                                             <span class="detail-value">
                                                                 <a href="https://www.google.com/maps?q=<?php echo $request['latitude']; ?>,<?php echo $request['longitude']; ?>" target="_blank" class="map-link">
                                                                     <i class="fas fa-map-marker-alt"></i> View on Map
                                                                 </a>
                                                             </span>
                                                         </div>
                                                         <?php endif; ?>
                                                     </div>
                                                 </div>
                                                 <?php endif; ?>
                                                 <!-- End of Checkout Information section -->
                                                 
                                                 <?php /* Raw add-ons JSON display removed to avoid duplication */ ?>
                                                 
                                                 <?php if (!empty($request['special_requests'])): ?>
                                                     <div class="detail-item" style="margin-top: 15px;">
                                                         <span class="detail-label">Special Requests:</span>
                                                         <p class="detail-value"><?php echo nl2br(htmlspecialchars($request['special_requests'])); ?></p>
                                                     </div>
                                                 <?php endif; ?>

                                                
                                                <div class="detail-item" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
                                                    <form method="POST" class="status-form" style="display: flex; gap: 10px; align-items: center;">
                                                        <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                                        
                                                        <div style="flex: 1;">
                                                            <label class="detail-label">Status:</label>
                                                            <select name="status" class="form-control" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                                                                <option value="pending" <?php echo $request['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="in_progress" <?php echo $request['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                                <option value="completed" <?php echo $request['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                <option value="cancelled" <?php echo $request['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <?php if ($paymentStatusExists): ?>
                                                        <div style="flex: 1;">
                                                            <label class="detail-label">Payment Status:</label>
                                                            <select name="payment_status" class="form-control" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                                                                <option value="pending" <?php echo (isset($request['payment_status']) && $request['payment_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="paid" <?php echo (isset($request['payment_status']) && $request['payment_status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                                                            </select>
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <div style="margin-top: 23px;">
                                                            <button type="submit" name="update_status" class="btn btn-edit">
                                                                <i class='bx bx-save'></i> Update
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>
    
    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.querySelector('.toggle');
            const navigation = document.querySelector('.navigation');
            const main = document.querySelector('.main');
            const icon = toggle ? toggle.querySelector('i') : null;
            
            // Toggle sidebar function
            function toggleSidebar() {
                // Toggle active class on navigation
                navigation.classList.toggle('active');
                
                // Toggle expanded class on content container
                const content = document.getElementById('admin-content');
                if (content) {
                    content.classList.toggle('expanded');
                }
                
                // Toggle the menu icon between menu and x
                if (icon) {
                    if (navigation.classList.contains('active')) {
                        icon.classList.remove('bx-menu');
                        icon.classList.add('bx-x');
                        // Hide text in navigation items
                        document.querySelectorAll('.navigation .title').forEach(title => {
                            title.style.display = 'none';
                        });
                    } else {
                        icon.classList.remove('bx-x');
                        icon.classList.add('bx-menu');
                        // Show text in navigation items
                        document.querySelectorAll('.navigation .title').forEach(title => {
                            title.style.display = 'block';
                        });
                    }
                }
            }
            
            // Add click event to toggle button
            if (toggle) {
                toggle.addEventListener('click', toggleSidebar);
            }
            
            // Add hovered class to selected list item
            const list = document.querySelectorAll('.navigation li:not(:first-child)');
            
            function activeLink() {
                list.forEach((item) => {
                    item.classList.remove('hovered');
                });
                this.classList.add('hovered');
            }
            
            // Handle list item interactions
            list.forEach((item) => {
                item.addEventListener('mouseover', activeLink);
                
                // Handle click on mobile
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        toggleSidebar();
                    }
                });
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992 && 
                    !navigation.contains(event.target) && 
                    !toggle.contains(event.target) &&
                    navigation.classList.contains('active')) {
                    toggleSidebar();
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    // Reset styles on desktop
                    navigation.classList.remove('collapsed');
                    main.classList.remove('expanded');
                    if (icon) {
                        icon.classList.remove('bx-x');
                        icon.classList.add('bx-menu');
                        document.querySelectorAll('.navigation .title').forEach(title => {
                            title.style.display = 'block';
                        });
                    }
                }
            });
            
            // Toggle build details
            document.addEventListener('click', function(e) {
                // Check if the clicked element is a view-details button or a child of one
                const viewButton = e.target.closest('.view-details');
                if (viewButton) {
                    e.preventDefault();
                    e.stopPropagation();
                    const requestId = viewButton.getAttribute('data-id');
                    const detailsRow = document.getElementById('details-' + requestId);
                    const btnText = viewButton.querySelector('.btn-text');
                    const icon = viewButton.querySelector('i');
                    
                    if (detailsRow) {
                        // Toggle display
                        const isHidden = window.getComputedStyle(detailsRow).display === 'none';
                        
                        // Hide all other open details first
                        document.querySelectorAll('.details-row').forEach(row => {
                            if (row.id !== 'details-' + requestId) {
                                row.style.display = 'none';
                                const otherId = row.id.replace('details-', '');
                                const otherBtn = document.querySelector(`.view-details[data-id="${otherId}"]`);
                                if (otherBtn) {
                                    otherBtn.innerHTML = '<i class="bx bx-show"></i> <span class="btn-text">View</span>';
                                }
                            }
                        });
                        
                        // Toggle current row
                        detailsRow.style.display = isHidden ? 'table-row' : 'none';
                        
                        // Update button icon and text
                        if (isHidden) {
                            viewButton.innerHTML = '<i class="bx bx-hide"></i> <span class="btn-text">Hide</span>';
                            // Scroll to show the details
                            detailsRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } else {
                            viewButton.innerHTML = '<i class="bx bx-show"></i> <span class="btn-text">View</span>';
                        }
                    }
                }
            });
            
            // Handle form submission with confirmation
            document.addEventListener('submit', function(e) {
                const form = e.target.closest('.status-form');
                if (form) {
                    if (!confirm('Are you sure you want to update this custom build request?')) {
                        e.preventDefault();
                    }
                }
            });
            
        }); // Close the DOMContentLoaded event listener
    </script>
</body>
</html>
