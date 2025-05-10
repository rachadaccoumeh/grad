<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="category_item.css">
    <title>Category Management - RoomGenius Admin</title>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="admin.php">
                        <span class="icon"><i class="fas fa-brain"></i> <i class="fas fa-couch"></i></span>
                        <span class="title">RoomGenius</span>
                    </a>
                </li>
                <li>
                    <a href="users.php">
                        <span class="icon"><i class='bx bx-group'></i></span>
                        <span class="title">Users</span>
                    </a>
                </li>
                <li>
                    <a href="companies.php">
                        <span class="icon"><i class='bx bx-buildings'></i></span>
                        <span class="title">Companies</span>
                    </a>
                </li>
                <li>
                    <a href="message.php">
                        <span class="icon"><i class='bx bx-message'></i></span>
                        <span class="title">Messages</span>
                    </a>
                </li>
                <li class="active">
                    <a href="category_item.php">
                        <span class="icon"><i class='bx bx-basket'></i></span>
                        <span class="title">Category items</span>
                    </a>
                </li>
                <li>
                    <a href="product.php">
                        <span class="icon"><i class='bx bx-box'></i></span>
                        <span class="title">Product</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php">
                        <span class="icon"><i class='bx bx-receipt'></i></span>
                        <span class="title">Orders</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="icon"><i class='bx bx-log-out'></i></span>
                        <span class="title">Sign out</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Manage Categories</h1>

            <div class="card">
                <h2>Add New Category</h2>
                <form class="category-form" action="category_item.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="category_name">Category Name</label>
                        <input type="text" id="category_name" name="category_name" required>
                    </div>
                    <div class="form-group">
                        <label for="category_description">Description</label>
                        <textarea id="category_description" name="category_description"></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Existing Categories</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Dining Room</td>
                            <td>Furniture for dining areas including tables, chairs, and cabinets.</td>
                            <td>
                                <div class="action-buttons">
                                  <a href="#" class="btn btn-primary">Edit</a>
                                  <a href="#" class="btn btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Living Room</td>
                            <td>Modern sofas, coffee tables, and entertainment units for your living area.</td>
                            <td>
                                <div class="action-buttons">
                                  <a href="#" class="btn btn-primary">Edit</a>
                                  <a href="#" class="btn btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Bedroom</td>
                            <td>Beds, nightstands, and wardrobes for comfortable sleeping quarters.</td>
                            <td>
                                <div class="action-buttons">
                                  <a href="#" class="btn btn-primary">Edit</a>
                                  <a href="#" class="btn btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Office</td>
                            <td>Desks, chairs, and storage solutions for home and professional offices.</td>
                            <td>
                                <div class="action-buttons">
                                  <a href="#" class="btn btn-primary">Edit</a>
                                  <a href="#" class="btn btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Kitchen</td>
                            <td>Modern kitchen cabinets, islands, and dining sets.</td>
                            <td>
                                <div class="action-buttons">
                                  <a href="#" class="btn btn-primary">Edit</a>
                                  <a href="#" class="btn btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Clothing Room</td>
                            <td>Walk-in closets, wardrobes, and storage solutions for clothing.</td>
                            <td>
                                <div class="action-buttons">
                                  <a href="#" class="btn btn-primary">Edit</a>
                                  <a href="#" class="btn btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>Bathroom</td>
                            <td>Modern bathroom designs, vanities, storage units, and fixtures.</td>
                            <td>
                                <div class="action-buttons">
                                  <a href="#" class="btn btn-primary">Edit</a>
                                  <a href="#" class="btn btn-danger">Delete</a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Placeholder for future JavaScript functionality
        document.addEventListener('DOMContentLoaded', function () {
            // Add JS here
        });
    </script>
</body>
</html>
