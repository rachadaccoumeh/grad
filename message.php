<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') { 
    header("Location: index.php");
    exit; 
}

// Set the current page for the sidebar
$current_page = 'messages';

// Database connection
$conn = new mysqli("localhost", "root", "root123", "roomgenius_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine which filter to apply
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Fetch messages based on filter
if ($filter === 'unread') {
    $sql = "SELECT * FROM messages WHERE is_read = 0 ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM messages ORDER BY created_at DESC";
}
$result = $conn->query($sql);

// Handle message actions if any
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action === 'read') {
        $updateSql = "UPDATE messages SET is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete') {
        $deleteSql = "DELETE FROM messages WHERE id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        header("Location: message.php");
        exit();
    }
}

// Get selected message if any
$selectedMessage = null;
if (isset($_GET['view'])) {
    $viewId = (int)$_GET['view'];
    $viewSql = "SELECT * FROM messages WHERE id = ?";
    $stmt = $conn->prepare($viewSql);
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $selectedResult = $stmt->get_result();
    
    if ($selectedResult->num_rows > 0) {
        $selectedMessage = $selectedResult->fetch_assoc();
        
        if ($selectedMessage['is_read'] == 0) {
            $updateSql = "UPDATE messages SET is_read = 1 WHERE id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("i", $viewId);
            $stmt->execute();
        }
    }
    $stmt->close();
}

// Count unread messages
$unreadSql = "SELECT COUNT(*) as unread_count FROM messages WHERE is_read = 0";
$unreadResult = $conn->query($unreadSql);
$unreadCount = $unreadResult->fetch_assoc()['unread_count'];

// Get total message count
$totalSql = "SELECT COUNT(*) as total_count FROM messages";
$totalResult = $conn->query($totalSql);
$totalCount = $totalResult->fetch_assoc()['total_count'];
?>
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
    <link rel="stylesheet" href="message.css">
    <title>Messages - RoomGenius Admin</title>
    <style>
        /* Define CSS variables for dynamic values */
        :root {
            --admin-content-margin: 250px;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }
        
        /* Fix for admin wrapper layout */
        #admin-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Navigation styles */
        .navigation {
            width: var(--sidebar-width);
            transition: width 0.3s ease;
            position: fixed;
            height: 100%;
            z-index: 1000;
        }
        
        /* Collapsed navigation */
        .navigation.active {
            width: var(--sidebar-collapsed-width) !important;
        }
        
        /* Hide text in navigation when collapsed */
        .navigation.active .title {
            display: none;
        }
        
        #admin-content {
            flex: 1;
            margin-left: var(--admin-content-margin); /* Match the width of the navigation */
            transition: margin-left 0.3s ease, width 0.3s ease;
            width: calc(100% - var(--admin-content-margin));
            overflow-x: hidden;
        }
        
        /* When navigation is active (collapsed) */
        .navigation.active + #admin-content {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }
        
        /* Override any absolute positioning in main */
        .main {
            position: relative !important;
            left: 0 !important;
            width: 100% !important;
            margin-left: 0 !important;
        }
    </style>
