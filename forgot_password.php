<?php
session_start();
include('db.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['reset_request'])) {
    $email = $_POST['email'];
    
    // Check if email exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit;
    }
    
    $stmt->bind_param("s", $email);
    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        exit;
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        
        // Set token expiration (1 hour from now)
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete any existing tokens for this user
        $delete_sql = "DELETE FROM password_reset WHERE user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        
        if (!$delete_stmt) {
            echo "Prepare failed for delete: (" . $conn->errno . ") " . $conn->error;
            exit;
        }
        
        $delete_stmt->bind_param("i", $user_id);
        
        if (!$delete_stmt->execute()) {
            echo "Delete execute failed: (" . $delete_stmt->errno . ") " . $delete_stmt->error;
            exit;
        }
        
        // Store token in database
        $insert_sql = "INSERT INTO password_reset (user_id, token, expiry) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        if (!$insert_stmt) {
            echo "Prepare failed for insert: (" . $conn->errno . ") " . $conn->error;
            exit;
        }
        
        $insert_stmt->bind_param("iss", $user_id, $token, $expiry);
        
        if (!$insert_stmt->execute()) {
            echo "Insert execute failed: (" . $insert_stmt->errno . ") " . $insert_stmt->error;
            exit;
        }
        
        // For debugging: Let's confirm the record was inserted
        echo "<div style='background-color: #f5f5f5; padding: 10px; margin-bottom: 15px; border-left: 5px solid #0070f3;'>
            <p><strong>DEBUG:</strong> Record inserted into password_reset table with:</p>
            <ul>
                <li>user_id: {$user_id}</li>
                <li>token: {$token}</li>
                <li>expiry: {$expiry}</li>
            </ul>
        </div>";
        
        // Create reset link
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
            
            // DEVELOPMENT MODE: Display link on screen instead of sending email
            // In production, you would use a proper email sending service
            $_SESSION['dev_reset_link'] = $reset_link;
            $success_message = "DEVELOPMENT MODE: Password reset link created. Click the link below to reset your password.";
            
            // For production, you would use proper email sending:
            /*
            // Email headers
            $to = $email;
            $subject = "RoomGenius Password Reset";
            $message = "Hello,\n\nYou have requested to reset your password for your RoomGenius account.\n\n";
            $message .= "Please click the link below to reset your password:\n";
            $message .= $reset_link . "\n\n";
            $message .= "This link will expire in 1 hour.\n\n";
            $message .= "If you did not request this password reset, please ignore this email.\n\n";
            $message .= "Regards,\nRoomGenius Team";
            $headers = "From: noreply@roomgenius.com\r\n";
            $headers .= "Reply-To: noreply@roomgenius.com\r\n";
            
            if (mail($to, $subject, $message, $headers)) {
                $success_message = "A password reset link has been sent to your email address.";
            } else {
                $error_message = "Failed to send reset email. Please try again later.";
            }
            */
        } else {
            $error_message = "Failed to process your request. Please try again later.";
        }
    } else {
        // Don't reveal if email exists or not (security best practice)
        $success_message = "If your email is registered, you will receive a password reset link.";
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - RoomGenius</title>
    <link rel="stylesheet" href="index.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <style>
        .form-box.forgot-password {
            width: 100%;
            padding: 40px;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            background-color: rgba(0, 255, 0, 0.1);
            color: green;
        }
        .error {
            background-color: rgba(255, 0, 0, 0.1);
            color: red;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #0070f3;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h2 class="logo">
                <i class="fas fa-brain"></i>
                <i class="fas fa-couch"></i>
                RoomGenius
            </h2>
            <div class="text-sci">
                <h2>Forgot Password<br><span>We're here to help</span></h2>
                <p>Enter your email address and we'll send you a link to reset your password.</p>
                <div class="social-icons">
                    <a href="#"><i class='bx bxl-linkedin'></i></a>
                    <a href="#"><i class='bx bxl-facebook'></i></a>
                    <a href="#"><i class='bx bxl-instagram'></i></a>
                    <a href="#"><i class='bx bxl-tiktok'></i></a>
                </div>
            </div>
        </div>

        <div class="logreg-box">
            <div class="form-box forgot-password">
                <?php if (isset($success_message)): ?>
                    <div class="message success">
                        <?php echo $success_message; ?>
                        <?php if (isset($_SESSION['dev_reset_link'])): ?>
                            <div style="margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 5px; word-break: break-all;">
                                <p style="font-size: 13px; margin-bottom: 8px;"><strong>Development Reset Link:</strong></p>
                                <a href="<?php echo $_SESSION['dev_reset_link']; ?>" style="font-size: 12px; color: #0070f3;"><?php echo $_SESSION['dev_reset_link']; ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <h2>Reset Password</h2>

                    <div class="input-box">
                        <span class="icon"><i class="ri-mail-line"></i></span>
                        <input type="email" name="email" required>
                        <label>Email</label>
                    </div>

                    <button type="submit" class="btn" name="reset_request">Send Reset Link</button>

                    <div class="login-register">
                        <p>Remember your password? 
                            <a href="index.php" class="register-link">Back to Login</a>
                        </p>
                    </div>
                </form>
                
                
            </div>
        </div>
    </div>
</body>
</html>