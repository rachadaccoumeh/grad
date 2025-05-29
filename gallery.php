<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = "root123"; // Replace with your database password
$dbname = "roomgenius_db"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get user information if logged in
$user_info = null;
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $user_query = "SELECT id, name, email, role, created_at FROM users WHERE id = ?";
  $stmt = $conn->prepare($user_query);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $user_info = $result->fetch_assoc();
  }
  $stmt->close();
}

// Get featured products from database
$query = "SELECT * FROM products WHERE is_featured = 1 ORDER BY date_added DESC";
$result = $conn->query($query);

// Initialize an array to store products
$products = [];
if ($result && $result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $products[] = $row;
  }
}

// Get unique sizes from products (if size field exists in database)
$sizes = [];
foreach ($products as $product) {
  if (isset($product['size']) && !empty($product['size']) && !in_array($product['size'], $sizes)) {
    $sizes[] = $product['size'];
  }
}
sort($sizes); // Sort sizes alphabetically

// Close the connection
$conn->close();

// Format price function
function formatPrice($price) {
  return number_format($price, 0);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RoomGenius Gallery</title>
  <link rel="stylesheet" href="gallery.css">
  <link rel="stylesheet" href="footer.css"> <!-- Adding footer stylesheet -->
  <link rel="stylesheet" href="css/product-modal.css"> <!-- Product Modal CSS -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Adding AOS (Animate On Scroll) library for scroll animations -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  
  <!-- Add styles for user profile popup -->
  <style>
    /* User profile popup styles */
    .user-popup {
      display: none;
      position: fixed;
      top: 80px;
      right: 20px;
      width: 300px;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      z-index: 1000;
      padding: 20px;
      font-family: 'Poppins', sans-serif;
      animation: fadeIn 0.3s ease;
    }
    
    .user-popup.active {
      display: block;
    }
    
    .user-popup-header {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }
    
    .user-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-color: #24424c;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-right: 15px;
    }
    
    .user-info-header h3 {
      margin: 0;
      color: #24424c;
      font-size: 18px;
    }
    
    .user-info-header p {
      margin: 5px 0 0;
      color: #666;
      font-size: 14px;
    }
    
    .user-info-details {
      margin-top: 15px;
    }
    
    .info-item {
      margin-bottom: 12px;
    }
    
    .info-item label {
      display: block;
      font-size: 12px;
      color: #888;
      margin-bottom: 3px;
    }
    
    .info-item p {
      margin: 0;
      font-size: 14px;
      color: #333;
    }
    
    .user-popup-footer {
      margin-top: 20px;
      padding-top: 15px;
      border-top: 1px solid #eee;
      display: flex;
      justify-content: space-between;
    }
    
    .user-popup-footer a {
      text-decoration: none;
      color: #24424c;
      font-size: 14px;
      transition: color 0.2s;
    }
    
    .user-popup-footer a:hover {
      color: #3a6b7e;
    }
    
    .logout-btn {
      background-color: #f1f1f1;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      color: #333;
      font-size: 14px;
      transition: background-color 0.2s;
    }
    
    .logout-btn:hover {
      background-color: #e0e0e0;
    }
    
    .login-message {
      text-align: center;
      padding: 15px 0;
    }
    
    .login-message a {
      color: #24424c;
      text-decoration: none;
      font-weight: bold;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Make user icon clickable */
    .user-container {
      cursor: pointer;
    }
  </style>
</head>
<body class="showCart">
  <!-- Initialize AOS animations -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize AOS animations with optimized settings for better scroll sync
      AOS.init({
        duration: 400, // Faster animations
        easing: 'ease-out',
        once: true, // Only animate once to avoid repeat animations while scrolling
        offset: 50, // Smaller offset to trigger animations sooner
        delay: 0, // No base delay, we'll use individual delays where needed
        throttleDelay: 50, // More responsive to scroll
        mirror: false // Don't animate out when scrolling past
      });
    });
  </script>
  <!-- Header HTML -->
   <div>
  <header>
    <div class="logo"><i class="fas fa-brain"></i>
      <i class="fas fa-couch"></i>
      RoomGenius
    </div>



  <!-- Enhanced search bar with improved UI/UX -->
  <div class="search-bar">
    <input type="text" placeholder="Search for products, styles, categories..." id="searchInput" />
    <button onclick="searchProducts()" aria-label="Search">
      <i class="fas fa-search"></i>
    </button>
  </div>
  <div class="right-section">
    <div class="icons">
      <!-- Separate cart and user into individual containers -->
      <span class="cart-container">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count" id="cartCount">0</span>
      </span>
      <!-- Wishlist icon container with count badge -->
      <span class="wishlist-container" onclick="window.location.href='wishlist.html'">
        <i class="fas fa-heart" style="color: red; font-size: 20px;"></i>
        <span class="wishlist-count" id="wishlistCount">0</span>
      </span>
      
      <!-- User profile icon with click event -->
      <span class="user-container" id="userProfileIcon">
        <i class="fas fa-user"></i>
      </span>
      
     <span>
    </div>
     <div class="ai-button-container">
      <button class="ai-button" onclick="window.location.href='customize.php'">
      <i class="fas fa-cog"></i> customize
      </button>
    </div>
     </span>

    <div class="ai-button-container">
      <button class="ai-button" onclick="window.location.href='ai-page.php'">
        <i class="fas fa-robot"></i> Ai room genie
      </button>
    </div>
  </div>