</head>
<body>
    <div id="admin-wrapper">
        <?php include 'admin_sidebar.php'; ?>
        
        <div id="admin-content">
            <div class="main">
                <div class="topbar">
                    <div class="toggle" onclick="toggleSidebar()">
                        <i class='bx bx-menu'></i>
                    </div>
                    <div class="search">
                        <label>
                            <input type="text" placeholder="Search messages..." id="search-input">
                            <i class='bx bx-search'></i>
                        </label>
                    </div>
                    <div class="user"><img src="photos/adminphoto.JPG" alt="Admin"></div>
            </div>

            <!-- Message Dashboard -->
            <div class="message-container">
                <div class="message-sidebar">
                    <div class="message-header">
                        <h3>Messages</h3>
                        <div class="message-stats">
                            <span class="unread-badge"><?php echo $unreadCount; ?> unread</span>
                        </div>
                    </div>
                    <div class="message-filters">
                        <a href="message.php" class="filter-btn <?php echo !isset($_GET['filter']) ? 'active' : ''; ?>">All (<?php echo $totalCount; ?>)</a>
                        <a href="message.php?filter=unread" class="filter-btn <?php echo isset($_GET['filter']) && $_GET['filter'] == 'unread' ? 'active' : ''; ?>">Unread (<?php echo $unreadCount; ?>)</a>
                    </div>
                    <div class="message-list">
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $activeClass = isset($_GET['view']) && $_GET['view'] == $row['id'] ? 'active' : '';
                                $unreadClass = $row['is_read'] == 0 ? 'unread' : '';
                                $messageDate = date('M d', strtotime($row['created_at']));
                                $messagePreview = substr($row['message'], 0, 100) . (strlen($row['message']) > 100 ? '...' : '');
                        ?>
                        <a href="message.php?view=<?php echo $row['id']; ?><?php echo $filter ? '&filter='.$filter : ''; ?>" class="message-item <?php echo $activeClass; ?> <?php echo $unreadClass; ?>" data-id="<?php echo $row['id']; ?>">
                            <div class="message-avatar">
                                <img src="https://via.placeholder.com/40" alt="User">
                            </div>
                            <div class="message-preview">
                                <div class="message-info">
                                    <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                                    <span class="message-time"><?php echo $messageDate; ?></span>
                                </div>
                                <p class="message-subject"><?php echo htmlspecialchars($row['email']); ?></p>
                                <p class="message-excerpt"><?php echo htmlspecialchars($messagePreview); ?></p>
                            </div>
                        </a>
                        <?php
                            }
                        } else {
                            echo '<div class="no-messages">No messages found</div>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Message Content -->
                <div class="message-content">
                    <?php if ($selectedMessage): ?>
                    <div class="message-view">
                        <div class="message-header-detail">
                            <div class="message-title">
                                <h2>Message from <?php echo htmlspecialchars($selectedMessage['name']); ?></h2>
                                <div class="message-meta">
                                    <span class="from">From: <?php echo htmlspecialchars($selectedMessage['name']); ?> (<?php echo htmlspecialchars($selectedMessage['email']); ?>)</span>
                                    <span class="date"><?php echo date('F j, Y g:i A', strtotime($selectedMessage['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="message-actions">
                                <a href="mailto:<?php echo htmlspecialchars($selectedMessage['email']); ?>" class="action-btn"><i class='bx bx-reply'></i> Reply by Email</a>
                                <a href="message.php?action=delete&id=<?php echo $selectedMessage['id']; ?><?php echo $filter ? '&filter='.$filter : ''; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this message?');"><i class='bx bx-trash'></i> Delete</a>
                            </div>
                        </div>
                        <div class="message-body">
                            <?php echo nl2br(htmlspecialchars($selectedMessage['message'])); ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="no-message-selected">
                        <div class="empty-state">
                            <i class='bx bx-envelope-open'></i>
                            <h3>No message selected</h3>
                            <p>Select a message from the list to view its contents</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar function for the burger menu
        function toggleSidebar() {
            const navigation = document.querySelector('.navigation');
            const main = document.querySelector('.main');
            const adminContent = document.querySelector('#admin-content');
            
            if (navigation) navigation.classList.toggle('active');
            if (main) main.classList.toggle('active');
            
            // Force immediate style update for admin content
            if (adminContent) {
                // Apply transition for smooth animation
                adminContent.style.transition = 'margin-left 0.3s ease, width 0.3s ease';
                
                if (navigation && navigation.classList.contains('active')) {
                    // When sidebar is collapsed
                    adminContent.style.marginLeft = '70px';
                    adminContent.style.width = 'calc(100% - 70px)';
                    document.documentElement.style.setProperty('--admin-content-margin', '70px');
                } else {
                    // When sidebar is expanded
                    adminContent.style.marginLeft = '250px';
                    adminContent.style.width = 'calc(100% - 250px)';
                    document.documentElement.style.setProperty('--admin-content-margin', '250px');
                }
            }
            
            return false;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const messageItems = document.querySelectorAll('.message-item');
            
            // Add clear button to search input
            const searchContainer = document.querySelector('.search label');
            const clearButton = document.createElement('i');
            clearButton.className = 'bx bx-x';
            clearButton.style.position = 'absolute';
            clearButton.style.top = '12px';
            clearButton.style.right = '15px';
            clearButton.style.color = '#999';
            clearButton.style.cursor = 'pointer';
            clearButton.style.display = 'none';
            searchContainer.appendChild(clearButton);
            
            // Function to perform search
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let matchCount = 0;
                
                // Remove any existing "no results" message
                const existingNoResults = document.querySelector('.no-search-results');
                if (existingNoResults) {
                    existingNoResults.remove();
                }
                
                // Filter messages based on search term
                messageItems.forEach(function(item) {
                    // Reset highlights first
                    const contentElements = [
                        item.querySelector('h4'),
                        item.querySelector('.message-subject'),
                        item.querySelector('.message-excerpt')
                    ];
                    
                    contentElements.forEach(el => {
                        if (el.querySelector('.highlight')) {
                            el.textContent = el.textContent; // Remove highlighting
                        }
                    });
                    
                    const name = contentElements[0].textContent.toLowerCase();
                    const email = contentElements[1].textContent.toLowerCase();
                    const excerpt = contentElements[2].textContent.toLowerCase();
                    
                    if (searchTerm === '' || name.includes(searchTerm) || email.includes(searchTerm) || excerpt.includes(searchTerm)) {
                        item.style.display = 'flex';
                        matchCount++;
                        
                        // Add highlighting if there's a search term
                        if (searchTerm !== '') {
                            contentElements.forEach(el => {
                                const text = el.textContent;
                                if (text.toLowerCase().includes(searchTerm)) {
                                    highlightText(el, searchTerm);
                                }
                            });
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Show "no results" message if needed
                if (matchCount === 0 && searchTerm !== '') {
                    const noResults = document.createElement('div');
                    noResults.className = 'no-messages no-search-results';
                    noResults.textContent = 'No messages match your search';
                    document.querySelector('.message-list').appendChild(noResults);
                }
                
                // Update clear button visibility
                clearButton.style.display = searchTerm === '' ? 'none' : 'block';
            }
            
            // Function to highlight search matches
            function highlightText(element, searchTerm) {
                const text = element.textContent;
                const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
                
                // Create highlighted HTML
                const highlightedText = text.replace(regex, '<span class="highlight">$1</span>');
                element.innerHTML = highlightedText;
            }
            
            // Helper function to escape special regex characters
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }
            
            // Function to clear search
            function clearSearch() {
                searchInput.value = '';
                searchInput.focus();
                performSearch();
            }
            
            // Event listeners
            searchInput.addEventListener('input', performSearch);
            clearButton.addEventListener('click', clearSearch);
            
            // Handle keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Clear search on Escape key when search is focused
                if (e.key === 'Escape' && document.activeElement === searchInput) {
                    clearSearch();
                }
                
                // Focus search on Ctrl+F or Cmd+F
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        });
    </script>
</body>
</html>