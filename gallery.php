<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "roomgenius_db"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get products from database
$query = "SELECT * FROM products ORDER BY date_added DESC";
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
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="showCart">
  <!-- Header HTML -->
   <div>
  <header>
  <div class="logo"><i class="fas fa-brain"></i>
    <i class="fas fa-couch"></i>
    RoomGenius
  </div>

  <div class="search-bar">
    <input type="text" placeholder="Search here..." id="searchInput" />
    <button onclick="searchProducts()"><i class="fas fa-search"></i></button>
  </div>
  <div class="right-section">
    <div class="icons">
      <!-- Separate cart and user into individual containers -->
      <span class="cart-container">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count" id="cartCount">0</span>
      </span>
      <span class="user-container">
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
      <button class="ai-button" onclick="window.location.href='ai-page.html'">
        <i class="fas fa-robot"></i> Ai room genie
      </button>
    </div>
  </div>
</header>
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
    <div class="carousel">
      <div class="carousel-image"><img src="photos/kitchen.PNG" alt="Living Room Interior"></div>
      <div class="carousel-image"><img src="photos/bedroom.png" alt="Home Office Interior"></div>
      <div class="carousel-image"><img src="photos/garden.png" alt="Bedroom Interior"></div>
      <div class="carousel-image"><img src="photos/diningroom.PNG" alt="Kitchen Interior"></div>
      <div class="carousel-image"><img src="photos/clothingroom.PNG" alt="Dining Room Interior"></div>
    </div>
    <div class="carousel-overlay">
      <h1>OUR GALLERY</h1>
      <div class="breadcrumb">
        <a href="home.php"><i class='bx bx-home'></i>Home</a> / Gallery
      </div>
    </div>
  </div>

  <section class="categories">
    <div class="section-title">
      <h3>Our Categories</h3>
      <div class="dropdown">
      <select class="view-all-btn" name="category" id="categorySelect" onchange="navigateToPage()">
          <option class="choose" value="">Choose a category</option>
          <option class="options" value="Gameroom.php">Game Room</option>
          <option class="options" value="Gym.php">Gym</option>
          <option class="options" value="prayerroom.php">Prayer Room</option>
          <option class="options" value="garden.php">garden</option>
          <option class="options" value="workshop.php">Workshop</option>
          <option class="options" value="Closet.php">Closet</option>
          <option class="options" value="Laundryroom.php">Loundry room</option>
          <option class="options" value="Mudroom.php">Mudroom</option>
          <option class="options" value="guest room.php">guest room</option>
          <option class="options" value="Nursery.php">Nursery</option>
          <option class="options" value="bath room.php">Bathroom</option>
        </select>
      </div>
    </div>
    
    <div class="category-grid">
      <div class="category-item" onclick="window.location.href='kitchen.php'">
        <span><img src="image/Photoroom_20250415_183143[1].png" alt="kitchen" width="90" height="90" /></span>
        <p>Kitchen</p>
      </div>
      <div class="category-item" onclick="window.location.href='livingroom.php'">
        <span><img src="image/Photoroom_20250415_183117[1].png" alt="Living room" width="90" height="90" /></span>
        <p>Living Room</p>
      </div>
      <div class="category-item" onclick="window.location.href='bedroom.php'">
        <span><img src="image/Photoroom_20250415_183037[1].png" alt="bedroom" width="90" height="90" /></span>
        <p>Bedroom</p>
      </div>
      <div class="category-item" onclick="window.location.href='office.php'">
        <span><img src="image/Photoroom_20250415_182952[1].png" alt="office" width="90" height="90" /></span>
        <p>Office</p>
      </div>
      <div class="category-item" onclick="window.location.href='diningroom.php'">
        <span><img src="image/Photoroom_20250415_182806[1].png" alt="dining room" width="90" height="90" /></span>
        <p>Dining Room</p>
      </div>
    </div>
  </section>

  <section class="products">
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

    <div class="product-grid" id="productGrid">
      <?php if(count($products) > 0): ?>
        <?php foreach($products as $product): ?>
          <div class="product-card" 
              data-style="<?php echo htmlspecialchars($product['style']); ?>" 
              data-price="<?php echo $product['price']; ?>" 
              data-date="<?php echo $product['date_added']; ?>" 
              data-id="<?php echo htmlspecialchars($product['product_id']); ?>"
              data-size="<?php echo isset($product['size']) ? htmlspecialchars($product['size']) : ''; ?>"
              data-name="<?php echo htmlspecialchars($product['name']); ?>"
              data-description="<?php echo htmlspecialchars($product['description']); ?>">
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
            <div class="gallery-description"><?php echo htmlspecialchars($product['description']); ?></div>
            <div class="gallery-meta">
              <div class="price">$<?php echo formatPrice($product['price']); ?></div>
              <div class="action-buttons">
                <button class="favorite-btn">♡</button>
                <button class="add-to-cart" onclick="addToCart(this)" 
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
  
  // Add to cart functionality
  function addToCart(button) {
    const cartCount = document.getElementById('cartCount');
    let currentCount = parseInt(cartCount.textContent);
    cartCount.textContent = currentCount + 1;
    
    // Visual feedback for adding to cart
    button.textContent = "Added!";
    setTimeout(() => {
      button.textContent = "Add to cart";
    }, 1500);
    
    // Get product data from attributes
    const productId = button.getAttribute('data-id');
    const productName = button.getAttribute('data-name');
    const productPrice = button.getAttribute('data-price');
    const productImage = button.getAttribute('data-image');
    
    // Create cart item object
    const cartItem = {
      id: productId,
      name: productName,
      price: productPrice,
      image: productImage,
      quantity: 1
    };
    
    // Add to cart in localStorage
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Check if item already exists in cart
    const existingItemIndex = cart.findIndex(item => item.id === productId);
    
    if (existingItemIndex > -1) {
      // Update quantity if item exists
      cart[existingItemIndex].quantity += 1;
    } else {
      // Add new item to cart
      cart.push(cartItem);
    }
    
    // Save cart to localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Update cart UI if necessary
    if (typeof updateCartUI === 'function') {
      updateCartUI();
    }
    
    // Show notification
    showAddToCartNotification(productName);
  }
  
  // Function to display add to cart notification
  function showAddToCartNotification(productName) {
    const notification = document.createElement('div');
    notification.textContent = `${productName} added to cart!`; // Fixed syntax error here
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.backgroundColor = '#24424c';
    notification.style.color = '#fff8e3';
    notification.style.padding = '10px 20px';
    notification.style.borderRadius = '5px';
    notification.style.zIndex = '1000';
    notification.style.transition = 'opacity 0.5s';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.style.opacity = '0';
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 500);
    }, 2000);
  }
  
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
    
    // Set up cart button click handler
    const cartIcon = document.querySelector('.cart-container');
    if (cartIcon) {
      cartIcon.addEventListener('click', function() {
        document.body.classList.toggle('showCart');
      });
    }
    
    // Set up close cart button
    const closeBtn = document.querySelector('.close');
    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        document.body.classList.remove('showCart');
      });
    }
    
    // Update cart UI if cart.js is loaded
    if (typeof updateCartUI === 'function') {
      updateCartUI();
    }
  });

  </script>
</body>
</html>