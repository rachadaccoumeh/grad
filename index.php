<?php
session_start();
include('db.php');

if (isset($_POST['signin'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Use a prepared statement to prevent SQL injection
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect user based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: choice_page.php");
                    break;
                case 'customer':
                    header("Location: gallery.php"); // Redirect to gallery.php for customer role
                    break;
                    // Find this code block in index.php and update it
                case 'companies':
                 // Fetch company_id from companies table
                 $companyQuery = "SELECT company_id FROM companies WHERE user_id = ?";
                 $companyStmt = $conn->prepare($companyQuery);
                 $companyStmt->bind_param("i", $user['id']);
                 $companyStmt->execute();
                 $companyResult = $companyStmt->get_result();
    
    if ($companyResult->num_rows > 0) {
        $companyData = $companyResult->fetch_assoc();
        $_SESSION['company_id'] = $companyData['company_id'];
        $_SESSION['user_id'] = $user['id']; // Add this line to store user_id
        header("Location: companies.php");
    } else {
        echo "<script>alert('Company record not found.');</script>";
    }
    break;
                    
                default:
                    echo "<script>alert('Unknown user role!');</script>";
                    exit();
            }
            exit();
        } else {
            echo "<script>alert('Invalid email or password!');</script>";
            exit();
        }
    } else {
        echo "<script>alert('User not found!');</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoomGenius</title>
    <link rel="stylesheet" href="index.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
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
                <h2>Welcome!<br><span>To Our New Website</span></h2>
                <p><strong>RoomGenius</strong> – Your AI-powered assistant for smart, stylish, and effortless interior design.</p>
                <div class="social-icons">
                    <a href="#"><i class='bx bxl-linkedin'></i></a>
                    <a href="#"><i class='bx bxl-facebook'></i></a>
                    <a href="#"><i class='bx bxl-instagram'></i></a>
                    <a href="#"><i class='bx bxl-tiktok'></i></a>
                </div>
            </div>
        </div>

        <div class="logreg-box">
            <div class="form-box login">
                <form action="index.php" method="post">
                    <h2>Sign In</h2>

                    <div class="input-box">
                        <span class="icon"><i class="ri-mail-line"></i></span>
                        <input type="email" name="email" required>
                        <label>Email</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class='bx bxs-lock-alt'></i></span>
                        <input type="password" name="password" required>
                        <label>Password</label>
                    </div>

                    <div class="remember-forget">
                        <label><input type="checkbox"> Remember Me</label>
                        <a href="#">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn" name="signin">Sign In</button>

                    <div class="login-register">
                        <p>Don't have an account? 
                            <a href="signup.php" class="register-link">Sign Up</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>