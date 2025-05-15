<?php
session_start();
include('db.php');

// Initialize variables
$token_valid = false;
$token = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if token exists and is valid
    $sql = "SELECT * FROM password_reset WHERE token = ? AND expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $token_valid = true;
        $reset_data = $result->fetch_assoc();
        $user_id = $reset_data['user_id'];
    } else {
        $error_message = "Invalid or expired reset link. Please request a new one.";
    }
}

if (isset($_POST['reset_password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    if (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check token validity
        $check_sql = "SELECT * FROM password_reset WHERE token = ? AND expiry > NOW()";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $token);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $reset_data = $check_result->fetch_assoc();
            $user_id = $reset_data['user_id'];
            
            // Hash the new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Update the user's password
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                // Delete all reset tokens for this user
                $delete_sql = "DELETE FROM password_reset WHERE user_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $user_id);
                $delete_stmt->execute();
                
                $success_message = "Your password has been reset successfully. You can now log in with your new password.";
            } else {
                $error_message = "Failed to update password. Please try again.";
            }
        } else {
            $error_message = "Invalid or expired reset link. Please request a new one.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - RoomGenius</title>
    <link rel="stylesheet" href="index.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <style>
        .form-box.reset-password {
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
        .password-requirements {
            margin-top: 15px;
            font-size: 0.85em;
            color: #666;
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
                <h2>Reset Password<br><span>Create a new password</span></h2>
                <p>Choose a strong password to secure your RoomGenius account.</p>
                <div class="social-icons">
                    <a href="#"><i class='bx bxl-linkedin'></i></a>
                    <a href="#"><i class='bx bxl-facebook'></i></a>
                    <a href="#"><i class='bx bxl-instagram'></i></a>
                    <a href="#"><i class='bx bxl-tiktok'></i></a>
                </div>
            </div>
        </div>

        <div class="logreg-box">
            <div class="form-box reset-password">
                <?php if (isset($success_message)): ?>
                    <div class="message success">
                        <?php echo $success_message; ?>
                        <p><a href="index.php">Return to login</a></p>
                    </div>
                <?php elseif (isset($error_message)): ?>
                    <div class="message error">
                        <?php echo $error_message; ?>
                        <?php if (strpos($error_message, "expired") !== false): ?>
                            <p><a href="forgot_password.php">Request a new reset link</a></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($token_valid || isset($_POST['reset_password'])): ?>
                <?php if (!isset($success_message)): ?>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <h2>Create New Password</h2>
                    
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="input-box">
                        <span class="icon"><i class='bx bxs-lock-alt'></i></span>
                        <input type="password" name="password" required>
                        <label>New Password</label>
                    </div>
                    
                    <div class="input-box">
                        <span class="icon"><i class='bx bxs-lock-alt'></i></span>
                        <input type="password" name="confirm_password" required>
                        <label>Confirm New Password</label>
                    </div>
                    
                    <div class="password-requirements">
                        <p>Password must be at least 8 characters long.</p>
                    </div>

                    <button type="submit" class="btn" name="reset_password">Reset Password</button>
                </form>
                <?php endif; ?>
                <?php elseif (!isset($error_message)): ?>
                    <div class="message error">No valid reset token provided. Please use the link from your email.</div>
                <?php endif; ?>
                
                
            </div>
        </div>
    </div>
</body>
</html>