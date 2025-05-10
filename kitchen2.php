<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RoomGenuis Gallery</title>
  <link rel="stylesheet" href="Kitchen2.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="gallery.js"></script>
</head>

<body class="showCart">
  <!-- Replace your existing header HTML with this -->
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
    </div>
    <div class="ai-button-container">
      <button class="ai-button" onclick="window.location.href='ai-page.html'">
        <i class="fas fa-robot"></i> Ai room genie
      </button>
    </div>
  </div>
</header>


<!-- Cart HTML Structure -->
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
<script src="wishlist.js"></script>

  <div class="carousel-container">
    <div class="carousel">
      <div class="carousel-image"><img src="kitchenphotos/IMG_4728.jpg" alt="Kitchen Interior"></div>
      <div class="carousel-image"><img src="kitchenphotos/IMG_4729.JPG" alt="Kitchen Interior"></div>
      <div class="carousel-image"><img src="kitchenphotos/IMG_4730.jpg" alt="Kitchen Interior"></div>
      <div class="carousel-image"><img src="kitchenphotos/IMG_4731.jpg" alt="Kitchen Interior"></div>
      <div class="carousel-image"><img src="kitchenphotos/IMG_4732.jpg" alt="Kitchen Interior"></div>
      <div class="carousel-image"><img src="kitchenphotos/IMG_4737.jpg" alt="Kitchen Interior"></div>
    </div>
    <div class="carousel-overlay">
      <h1>Kitchen Category</h1>
      <div class="breadcrumb">
        <a href="home.php"><i class='bx bx-home'></i>Home</a><a href="gallery.php">/ Gallery </a> / Kitchen
      </div>
      <h4 class="carousel-subtext">Explore our collection of stunning kitchen designs that combine functionality with aesthetic appeal.</h4>
    </div>
  </div>

  <section class="categories">
    <div class="section-title">
      <h2>kitchen products</h2>
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
          <option value="Under $5,000">Under $5,000</option>
          <option value="$5,000 - $10,000">$5,000 - $10,000</option>
          <option value="$10,000 - $20,000">$10,000 - $20,000</option>
          <option value="$20,000+">$20,000+</option>
        </select>
        <select class="view-all-btn" name="size" id="size" onchange="filterProducts()">
          <option class="choose" value="">Size</option>
          <option value="Small">Small</option>
          <option value="Medium">Medium</option>
          <option value="Large">Large</option>
        </select>
        <select class="view-all-btn" name="sortOptions" id="sortOptions" onchange="filterProducts()">
          <option class="choose" value="">Sort By: Featured</option>
          <option value="priceLow">Price: Low to High</option>
          <option value="priceHigh">Price: High to Low</option>
          <option value="newest">Newest</option>
          <option value="popular">Most Popular</option>
        </select>
        <button id="clearFilters" class="view-all-btn" style="background-color: #800020;" onclick="clearAllFilters()">
          back
        </button>
      </div>
    </div>
  </section>

  <section class="products">
    <div class="product-grid" id="productGrid">
      <!-- Product 1 -->
      <div class="product-card" data-style="Modern" data-price="12500" data-size="Medium" data-date="2025-01-15" data-id="K8">
        <div class="gallery-image">
          <img src="productkitchen/IMG_4755.jpg" alt="Modern orange and green Kitchen">
        </div>
        <div class="gallery-info">
          <div class="gallery-title"><h4>Orange and green kitchen</h4></div>
          <div class="gallery-description">Clean lines and minimalist design with high-end appliances and ample storage space.</div>
          <div class="gallery-meta">
            <div class="price">$12,500</div>
            <div class="action-buttons">
              <button class="favorite-btn">❤</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Product 2 -->
      <div class="product-card" data-style="Traditional" data-price="8500" data-size="Small" data-date="2024-12-10" data-id="K9">
        <div class="gallery-image">
          <img src="productkitchen/IMG_4764.jpg" alt="Orange kitchen">
        </div>
        <div class="gallery-info">
          <div class="gallery-title"><h4>Orange kitchen</h4></div>
          <div class="gallery-description">Warm traditional design with classic elements and comfortable layout.</div>
          <div class="gallery-meta">
            <div class="price">$8,500</div>
            <div class="action-buttons">
              <button class="favorite-btn">❤</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Product 3 -->
      <div class="product-card" data-style="Minimalist" data-price="15000" data-size="Medium" data-date="2025-03-22" data-id="K10">
        <div class="gallery-image">
          <img src="productkitchen/IMG_4765.jpg" alt="Green Kitchen">
        </div>
        <div class="gallery-info">
          <div class="gallery-title"><h4>Green Kitchen</h4></div>
          <div class="gallery-description">Sleek minimalist aesthetic with eco-friendly materials and smart storage solutions.</div>
          <div class="gallery-meta">
            <div class="price">$15,000</div>
            <div class="action-buttons">
              <button class="favorite-btn">❤</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Product 4 -->
      <div class="product-card" data-style="Modern" data-price="18500" data-size="Large" data-date="2025-02-05" data-id="K11">
        <div class="gallery-image">
          <img src="productkitchen/IMG_4766.jpg" alt="Arabic modern blue kitchen">
        </div>
        <div class="gallery-info">
          <div class="gallery-title"><h4>Arabic modern blue kitchen</h4></div>
          <div class="gallery-description">Fusion of traditional Arabic elements with modern design, featuring blue accents.</div>
          <div class="gallery-meta">
            <div class="price">$18,500</div>
            <div class="action-buttons">
              <button class="favorite-btn">❤</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Product 5 -->
      <div class="product-card" data-style="Rustic" data-price="9800" data-size="Medium" data-date="2025-01-30" data-id="K12">
        <div class="gallery-image">
          <img src="productkitchen/IMG_4767.jpg" alt="Pink and green kitchen">
        </div>
        <div class="gallery-info">
          <div class="gallery-title"><h4>Pink and green kitchen</h4></div>
          <div class="gallery-description">Charming rustic design with natural wood elements and vintage-inspired fixtures.</div>
          <div class="gallery-meta">
            <div class="price">$9,800</div>
            <div class="action-buttons">
              <button class="favorite-btn">❤</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Product 6 -->
      <div class="product-card" data-style="Industrial" data-price="14500" data-size="Large" data-date="2025-03-10" data-id="K13">
        <div class="gallery-image">
          <img src="productkitchen/IMG_4771.jpg" alt="Blue kitchen">
        </div>
        <div class="gallery-info">
          <div class="gallery-title"><h4>Blue kitchen</h4></div>
          <div class="gallery-description">Bold industrial design with metal finishes, exposed elements, and contemporary blue color scheme.</div>
          <div class="gallery-meta">
            <div class="price">$14,500</div>
            <div class="action-buttons">
              <button class="favorite-btn">❤</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="pagination">
      <button onclick="window.location.href='kitchen.php'">«</button>
      <button onclick="window.location.href='kitchen.php'">1</button>
      <button class="active" onclick="window.location.href='kitchen2.php'">2</button>
      <button onclick="window.location.href='kitchen3.php'">3</button>
      <button onclick="window.location.href='kitchen3.php'">»</button>
    </div>
  </section>

  <script>
    // Function to filter products based on selected criteria
    function filterProducts() {
      const style = document.getElementById('style').value;
      const priceRange = document.getElementById('priceRange').value;
      const size = document.getElementById('size').value;
      const sortOption = document.getElementById('sortOptions').value;
      const productCards = document.querySelectorAll('.product-card');
      
      let visibleCount = 0;
      
      productCards.forEach(card => {
        const cardStyle = card.getAttribute('data-style');
        const cardPrice = parseInt(card.getAttribute('data-price'));
        const cardSize = card.getAttribute('data-size');
        
        // Check if card matches all selected filters
        let styleMatch = style === '' || cardStyle === style;
        let sizeMatch = size === '' || cardSize === size;
        let priceMatch = true;
        
        // Price range logic
        if (priceRange !== '') {
          if (priceRange === 'Under $5,000' && cardPrice >= 5000) {
            priceMatch = false;
          } else if (priceRange === '$5,000 - $10,000' && (cardPrice < 5000 || cardPrice > 10000)) {
            priceMatch = false;
          } else if (priceRange === '$10,000 - $20,000' && (cardPrice < 10000 || cardPrice > 20000)) {
            priceMatch = false;
          } else if (priceRange === '$20,000+' && cardPrice < 20000) {
            priceMatch = false;
          }
        }
        
        // Show or hide based on filter match
        if (styleMatch && priceMatch && sizeMatch) {
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
      const productGrid = document.getElementById('productGrid');
      const noResultsMsg = document.getElementById('noResultsMsg');
      
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
      
      // Show or hide the clear filters button
      toggleClearFiltersButton(style, priceRange, size, sortOption);
    }
    
    // Function to sort products
    function sortProducts(sortOption) {
      const productGrid = document.getElementById('productGrid');
      const products = Array.from(document.querySelectorAll('.product-card'));
      
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
          // You can implement popularity logic here
          // For now, let's use a simple random sort
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
      
      // Optional: show success message
      const product = button.closest('.product-card');
      const title = product.querySelector('.gallery-title h4').textContent;
      
      // Using a subtle notification instead of an alert
      const notification = document.createElement('div');
      notification.textContent = `${title} added to cart!`;
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
      const searchInput = document.getElementById('searchInput').value.toLowerCase();
      const productCards = document.querySelectorAll('.product-card');
      let foundResults = false;
      
      productCards.forEach(card => {
        const title = card.querySelector('.gallery-title h4').textContent.toLowerCase();
        const description = card.querySelector('.gallery-description').textContent.toLowerCase();
        
        if (title.includes(searchInput) || description.includes(searchInput)) {
          card.style.display = 'flex';
          foundResults = true;
        } else {
          card.style.display = 'none';
        }
      });
      
      // Reset filters to match search results
      document.getElementById('style').value = '';
      document.getElementById('priceRange').value = '';
      document.getElementById('size').value = '';
      document.getElementById('sortOptions').value = '';
      
      // Show message if no results found
      const productGrid = document.getElementById('productGrid');
      const noResultsMsg = document.getElementById('noResultsMsg');
      
      if (!foundResults) {
        if (!noResultsMsg) {
          const msgElement = document.createElement('div');
          msgElement.id = 'noResultsMsg';
          msgElement.innerHTML = '<p>No products match your search. Please try different keywords.</p>';
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
    
    // Function to toggle the clear filters button
    function toggleClearFiltersButton(style, priceRange, size, sortOption) {
      const clearFiltersBtn = document.getElementById('clearFilters');
      
      if (style || priceRange || size || sortOption) {
        clearFiltersBtn.style.display = 'block';
      } else {
        clearFiltersBtn.style.display = 'none';
      }
    }
    
    // Function to clear all filters
    function clearAllFilters() {
      document.getElementById('style').value = '';
      document.getElementById('priceRange').value = '';
      document.getElementById('size').value = '';
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
    
    // Favorite button functionality
    document.addEventListener('DOMContentLoaded', function() {
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
      
      // Initialize - hide clear filters button on page load
      document.getElementById('clearFilters').style.display = 'none';
    });
  </script>
</body>

</html>