<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Page</title>
    <link rel="stylesheet" href="choice_page.css"> <!-- Link to the CSS file -->
</head>
<body>
    <div class="container">
        <h2>Welcome, Admin ⚜️</h2>
        <p>Choose where you'd like to go:</p>
        <a href="admin.php">Go to Admin Dashboard</a>
        <a href="gallery.php">Go to Gallery</a>
    </div>
</body>
</html>