</header>
</div>

<!-- User Profile Popup -->
<div class="user-popup" id="userPopup">
  <?php if ($user_info): ?>
    <!-- User is logged in, show profile info -->
    <div class="user-popup-header">
      <div class="user-avatar">
        <?php echo strtoupper(substr($user_info['name'], 0, 1)); ?>
      </div>
      <div class="user-info-header">
        <h3><?php echo htmlspecialchars($user_info['name']); ?></h3>
        <p><?php echo htmlspecialchars($user_info['role']); ?></p>
      </div>
    </div>
    <div class="user-info-details">
      <div class="info-item">
        <label>Email</label>
        <p><?php echo htmlspecialchars($user_info['email']); ?></p>
      </div>
      <div class="info-item">
        <label>Member Since</label>
        <p><?php echo date('F j, Y', strtotime($user_info['created_at'])); ?></p>
      </div>
    </div>
    <div class="user-popup-footer">
      <!-- <a href="profile.php">View Profile</a> -->
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  <?php else: ?>
    <!-- User is not logged in, show login/register options -->
    <div class="login-message">
      <p>You are not logged in</p>
      <a href="login.php">Login</a> or <a href="register.php">Register</a>
    </div>
  <?php endif; ?>
</div>

<div class="cartTab">
  <h1>Shopping Cart</h1>
  <div class="listCart">
    <!-- Cart items will be dynamically added here -->
    <div class="empty-cart">Your cart is empty</div>
  </div>
  <div class="btn">
    <button class="close">CLOSE</button>
    <button class="checkOut">CHECK OUT</button>
  </div>
</div>

