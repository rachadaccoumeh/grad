<?php
session_start();
require_once('category_template.php');

// Define category specific variables
$category_name = "Prayer Room";
$page_title = "Prayer Room Collection";
$category_description = "Design a peaceful and reverent prayer space with our carefully selected furniture, storage options, and spiritual decor elements.";

// Display the kitchen category page
displayCategoryPage($category_name, $page_title, $category_description);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RoomGenius Prayer Room Gallery</title>
  <script src="gallery.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #c9b99e;
    }

    /* Header Styles */
    header {
      background-color: #c9b99e;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-weight: bold;
      font-size: 24px;
      color: #24424c;
      filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
    }

    .icons {
  display: flex;
  align-items: center;
  gap: 15px;
  

}

    .search-bar {
      display: flex;
      align-items: center;
    }

    .search-bar input {
      padding: 8px 12px;
      border: 1px solid #24424c;
      border-radius: 20px 0 0 20px;
      outline: none;
    }

    .search-bar button {
      padding: 8px 12px;
      border: 1px solid #24424c;
      background-color: #24424c;
      border-radius: 0 20px 20px 0;
      cursor: pointer;
    }

    .cart-container, .user-container {
  position: relative;
  display: inline-block;
  cursor: pointer;
}
.cart-container i, .user-container i {
  font-size: 18px;
  color: #24424c;
}

