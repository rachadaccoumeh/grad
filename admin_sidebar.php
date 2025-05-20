<?php 
if (!isset($_SESSION)) { 
    session_start(); 
}

// Get current page filename without .php
$current_page = $current_page ?? basename($_SERVER['PHP_SELF'], '.php');

// Function to check if menu item is active
function isActive($page, $current) {
    return $page === $current ? 'active' : '';
}
?>

<div class="navigation">
    <ul>
        <!-- Logo item - not clickable -->
        <li>
            <a>
                <span class="icon"><i class="fas fa-brain"></i></span>
                <span class="title">RoomGenius</span>
            </a>
        </li>
        <!-- Dashboard tab - clickable -->
        <li class="<?php echo isActive('dashboard', $current_page); ?>">
            <a href="admin.php">
                <span class="icon"><i class='bx bx-grid-alt'></i></span>
                <span class="title">Dashboard</span>
            </a>
        </li>
        <li class="<?php echo isActive('users', $current_page); ?>">
            <a href="users.php">
                <span class="icon"><i class='bx bx-group'></i></span>
                <span class="title">Users</span>
            </a>
        </li>
        <li class="<?php echo isActive('messages', $current_page); ?>">
            <a href="message.php">
                <span class="icon"><i class='bx bx-message'></i></span>
                <span class="title">Messages</span>
            </a>
        </li>
        <li class="<?php echo isActive('custom_builds', $current_page); ?>">
            <a href="custom_builds.php">
                <span class="icon"><i class='bx bx-customize'></i></span>
                <span class="title">Custom Builds</span>
            </a>
        </li>
        <li class="<?php echo isActive('product', $current_page); ?>">
            <a href="product.php">
                <span class="icon"><i class='bx bx-box'></i></span>
                <span class="title">Product</span>
            </a>
        </li>
        <li class="<?php echo isActive('orders', $current_page); ?>">
            <a href="orders.php">
                <span class="icon"><i class='bx bx-package'></i></span>
                <span class="title">Orders</span>
            </a>
        </li>
        <li>
            <a href="logout.php">
                <span class="icon"><i class='bx bx-log-out'></i></span>
                <span class="title">Sign Out</span>
            </a>
        </li>
    </ul>
</div>

<script>
// Only run this code if toggle script hasn't been initialized
if (!window.sidebarToggleInitialized) {
    // Toggle sidebar
    let toggle = document.querySelector('.toggle');
    let navigation = document.querySelector('.navigation');
    let main = document.querySelector('.main');
    
    if (toggle && navigation && main) {
        toggle.onclick = function() {
            navigation.classList.toggle('active');
            main.classList.toggle('active');
        }
        
        // Add hovered class to selected list item
        let list = document.querySelectorAll('.navigation li');
        function activeLink() {
            list.forEach((item) => {
                item.classList.remove('hovered');
            });
            this.classList.add('hovered');
        }
        
        list.forEach((item) => item.addEventListener('mouseover', activeLink));
    }
    
    // Mark as initialized
    window.sidebarToggleInitialized = true;
}
</script>
