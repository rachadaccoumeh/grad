<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $request_id = intval($_POST['request_id']);
    $status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    $stmt = $conn->prepare("UPDATE custom_requests SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('ssi', $status, $admin_notes, $request_id);
    $stmt->execute();
    
    $_SESSION['success_message'] = 'Request status updated successfully.';
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get all custom requests with user information
$query = "SELECT cr.*, u.name as user_name, u.email as user_email 
          FROM custom_requests cr 
          LEFT JOIN users u ON cr.user_id = u.id 
          ORDER BY cr.created_at DESC";
$result = $conn->query($query);
$requests = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Custom Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .request-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fff;
        }
        .request-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .request-id {
            font-weight: bold;
            color: #555;
        }
        .request-date {
            color: #777;
            font-size: 0.9em;
        }
        .request-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.9em;
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
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .detail-group {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }
        .detail-value {
            color: #333;
        }
        .request-image {
            max-width: 300px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 4px;
        }
        .admin-notes {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .status-form {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .form-group {
            margin-bottom: 10px;
        }
        select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Custom Product Requests</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <?php 
                echo htmlspecialchars($_SESSION['success_message']); 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php foreach ($requests as $request): ?>
            <div class="request-card">
                <div class="request-header">
                    <div>
                        <span class="request-id">Request #<?php echo htmlspecialchars($request['id']); ?></span>
                        <span class="request-date">
                            <?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?>
                        </span>
                    </div>
                    <span class="request-status status-<?php echo htmlspecialchars($request['status']); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                    </span>
                </div>
                
                <div class="request-details">
                    <div class="detail-group">
                        <span class="detail-label">Customer</span>
                        <span class="detail-value">
                            <?php 
                            echo $request['user_name'] 
                                ? htmlspecialchars($request['user_name'] . ' (' . $request['user_email'] . ')')
                                : 'Guest';
                            ?>
                        </span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Product Type</span>
                        <span class="detail-value"><?php echo htmlspecialchars(ucfirst($request['product_type'])); ?></span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Style</span>
                        <span class="detail-value"><?php echo htmlspecialchars($request['style']); ?></span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Material</span>
                        <span class="detail-value"><?php echo htmlspecialchars($request['material']); ?></span>
                    </div>
                    <?php if ($request['wood_type']): ?>
                    <div class="detail-group">
                        <span class="detail-label">Wood Type</span>
                        <span class="detail-value"><?php echo htmlspecialchars($request['wood_type']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($request['fabric_type']): ?>
                    <div class="detail-group">
                        <span class="detail-label">Fabric Type</span>
                        <span class="detail-value"><?php echo htmlspecialchars($request['fabric_type']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-group">
                        <span class="detail-label">Color</span>
                        <span class="detail-value"><?php echo htmlspecialchars($request['color']); ?></span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Finish Type</span>
                        <span class="detail-value"><?php echo htmlspecialchars($request['finish_type']); ?></span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Dimensions</span>
                        <span class="detail-value"><?php echo htmlspecialchars($request['dimensions']); ?></span>
                    </div>
                    <?php if ($request['add_ons']): 
                        $add_ons = json_decode($request['add_ons'], true);
                        if (!empty($add_ons)): ?>
                        <div class="detail-group">
                            <span class="detail-label">Add-ons</span>
                            <span class="detail-value"><?php echo htmlspecialchars(implode(', ', $add_ons)); ?></span>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="detail-group">
                        <span class="detail-label">Budget</span>
                        <span class="detail-value">$<?php echo number_format($request['budget'], 2); ?></span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Estimated Price</span>
                        <span class="detail-value">$<?php echo number_format($request['estimated_price'], 2); ?></span>
                    </div>
                </div>
                
                <?php if ($request['special_requests']): ?>
                    <div class="detail-group">
                        <span class="detail-label">Special Requests</span>
                        <p class="detail-value"><?php echo nl2br(htmlspecialchars($request['special_requests'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($request['image_path']): ?>
                    <div class="detail-group">
                        <span class="detail-label">Reference Image</span>
                        <img src="<?php echo htmlspecialchars($request['image_path']); ?>" alt="Reference Image" class="request-image">
                    </div>
                <?php endif; ?>
                
                <?php if ($request['admin_notes']): ?>
                    <div class="admin-notes">
                        <strong>Admin Notes:</strong>
                        <p><?php echo nl2br(htmlspecialchars($request['admin_notes'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <form action="" method="POST" class="status-form">
                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                    
                    <div class="form-group">
                        <label for="status">Update Status:</label>
                        <select name="status" id="status" required>
                            <option value="pending" <?php echo $request['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $request['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $request['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="rejected" <?php echo $request['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_notes">Admin Notes:</label>
                        <textarea name="admin_notes" id="admin_notes" rows="3" placeholder="Add any notes or updates here..."><?php echo htmlspecialchars($request['admin_notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_status">Update Status</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