.cart-count {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #24424c;
  color: white;
  font-size: 12px;
  font-weight: bold;
  height: 18px;
  width: 18px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
}

    /* Carousel Styles */
    .carousel-container {
      position: relative;
      height: 200px;
      overflow: hidden;
      z-index: 1;
    }
    body.showCart .carousel-container {
  /* Keep carousel in place */
  transform: none;
}

    .carousel {
      display: flex;
      width: 100%;
      height: 100%;
    }

    .carousel-image {
      flex: 1;
      overflow: hidden;
    }

    .carousel-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .carousel-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.3);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: white;
    }

    .carousel-overlay h1 {
      font-size: 36px;
      margin-bottom: 10px;
    }

    .breadcrumb {
      margin-top: 10px;
      font-size: 14px;
      margin-bottom: 10px;
    }

    .breadcrumb a {
      color: white;
      text-decoration: none;
      margin-bottom: 10px;
    }

    .carousel-subtext {
      max-width: 80%;
      text-align: center;
      margin-top: 10px;
    }

    .categories, .products {
      padding: 30px;
      color: #24424c;
    }

    .section-title {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .dropdown {
      display: flex;
      gap: 10px;
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
      gap: 20px;
      justify-content: center;
    }

    .product-card {
      background-color: #fff8e3;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
      padding: 15px;
      text-align: center;
      width: 100%;
      transition: transform 0.2s, box-shadow 0.2s;
      display: flex;
      flex-direction: column;
    }

    .gallery-image {
      width: 100%;
      height: 150px;
      overflow: hidden;
      border-radius: 5px;
      margin-bottom: 10px;
    }

    .gallery-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .gallery-title h4 {
      font-size: 13px;
      margin-bottom: 5px;
      color: #24424c;
    }

    .gallery-description {
      font-size: 12px;
      line-height: 1.3;
      margin-bottom: 10px;
      color: #666;
      text-align: left;
    }

    .gallery-meta {
      display: block;
      justify-content: space-between;
      align-items: center;
      padding-top: 10px;
      border-top: 1px solid #ccc;
    }

    .price {
      font-weight: bold;
      color: #24424c;
      font-size: 14px;
      margin-bottom: 8px;
    }

    .action-buttons {
      display: flex;
      gap: 6px;
      align-items: center;
      justify-content: center;
    }

    .favorite-btn {
      background-color: #f8f8f8;
      color: #24424c;
      font-size: 16px;
      padding: 5px 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      cursor: pointer;
    }

    .add-to-cart {
      background-color: #24424c;
      color: white;
      font-size: 13px;
      padding: 5px 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .product-card:active {
      transform: scale(0.95);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .view-all-btn {
      background-color: #24424c;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      color: #fff8e3;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .view-all-btn:hover {
      color: #24424c;
      background-color: #fff8e3;
      border: 1px solid #24424c;
    }

    .right-section {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .pagination {
      display: flex;
      justify-content: center;
      padding: 40px 0;
      gap: 10px;
    }

    .pagination button {
      padding: 10px 15px;
      border: 1px solid #ddd;
      background-color: white;
      cursor: pointer;
      border-radius: 5px;
    }

    .pagination button.active {
      background-color: #24424c;
      color: white;
      border-color: #4a90e2;
    }

    .pagination button:hover:not(.active) {
      background-color: #f5f5f5;
    }

    .ai-button {
      background-color: #24424c;
      color: #fff8e3;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: bold;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background-color 0.3s ease;
      font-size: 14px;
    }

    .ai-button i {
      font-size: 16px;
    }

    .ai-button:hover {
      background-color: #fff8e3;
      color: #24424c;
      border: 1px solid #24424c;
      transform: scale(1.05);
    }

    .ai-button:active {
      transform: scale(0.95);
    }

    .choose {
      color: #888;
    }

    .options {
      background-color: #fff8e3;
      color: #2a7a88;
    }

    .notification {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: #24424c;
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      z-index: 1000;
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.3s, transform 0.3s;
    }

    .notification.show {
      opacity: 1;
      transform: translateY(0);
    }

    #noResultsMsg {
      grid-column: 1 / -1;
      text-align: center;
      padding: 30px;
      color: #24424c;
      font-weight: bold;
    }

    
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
.cartTab {
  right: -400px; /* Hidden by default */
  z-index: 1000
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body class="showCart">
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
        <button class="ai-button" onclick="window.location.href='ai-page.php'">
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
      <div class="carousel-image"><img src="pphoto/IMG_6067.JPG" alt="Prayer Room Interior 1"></div>
      <div class="carousel-image"><img src="pphoto/IMG_6068.JPG" alt="Prayer Room Interior 2"></div>
      <div class="carousel-image"><img src="pphoto/IMG_6069.jpg" alt="Prayer Room Interior 3"></div>
      <div class="carousel-image"><img src="pphoto/IMG_6070.JPG" alt="Prayer Room Interior 4"></div>
      <div class="carousel-image"><img src="pphoto/IMG_6071.JPG" alt="Prayer Room Interior 5"></div>
      <div class="carousel-image"><img src="pphoto/IMG_6072.JPG" alt="Prayer Room Interior 6"></div>
    </div>
    <div class="carousel-overlay">
      <h1>Prayer Room Category</h1>
      <div class="breadcrumb">
        <a href="home.php"><i class='bx bx-home'></i>Home</a><a href="gallery.php">/ Gallery </a> / Prayer Room
      </div>
      <h4 class="carousel-subtext">A corner of serenity and devotion — explore inspiring designs for Muslim prayer rooms.</h4>
    </div>
  </div>

  <section class="categories">
    <div class="section-title">
      <h2>Prayer Room Furniture</h2>
      <div class="dropdown">
        <select class="view-all-btn" name="style" id="style" onchange="filterProducts()">
          <option class="choose" value="">Style</option>
          <option class="options" value="Modern">Modern</option>
          <option class="options" value="Traditional">Traditional</option>
          <option class="options" value="Compact">Compact</option>
          <option class="options" value="Minimalist">Minimalist</option>
          <option class="options" value="Luxury">Luxury</option>
        </select>
        <select class="view-all-btn" name="priceRange" id="priceRange" onchange="filterProducts()">
          <option class="choose" value="">Price Range</option>
          <option value="Under $1,000">Under $1,000</option>
          <option value="$1,000 - $3,000">$1,000 - $3,000</option>
          <option value="$3,000 - $5,000">$3,000 - $5,000</option>
          <option value="$5,000+">$5,000+</option>
        </select>
        <select class="view-all-btn" name="size" id="size" onchange="filterProducts()">
          <option class="choose" value="">Features</option>
          <option value="Small">Storage Solutions</option>
          <option value="Medium">Multi-Function</option>
          <option value="Large">Custom Design</option>
        </select>
        <select class="view-all-btn" name="sortOptions" id="sortOptions" onchange="filterProducts()">
          <option class="choose" value="">Sort By: Featured</option>
          <option value="priceLow">Price: Low to High</option>
          <option value="priceHigh">Price: High to Low</option>
          <option value="newest">Newest</option>
          <option value="popular">Most Popular</option>
        </select>
        <button id="clearFilters" class="view-all-btn" style="background-color: #80827b; display: none;" onclick="clearAllFilters()">
          back
        </button>
      </div>
    </div>
  </section>

  <section class="products">
    <div class="product-grid" id="productGrid">
      <div class="product-card" data-style="Modern" data-price="1299" data-size="Medium" data-date="2025-03-15">
        <div class="gallery-image">
          <img src="pphoto/Py1.JPG" alt="Prayer Shelf Cabinet">
        </div>
        <div class="gallery-info">
          <div class="gallery-title">
            <h4>Modern Prayer Shelf Cabinet</h4>
          </div>
          <div class="gallery-description">Elegant wall-mounted prayer shelf with integrated storage for Quran, tasbih beads, and prayer garments. Features built-in LED lighting, hidden compartments, and adjustable shelves in a contemporary design with sustainable bamboo material.</div>
          <div class="gallery-meta">
            <div class="price">$1,299</div>
            <div class="action-buttons">
              <button class="favorite-btn">♡</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      <div class="product-card" data-style="Luxury" data-price="5899" data-size="Large" data-date="2024-12-10">
        <div class="gallery-image">
          <img src="pphoto/Py2.JPG" alt="Premium Prayer Room Suite">
        </div>
        <div class="gallery-info">
          <div class="gallery-title">
            <h4>Premium Prayer Room Suite</h4>
          </div>
          <div class="gallery-description">Complete prayer room ensemble featuring hand-carved wooden mihrab, plush prayer rug with memory foam padding, luxury Quran stand, ambient lighting system, ceiling medallion with Islamic geometric patterns, and integrated audio system for adhan.</div>
          <div class="gallery-meta">
            <div class="price">$5,899</div>
            <div class="action-buttons">
              <button class="favorite-btn">♡</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      <div class="product-card" data-style="Traditional" data-price="2450" data-size="Small" data-date="2025-02-20">
        <div class="gallery-image">
          <img src="pphoto/Py3.JPG" alt="Handcrafted Prayer Shelf">
        </div>
        <div class="gallery-info">
          <div class="gallery-title">
            <h4>Handcrafted Wooden Prayer Shelf</h4>
          </div>
          <div class="gallery-description">Artisanal prayer shelf with traditional arabesque carvings, built-in wooden Quran stand, prayer time display, and compartments for prayer accessories. Features solid walnut construction with mother-of-pearl inlay and brass accents.</div>
          <div class="gallery-meta">
            <div class="price">$2,450</div>
            <div class="action-buttons">
              <button class="favorite-btn">♡</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      <div class="product-card" data-style="Compact" data-price="899" data-size="Small" data-date="2025-04-01">
        <div class="gallery-image">
          <img src="pphoto/Py4.JPG" alt="Compact Prayer Station">
        </div>
        <div class="gallery-info">
          <div class="gallery-title">
            <h4>Compact Prayer Station</h4>
          </div>
          <div class="gallery-description">Space-efficient prayer solution perfect for apartments featuring folding prayer shelf, roll-out prayer mat, built-in compass for qibla direction, concealed storage for prayer attire, and foldable Islamic wall art that can be stored away when not in use.</div>
          <div class="gallery-meta">
            <div class="price">$899</div>
            <div class="action-buttons">
              <button class="favorite-btn">♡</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      <div class="product-card" data-style="Minimalist" data-price="799" data-size="Small" data-date="2025-01-25">
        <div class="gallery-image">
          <img src="pphoto/Py5.JPG" alt="Clean Prayer Nook">
        </div>
        <div class="gallery-info">
          <div class="gallery-title">
            <h4>Minimalist Prayer Nook</h4>
          </div>
          <div class="gallery-description">Sleek, minimalist prayer corner with floating prayer shelf, subtle qibla direction marker, slim profile Quran holder, and hidden storage compartments. Simple clean lines with natural oak finish and customizable accent lighting.</div>
          <div class="gallery-meta">
            <div class="price">$799</div>
            <div class="action-buttons">
              <button class="favorite-btn">♡</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      <div class="product-card" data-style="Modern" data-price="1990" data-size="Medium" data-date="2025-03-10">
        <div class="gallery-image">
          <img src="pphoto/F875EA15-D062-4369-BEAD-7C9DA03B28D0.PNG" alt="Smart Prayer Room System">
        </div>
        <div class="gallery-info">
          <div class="gallery-title">
            <h4>Smart Prayer Room System</h4>
          </div>
          <div class="gallery-description">Tech-integrated prayer room solution with digital prayer times display, automated adhan system, smart lighting that adjusts based on prayer times, temperature-controlled storage for prayer garments, and smartphone app controls for all features.</div>
          <div class="gallery-meta">
            <div class="price">$1,990</div>
            <div class="action-buttons">
              <button class="favorite-btn">♡</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
      <div class="product-card" data-style="Traditional" data-price="3599" data-size="Large" data-date="2024-12-20">
        <div class="gallery-image">
          <img src="pphoto/Py6.JPG" alt="Ornate Prayer Corner">
        </div>
        <div class="gallery-info">
          <div class="gallery-title">
            <h4>Ornate Prayer Corner Ensemble</h4>
          </div>
          <div class="gallery-description">Luxurious traditional prayer space featuring ornate wooden partition wall with Islamic geometric patterns, handcrafted prayer rug with premium wool, calligraphy wall art, antique-inspired Quran stand, and custom wooden shelving for Islamic texts and accessories.</div>
          <div class="gallery-meta">
            <div class="price">$3,599</div>
            <div class="action-buttons">
              <button class="favorite-btn">♡</button>
              <button class="add-to-cart" onclick="addToCart(this)">Add to cart</button>
            </div>
          </div>
        </div>
      </div>
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
          if (priceRange === 'Under $1,000' && cardPrice >= 1000) {
            priceMatch = false;
          } else if (priceRange === '$1,000 - $3,000' && (cardPrice < 1000 || cardPrice > 3000)) {
            priceMatch = false;
          } else if (priceRange === '$3,000 - $5,000' && (cardPrice < 3000 || cardPrice > 5000)) {
            priceMatch = false;
          } else if (priceRange === '$5,000+' && cardPrice < 5000) {
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
          // You can implement popularity logic here          // For now, let's use a simple random sort
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
      
      // Add search input enter key listener
      document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          searchProducts();
        }
      });
    });
  </script>
</body>
</html>