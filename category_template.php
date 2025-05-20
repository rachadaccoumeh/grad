<?php
/**
 * Category Template - RoomGenius
 * 
 * This file provides functions to display products by category
 */

/**
 * Get products for a specific category
 * 
 * @param string $category_name Category name (e.g. "Kitchen", "Living Room")
 * @return array Array of products from the database
 */
function displayCategoryProducts($category_name) {
  // Database connection
  $servername = "localhost";
  $username = "root"; 
  $password = "root123"; 
  $dbname = "roomgenius_db";

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Get products from database for this specific category
  $query = "SELECT * FROM products WHERE category = ? ORDER BY date_added DESC";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $category_name);
  $stmt->execute();
  $result = $stmt->get_result();

  // Initialize an array to store products
  $products = [];
  if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $products[] = $row;
    }
  }

  $stmt->close();
  $conn->close();
  
  return $products;
}

/**
 * Format price with appropriate number formatting
 * 
 * @param float $price Product price
 * @return string Formatted price
 */
function formatPrice($price) {
  return number_format($price, 0);
}

/**
 * Display category page content
 * 
 * @param string $category_name Category name to display
 * @param string $title Page title
 * @param string $description Category description
 */
function displayCategoryPage($category_name, $title, $description) {
  $products = displayCategoryProducts($category_name);
  
  // Include header if it exists
  $header_file = 'header.php';
  $use_header = file_exists($header_file);
  
  if ($use_header) {
    include($header_file);
  } else {
    // Basic header structure if header.php doesn't exist
    echo '
    <header>
      <div class="logo"><i class="fas fa-brain"></i><i class="fas fa-couch"></i> RoomGenius</div>
      <div class="search-bar">
        <input type="text" placeholder="Search here..." id="searchInput" />
        <button onclick="searchProducts()"><i class="fas fa-search"></i></button>
      </div>
      <div class="right-section">
        <div class="icons">
          <span class="cart-container">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count" id="cartCount">0</span>
          </span>
          <span class="user-container">
            <i class="fas fa-user"></i>
          </span>
          <span class="customize-container">
            <a href="customize.php" title="Customize">
              <i class="fas fa-cog"></i>
            </a>
          </span>
        </div>
        <div class="ai-button-container">
          <button class="ai-button" onclick="window.location.href=\'ai-page.php\'">
            <i class="fas fa-robot"></i> Ai room genie
          </button>
        </div>
      </div>
    </header>';
  }
  
  // Main category content
  ?>
  
  <div class="main-content">
    <div class="category-banner">
      <h1><?php echo htmlspecialchars($title); ?></h1>
      <div class="breadcrumb">
        <a href="home.php"><i class='bx bx-home'></i> Home</a> / 
        <a href="gallery.php">Gallery</a> / 
        <?php echo htmlspecialchars($category_name); ?>
      </div>
      <p class="category-description"><?php echo htmlspecialchars($description); ?></p>
    </div>

    <section class="products">
      <div class="section-title">
        <h3><?php echo htmlspecialchars($category_name); ?> Products</h3>
        <div class="dropdown">
          <select class="view-all-btn" name="style" id="style" onchange="filterProducts()">
            <option class="choose" value="">Style</option>
            <option class="options" value="Modern">Modern</option>
            <option class="options" value="Traditional">Traditional</option>
            <option class="options" value="Rustic">Rustic</option>
            <option class="options" value="Minimalist">Minimalist</option>
            <option class="options" value="Industrial">Industrial</option>
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
            <p>No <?php echo strtolower(htmlspecialchars($category_name)); ?> products available. Check back soon!</p>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <?php
  // Include footer if it exists
  $footer_file = 'footer.php';
  if (file_exists($footer_file)) {
    include($footer_file);
  }
  
  // Include standard JavaScript
  ?>
  <script>
  // Function to filter products based on selected criteria
  function filterProducts() {
    const style = document.getElementById('style').value;
    const priceRange = document.getElementById('priceRange').value;
    const sortOption = document.getElementById('sortOptions').value;
    const productCards = document.querySelectorAll('.product-card');
    
    let visibleCount = 0;
    
    productCards.forEach(card => {
      const cardStyle = card.getAttribute('data-style');
      const cardPrice = parseInt(card.getAttribute('data-price'));
      
      // Check if card matches all selected filters
      let styleMatch = style === '' || cardStyle === style;
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
      if (styleMatch && priceMatch) {
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
    toggleClearFiltersButton(style, priceRange, sortOption);
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
        return 0.5 - Math.random();
      }
      return 0;
    });
    
    // Re-append sorted products
    products.forEach(product => {
      productGrid.appendChild(product);
    });
  }
  
  // Function to toggle the clear filters button
  function toggleClearFiltersButton(style, priceRange, sortOption) {
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    if (style || priceRange || sortOption !== '') {
      clearFiltersBtn.style.display = 'block';
    } else {
      clearFiltersBtn.style.display = 'none';
    }
  }
  
  // Function to clear all filters
  function clearAllFilters() {
    document.getElementById('style').value = '';
    document.getElementById('priceRange').value = '';
    document.getElementById('sortOptions').value = '';
    
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
  });
  
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
    notification.textContent = ${productName} added to cart!;
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
  </script>
  <?php
}
?>
