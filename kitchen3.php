<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RoomGenuis Gallery</title>
  <link rel="stylesheet" href="Kitchen3.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    /* Shopping cart styles fixes */
.cartTab {
    width: 400px;
    background-color: #24424c; /* Changed to match your site's color scheme */
    color: #fff8e3;
    position: fixed;
    top: 0;
    right: -400px; /* Start offscreen */
    bottom: 0;
    display: grid;
    grid-template-rows: 70px 1fr 70px;
    transition: right 0.5s ease;
    z-index: 1000;
    box-shadow: -5px 0 15px rgba(0, 0, 0, 0.2);
  }

  body.showCart .cartTab {
    right: 0;
  }
  
  .cartTab h1 {
    padding: 20px;
    margin: 0;
    font-weight: 500;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  }
  .cartTab .btn {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
  }
  .cartTab .btn button {
    background-color: #906e2b;
    border: none;
    font-weight: 500;
    cursor: pointer;
    color: #fff8e3;
    padding: 15px 0;
    transition: background-color 0.3s;
  }
  .cartTab .btn button:hover {
    background-color: #7b5c23;
  }
  .cartTab .btn .close {
    background-color: #c9b99e;
    color: #24424c;
  }
  .cartTab .btn .close:hover {
    background-color: #baa88d;
  }
  .cartTab .listCart {
    overflow-y: auto;
    padding: 10px;
  }
  .cartTab .listCart .item .image {
    width: 100%;
    height: 60px;
    overflow: hidden;
    border-radius: 5px;
  }
  .cartTab .listCart .item .image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .cartTab .listCart .item .name {
    text-align: left;
    font-size: 14px;
  }
  
  .cartTab .listCart .item .totalPrice {
    font-weight: bold;
  }
  .cartTab .listCart .item {
    display: grid;
    grid-template-columns: 70px 1fr 80px 70px;
    gap: 10px;
    text-align: center;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1)
  }
  .listCart .quantity {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .listCart .quantity span {
    display: inline-block;
    width: 25px;
    height: 25px;
    background-color: #fff8e3;
    color: #24424c;
    border-radius: 50%;
    cursor: pointer;
    line-height: 25px;
    text-align: center;
    font-weight: bold;
    user-select: none;
  }
  
  .listCart .quantity span:nth-child(2) {
    background-color: transparent;
    color: #fff8e3;
    cursor: default;
  }
  .listCart .quantity span:hover:not(:nth-child(2)) {
    background-color: #e9d8c3;
  }
  /* Empty cart message */
  .empty-cart {
    text-align: center;
    padding: 30px 0;
    color: rgba(255, 248, 227, 0.7);
  }
  /* Fix for cart container pointer */
  .cart-container {
    cursor: pointer;
  }
  .main-content {
    position: relative;
    /* Ensure content doesn't shift when cart opens */
    transition: none;
  }
  /* Fix for any potential inherited transforms */
  body.showCart .main-content,
  body.showCart .carousel,
  body.showCart .carousel-container,
  body.showCart .categories,
  body.showCart .products {
    /* Prevent any accidental transforms */
    transform: none;
  }
  </style>
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
      <div class="product-card" data-style="Modern" data-price="300" data-size="Small" data-date="2025-02-10" data-id="K14">
        <div class="gallery-image">
          <img src="kitchen3product/IMG_4774.jpg" alt="Wooden Shelf">
        </div>
        <div class="gallery-info">
          <div class="gallery-title"><h4>Wooden Shelf</h4></div>
          <div class="gallery-description">Elegant wooden shelf with minimalist design, perfect for displaying kitchen items and storing essentials.</div>
          <div class="gallery-meta">
            <div class="price">$300</div>
            <div class="action-buttons">
              <button class="favorite-btn">❤</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Product 2 -->
      <div class="product-card" data-style="Minimalist" data-price="600" data-size="Medium" data-date="2025-03-15" data-id="K15">
        <div class="gallery-image">
          <img src="kitchen3product/IMG_4773.jpg" alt="Modern Shelf">
        </div>
        <div class="gallery-info">
          <div class="gallery-title"><h4>Modern Shelf</h4></div>
          <div class="gallery-description">Contemporary shelf design with clean lines and durable construction, adding functionality to any kitchen space.</div>
          <div class="gallery-meta">
            <div class="price">$600</div>
            <div class="action-buttons">
              <button class="favorite-btn">❤</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Product 3 -->
      <div class="product-card" data-style="Traditional" data-price="350" data-size="Small" data-date="2025-01-20" data-id="K16">
        <div class="gallery-image">
          <img src="kitchen3product/IMG_4772.JPG" alt="Shelf for Cups">
        </div>
        <div class="gallery-info">
          <div class="gallery-title"><h4>Shelf for Cups</h4></div>
          <div class="gallery-description">Specialized shelf designed for displaying and organizing cups and mugs with traditional styling elements.</div>
          <div class="gallery-meta">
            <div class="price">$350</div>
            <div class="action-buttons">
              <button class="favorite-btn">❤</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Product 4 -->
      <div class="product-card" data-style="Rustic" data-price="250" data-size="Small" data-date="2025-02-28" data-id="K17">
        <div class="gallery-image">
          <img src="kitchen3product/IMG_4776.jpg" alt="Wooden Storage">
        </div>
        <div class="gallery-info">
          <div class="gallery-title"><h4>Wooden Storage</h4></div>
          <div class="gallery-description">Rustic wooden storage solution with practical compartments for kitchen essentials and decorative items.</div>
          <div class="gallery-meta">
            <div class="price">$250</div>
            <div class="action-buttons">
              <button class="favorite-btn">❤</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="pagination">
      <button onclick="window.location.href='kitchen2.php'">«</button>
      <button onclick="window.location.href='kitchen.php'">1</button>
      <button onclick="window.location.href='kitchen2.php'">2</button>
      <button class="active" onclick="window.location.href='kitchen3.php'">3</button>
      <button onclick="window.location.href='kitchen.php'">»</button>
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