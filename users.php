<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Admin Dashboard</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="users.css">

</head>
<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li><a href="admin.php">
                    <span class="icon">
                        <i class="fas fa-brain"></i> <i class="fas fa-couch"></i>
                    </span>
                    <span class="title">RoomGenius</span></a></li>
                <li><a class="active" href="users.php">
                    <span class="icon"><i class='bx bx-group'></i>
                </span>
                <span class="title">Users</span></a></li>
                <li><a href="#"><span class="icon"><i class='bx bx-buildings'></i></span><span class="title">Companies</span></a></li>
                <li><a href="message.php"><span class="icon"><i class='bx bx-message'></i></span><span class="title">Messages</span></a></li>
                <li><a href="category_item.php"><span class="icon"><i class='bx bx-basket'></i></span><span class="title">Category items</span></a></li>
                <li><a href="product.php"><span class="icon"><i class='bx bx-box'></i></span><span class="title">Product</span></a></li>
                <li><a href="orders.php"><span class="icon"><i class='bx bx-receipt'></i></span><span class="title">Orders</span></a></li>
                <li><a href="#"><span class="icon"><i class='bx bx-log-out'></i></span><span class="title">Sign out</span></a></li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <h2>All Users</h2>
            </div>

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
                                echo "<tr><td colspan='6'>No customers found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>