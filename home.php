<?php
session_start(); // Start session to access session variables
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="home.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
</head>
<body>
    <div class="banner">
        <div class="navbar">
            <h2 class="logo"> 
                <i class="fas fa-brain"></i> <!-- AI/Brain Icon -->
                <i class="fas fa-couch"></i> <!-- Interior Design -->
                RoomGenius
            </h2>
            <ul>
                <li><a href="home.php">HOME</a></li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="index.php">LOGIN</a></li>
                <?php else: ?>
                    <li><a href="logout.php">LOGOUT</a></li>
                <?php endif; ?>
                <li><a href="about.php">ABOUT</a></li>
                <li><a href="contact.php">CONTACT US</a></li>
            </ul>
        </div>

        <div class="content">
            <h1>DESIGN YOUR HOUSE</h1>
            <p>AI-powered design for your perfect space—customize, visualize, and transform effortlessly</p>
            <div>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <button type="button" onclick="window.location.href='index.php'"><span></span>GET STARTED</button>
                <?php else: ?>
                    <button type="button" onclick="window.location.href='design.php'"><span></span>START DESIGNING</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>