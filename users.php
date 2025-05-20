<?php 
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') { 
    header("Location: index.php");
    exit;
}

include 'db.php';

// Set the current page for the sidebar
$current_page = 'users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin Dashboard</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="users.css">
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
                <div class="details">
                    <div class="recentOrders">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Date Joined</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT id, name, email, role, created_at, updated_at FROM users ORDER BY created_at DESC";
                                $result = $conn->query($query);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                            <td>{$row['id']}</td>
                                            <td>{$row['name']}</td>
                                            <td>{$row['email']}</td>
                                            <td>{$row['role']}</td>
                                            <td>{$row['created_at']}</td>
                                            <td>{$row['updated_at']}</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>No users found.</td></tr>";
                                }
                                $conn->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
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
                    navigation.classList.remove('active');
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
        });
    </script>
</body>
</html>