<!-- Include the cart script -->
<script src="cart.js"></script>
<script src="gallery.js"></script>


  <div class="main-content"></div>
  <div class="carousel-container">
        <div class="carousel" id="animated-carousel">
      <div class="carousel-image"><img src="photos/kitchen.PNG" alt="Living Room Interior"></div>
      <div class="carousel-image"><img src="photos/bedroom.png" alt="Home Office Interior"></div>
      <div class="carousel-image"><img src="photos/garden.png" alt="Bedroom Interior"></div>
      <div class="carousel-image"><img src="photos/diningroom.PNG" alt="Kitchen Interior"></div>
      <div class="carousel-image"><img src="photos/clothingroom.PNG" alt="Dining Room Interior"></div>
    </div>
        <div class="carousel-overlay">
      <h1 data-aos="fade-down">OUR GALLERY</h1>
      <div class="breadcrumb" data-aos="fade-up" data-aos-delay="200">
        <a href="home.php"><i class='bx bx-home'></i>Home</a> / Gallery
      </div>
    </div>
  </div>

  <section class="categories" data-aos="fade-up">
    <div class="section-title">
      <h3>Our Categories</h3>
      <div class="dropdown">
      <select class="view-all-btn" name="category" id="categorySelect" onchange="navigateToPage()">
          <option class="choose" value="">Choose a category</option>
          <option class="options" value="category.php?category=kitchen">Kitchen</option>
          <option class="options" value="category.php?category=livingroom">Living Room</option>
          <option class="options" value="category.php?category=bedroom">Bedroom</option>
          <option class="options" value="category.php?category=office">Office</option>
          <option class="options" value="category.php?category=diningroom">Dining Room</option>
          <option class="options" value="category.php?category=gameroom">Game Room</option>
          <option class="options" value="category.php?category=gym">Gym</option>
          <option class="options" value="category.php?category=prayerroom">Prayer Room</option>
          <option class="options" value="category.php?category=garden">Garden</option>
          <option class="options" value="category.php?category=workshop">Workshop</option>
          <option class="options" value="category.php?category=closet">Closet</option>
          <option class="options" value="category.php?category=laundryroom">Laundry Room</option>
          <option class="options" value="category.php?category=mudroom">Mudroom</option>
          <option class="options" value="category.php?category=guestroom">Guest Room</option>
          <option class="options" value="category.php?category=nursery">Nursery</option>
          <option class="options" value="category.php?category=bathroom">Bathroom</option>
        </select>
      </div>
    </div>
    
    <div class="category-grid" data-aos="fade-up" data-aos-delay="30">
      <div class="category-item" data-aos="zoom-in" data-aos-delay="50" onclick="window.location.href='category.php?category=kitchen'">
        <span><img src="image/Photoroom_20250415_183143[1].png" alt="kitchen" width="90" height="90" /></span>
        <p>Kitchen</p>
      </div>
      <div class="category-item" data-aos="zoom-in" data-aos-delay="70" onclick="window.location.href='category.php?category=livingroom'">
        <span><img src="image/Photoroom_20250415_183117[1].png" alt="Living room" width="90" height="90" /></span>
        <p>Living Room</p>
      </div>
      <div class="category-item" data-aos="zoom-in" data-aos-delay="90" onclick="window.location.href='category.php?category=bedroom'">
        <span><img src="image/Photoroom_20250415_183037[1].png" alt="bedroom" width="90" height="90" /></span>
        <p>Bedroom</p>
      </div>
      <div class="category-item" data-aos="zoom-in" data-aos-delay="110" onclick="window.location.href='category.php?category=office'">
        <span><img src="image/Photoroom_20250415_182952[1].png" alt="office" width="90" height="90" /></span>
        <p>Office</p>
      </div>
      <div class="category-item" data-aos="zoom-in" data-aos-delay="130" onclick="window.location.href='category.php?category=diningroom'">
        <span><img src="image/Photoroom_20250415_182806[1].png" alt="dining room" width="90" height="90" /></span>
        <p>Dining Room</p>
      </div>
    </div>
  </section>

  <section class="products" data-aos="fade-up" data-aos-delay="50">
    <div class="section-title">
      <h3>Our Products</h3>
      <div class="dropdown">
        <select class="view-all-btn" name="style" id="style" onchange="filterProducts()">
          <option class="choose" value="">Style</option>
          <option class="options" value="Modern">Modern</option>
          <option class="options" value="Traditional">Traditional</option>
          <option class="options" value="Rustic">Rustic</option>
          <option class="options" value="Minimalist">Minimalist</option>
          <option class="options" value="Industrial">Industrial</option>
        </select>
        <select class="view-all-btn" name="size" id="size" onchange="filterProducts()">
          <option class="choose" value="">Size</option>
          <?php foreach($sizes as $size): ?>
            <option class="options" value="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></option>
          <?php endforeach; ?>
          <?php if(empty($sizes)): ?>
            <option class="options" value="Small">Small</option>
            <option class="options" value="Medium">Medium</option>
            <option class="options" value="Large">Large</option>
          <?php endif; ?>
        </select>
        <select class="view-all-btn" name="priceRange" id="priceRange" onchange="filterProducts()">
          <option class="choose" value="">Price Range</option>
          <option value="Under $500">Under $500</option>
          <option value="$500 - $1000">$500 - $1000</option>
          <option value="$1000 - $2000">$1000 - $2000</option>
          <option value="$2000+">$2000+</option>
        </select>
        <select class="view-all-btn" name="sortOptions" id="sortOptions" onchange="filterProducts()">
          <option class="choose" value="">Sort By: Featured</option>
          <option value="priceLow">Price: Low to High</option>
          <option value="priceHigh">Price: High to Low</option>
          <option value="newest">Newest</option>
          <option value="popular">Most Popular</option>
        </select>
        <button id="clearFilters" class="view-all-btn" style="background-color: #80827b; display: none;" onclick="clearAllFilters()">
          Clear Filters
        </button>
      </div>
    </div>

    <div id="productGrid" class="product-grid" data-aos="fade-up" data-aos-delay="70">
      <?php 
      // Initialize counter for staggered animations
      $counter = 0;
      if(count($products) > 0): ?>
        <?php foreach($products as $product): 
          // Increment counter for each product
          $counter++;
        ?>
          <div class="product-card" data-aos="fade-up" data-aos-delay="<?php echo min(30 * $counter, 150); ?>" 
              data-style="<?php echo htmlspecialchars($product['style']); ?>" 
              data-price="<?php echo $product['price']; ?>" 
              data-date="<?php echo $product['date_added']; ?>" 
              data-id="<?php echo htmlspecialchars($product['product_id']); ?>"
              data-size="<?php echo isset($product['size']) ? htmlspecialchars($product['size']) : ''; ?>"
              data-name="<?php echo htmlspecialchars($product['name']); ?>"
              data-quantity="<?php echo $product['stock_quantity']; ?>"
              data-description="<?php echo htmlspecialchars($product['description']); ?>">
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
            <div class="gallery-description"><?php echo htmlspecialchars($product['description']); ?></div>
            <div class="gallery-meta">
              <div class="price">$<?php echo formatPrice($product['price']); ?></div>
              <div class="action-buttons">
                <button class="favorite-btn">♡</button>
                <button class="add-to-cart" 
                    data-product-id="<?php echo htmlspecialchars($product['product_id']); ?>"
                    data-id="<?php echo htmlspecialchars($product['product_id']); ?>" 
                    data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                    data-price="<?php echo $product['price']; ?>" 
                    data-image="<?php echo htmlspecialchars($product['image_path']); ?>">Add to cart</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div id="noResultsMsg" style="width: 100%; padding: 20px; text-align: center; color: #24424c;">
          <p>No products available. Check back soon!</p>
        </div>
      <?php endif; ?>
    </div>
  </section>


<script>
  // Function to navigate to category page
  function navigateToPage() {
    var select = document.getElementById("categorySelect");
    var selectedValue = select.value;
    if (selectedValue) {
      window.location.href = selectedValue;
    }
  }
  
  // Function to filter products based on selected criteria
  function filterProducts() {
    const style = document.getElementById('style').value;
    const size = document.getElementById('size').value;
    const priceRange = document.getElementById('priceRange').value;
    const sortOption = document.getElementById('sortOptions').value;
    const productCards = document.querySelectorAll('.product-card');
    
    let visibleCount = 0;
    
    productCards.forEach(card => {
      const cardStyle = card.getAttribute('data-style');
      const cardSize = card.getAttribute('data-size');
      const cardPrice = parseInt(card.getAttribute('data-price'));
      
      // Check if card matches all selected filters
      let styleMatch = style === '' || cardStyle === style;
      let sizeMatch = size === '' || cardSize === size;
      let priceMatch = true;
      
      // Price range logic
      if (priceRange !== '') {
        if (priceRange === 'Under $500' && cardPrice >= 500) {
          priceMatch = false;
        } else if (priceRange === '$500 - $1000' && (cardPrice < 500 || cardPrice > 1000)) {
          priceMatch = false;
        } else if (priceRange === '$1000 - $2000' && (cardPrice < 1000 || cardPrice > 2000)) {
          priceMatch = false;
        } else if (priceRange === '$2000+' && cardPrice < 2000) {
          priceMatch = false;
        }
      }
      
      // Show or hide based on filter match
      if (styleMatch && sizeMatch && priceMatch) {
        card.style.display = 'flex';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });
    
    // Apply sorting if items are visible
    if (visibleCount > 0 && sortOption !== '') {
      sortProducts(sortOption);
    }
    
    // Show message if no products match
    updateNoResultsMessage(visibleCount);
    
    // Show or hide the clear filters button
    toggleClearFiltersButton(style, size, priceRange, sortOption);
  }
  
  // Function for updating "no results" message
  function updateNoResultsMessage(visibleCount) {
    const productGrid = document.getElementById('productGrid');
    let noResultsMsg = document.getElementById('noResultsMsg');
    
    if (visibleCount === 0) {
      if (!noResultsMsg) {
        const msgElement = document.createElement('div');
        msgElement.id = 'noResultsMsg';
        msgElement.innerHTML = '<p>No products match your selected filters. Please try different criteria.</p>';
        msgElement.style.width = '100%';
        msgElement.style.padding = '20px';
        msgElement.style.textAlign = 'center';
        msgElement.style.color = '#24424c';
        productGrid.appendChild(msgElement);
      }
    } else if (noResultsMsg) {
      noResultsMsg.remove();
    }
  }
  
  // Function to sort products
  function sortProducts(sortOption) {
    const productGrid = document.getElementById('productGrid');
    const products = Array.from(document.querySelectorAll('.product-card:not([style*="display: none"])'));
    
    products.sort((a, b) => {
      if (sortOption === 'priceLow') {
        return parseInt(a.getAttribute('data-price')) - parseInt(b.getAttribute('data-price'));
      } 
      else if (sortOption === 'priceHigh') {
        return parseInt(b.getAttribute('data-price')) - parseInt(a.getAttribute('data-price'));
      }
      else if (sortOption === 'newest') {
        return new Date(b.getAttribute('data-date')) - new Date(a.getAttribute('data-date'));
      }
      else if (sortOption === 'popular') {
        // For now, using random ordering for popularity
        // In a real implementation, you would use actual popularity metrics
        return 0.5 - Math.random();
      }
      return 0;
    });
    
    // Re-append sorted products
    products.forEach(product => {
      productGrid.appendChild(product);
    });
  }
  
  // Cart functionality is handled in gallery.js
  // Initialize cart count on page load
  document.addEventListener('DOMContentLoaded', function() {
    // This will be handled by gallery.js
  });
  
  // Search functionality
  function searchProducts() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase().trim();
    if (searchInput === '') return; // Don't search if input is empty
    
    const productCards = document.querySelectorAll('.product-card');
    let foundCount = 0;
    
    productCards.forEach(card => {
      const name = card.getAttribute('data-name').toLowerCase();
      const description = card.getAttribute('data-description').toLowerCase();
      
      // Match against product name and description
      if (name.includes(searchInput) || description.includes(searchInput)) {
        card.style.display = 'flex';
        foundCount++;
      } else {
        card.style.display = 'none';
      }
    });
    
    // Reset filter dropdowns to show we're now searching
    document.getElementById('style').value = '';
    document.getElementById('size').value = '';
    document.getElementById('priceRange').value = '';
    document.getElementById('sortOptions').value = '';
    
    // Show message if no results found
    updateNoResultsMessage(foundCount);
    
    // Show clear filters button if search is active
    toggleClearFiltersButton('', '', '', '', searchInput !== '');
  }
  
  // Function to toggle the clear filters button
  function toggleClearFiltersButton(style, size, priceRange, sortOption, searchActive = false) {
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    if (style || size || priceRange || sortOption !== '' || searchActive) {
      clearFiltersBtn.style.display = 'block';
    } else {
      clearFiltersBtn.style.display = 'none';
    }
  }
  
  // Function to clear all filters
  function clearAllFilters() {
    document.getElementById('style').value = '';
    document.getElementById('size').value = '';
    document.getElementById('priceRange').value = '';
    document.getElementById('sortOptions').value = '';
    document.getElementById('searchInput').value = '';
    
    // Show all products
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
      card.style.display = 'flex';
    });
    
    // Remove any no results message
    const noResultsMsg = document.getElementById('noResultsMsg');
    if (noResultsMsg) {
      noResultsMsg.remove();
    }
    
    // Hide clear filters button
    document.getElementById('clearFilters').style.display = 'none';
  }
  
  // Initialize when DOM is loaded
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS animations
    AOS.refresh();
    
    // Set up cart animation
    const cartIcon = document.querySelector('.cart-container');
    if (cartIcon) {
      cartIcon.addEventListener('click', function() {
        const isOpening = !document.body.classList.contains('showCart');
        animateCart(isOpening);
        document.body.classList.toggle('showCart');
      });
    }
    
    // Set up close cart button with animation
    const closeBtn = document.querySelector('.close');
    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        animateCart(false);
        document.body.classList.remove('showCart');
      });
    }
    // Set up favorite buttons
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
    favoriteButtons.forEach(button => {
      button.addEventListener('click', function() {
        if (this.textContent === '❤') {
          this.textContent = '♡';
          this.style.color = '#24424c';
        } else {
          this.textContent = '❤';
          this.style.color = 'red';
        }
      });
    });
    
    // Initialize cart count from localStorage
    const cartCount = document.getElementById('cartCount');
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    let count = 0;
    
    cart.forEach(item => {
      count += item.quantity;
    });
    
    cartCount.textContent = count;
    
    // Hide clear filters button on page load
    document.getElementById('clearFilters').style.display = 'none';
    
    // Add search input enter key listener
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        searchProducts();
      }
    });
    
    // We've removed the automatic carousel animation to fix the always-moving images issue
    const carousel = document.getElementById('animated-carousel');
    if (carousel) {
      // Instead of continuous animation, we'll add a subtle hover effect
      carousel.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.02)';
      });
      carousel.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
      });
    }
    
    // Add hover animations to buttons
    const buttons = document.querySelectorAll('.ai-button, .view-all-btn, .add-to-cart, .favorite-btn');
    buttons.forEach(button => {
      button.classList.add('animate-on-hover');
    });
    
    // Setup search animation
    const searchBar = document.querySelector('.search-bar');
    if (searchBar) {
      searchBar.classList.add('search-animation');
    }
    
    // Update cart UI if cart.js is loaded
    if (typeof updateCartUI === 'function') {
      updateCartUI();
    }
  });

  // User profile popup functionality
  document.addEventListener('DOMContentLoaded', function() {
    const userProfileIcon = document.getElementById('userProfileIcon');
    const userPopup = document.getElementById('userPopup');
    
    // Toggle user popup when profile icon is clicked
    if (userProfileIcon) {
      userProfileIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        userPopup.classList.toggle('active');
      });
    }
    
    // Close popup when clicking outside
    document.addEventListener('click', function(e) {
      if (userPopup.classList.contains('active') && 
          !userPopup.contains(e.target) && 
          e.target !== userProfileIcon) {
        userPopup.classList.remove('active');
      }
    });
  });
  
  // Function to handle cart animations
  function animateCart(isOpening) {
    const cart = document.querySelector('.cartTab');
    
    if (isOpening) {
      // Create overlay for cart background if it doesn't exist
      if (!document.querySelector('.cart-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'cart-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
        overlay.style.zIndex = '999';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';
        document.body.appendChild(overlay);
        
        // Animate overlay in
        setTimeout(() => {
          overlay.style.opacity = '1';
        }, 10);
      }
      
      // Animate cart sliding in with a bounce effect
      cart.style.transition = 'right 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
    } else {
      // Find and remove overlay
      const overlay = document.querySelector('.cart-overlay');
      if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
          overlay.remove();
        }, 300);
      }
      
      // Animate cart sliding out
      cart.style.transition = 'right 0.3s cubic-bezier(0.6, -0.28, 0.735, 0.045)';
    }
  }
  
  // Function to animate product cards when they appear in viewport
  function setupProductAnimations() {
    // This is handled by AOS library
    // We're using data-aos attributes on the elements
  }
  </script>
  
  <!-- Include the product modal component -->
  <?php include 'product-modal.php'; ?>
  
  <!-- Include the footer component -->
  <?php include 'footer.php'; ?>
  
  <!-- Include the product modal JavaScript -->
  <script src="js/product-modal.js"></script>
  
  <!-- Initialize cart and wishlist functionality -->
  <script>
    // Initialize cart and wishlist functionality when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
      // Check if cartManager exists (from cart.js)
      if (window.cartManager && typeof window.cartManager.init === 'function') {
        // Initialize the cart
        window.cartManager.init();
        
        // Add click event to cart container if not already added
        const cartContainer = document.querySelector('.cart-container');
        if (cartContainer) {
          // Remove existing listeners to prevent duplicates
          const newCartContainer = cartContainer.cloneNode(true);
          cartContainer.parentNode.replaceChild(newCartContainer, cartContainer);
          
          // Add new click listener
          newCartContainer.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.add('showCart');
            
            // Call animateCart if it exists
            if (typeof animateCart === 'function') {
              animateCart(true);
            }
          });
        }
        
        // Add click event to close button if not already added
        const closeButton = document.querySelector('.cartTab .close');
        if (closeButton) {
          // Remove existing listeners to prevent duplicates
          const newCloseButton = closeButton.cloneNode(true);
          closeButton.parentNode.replaceChild(newCloseButton, closeButton);
          
          // Add new click listener
          newCloseButton.addEventListener('click', function() {
            document.body.classList.remove('showCart');
            
            // Call animateCart if it exists
            if (typeof animateCart === 'function') {
              animateCart(false);
            }
          });
        }
      }
      
      // Update wishlist count badge
      // Get wishlist count element
      const wishlistCount = document.getElementById('wishlistCount');
      if (wishlistCount) {
        // Get wishlist items from localStorage
        const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
        // Update the count
        wishlistCount.textContent = wishlist.length;
      }
    });
  </script>
</body>
</html>