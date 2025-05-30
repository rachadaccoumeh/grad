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

// Get category from URL parameter
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Map URL-friendly category names to database category names
$category_map = [
    'livingroom' => 'Living Room',
    'bedroom' => 'Bedroom',
    'kitchen' => 'Kitchen',
    'office' => 'Office',
    'diningroom' => 'Dining Room',
    'gameroom' => 'Game Room',
    'gym' => 'Gym',
    'prayerroom' => 'Prayer Room',
    'garden' => 'Garden',
    'closet' => 'Closet',
    'nursery' => 'Nursery',
    'bathroom' => 'Bathroom'
];

// Use mapped category if it exists, otherwise use the original with first letter capitalized
$db_category = $category_map[strtolower($category)] ?? ucwords(str_replace('-', ' ', $category));
$category_title = $db_category;

// For backward compatibility with existing URLs
if ($category === 'livingroom') {
    $db_category = 'Living Room';
}

// If no category is specified, redirect to gallery
if (empty($category)) {
  header('Location: gallery.php');
  exit;
}

// Get products from database filtered by category
$query = "SELECT * FROM products WHERE category = ? ORDER BY date_added DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $db_category);
$stmt->execute();
$result = $stmt->get_result();

// Debug: Uncomment the following line to see the actual query being executed
// error_log("Query: " . $query . " - Category: " . $db_category);

// Initialize an array to store products
$products = [];
if ($result && $result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $products[] = $row;
  }
}

// Get unique styles from products
$styles = [];
foreach ($products as $product) {
  if (isset($product['style']) && !empty($product['style']) && !in_array($product['style'], $styles)) {
    $styles[] = $product['style'];
  }
}
sort($styles); // Sort styles alphabetically

// Get unique sizes from products
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
  <title>RoomGenius - <?php echo $category_title; ?></title>
  <link rel="stylesheet" href="gallery.css">
  <link rel="stylesheet" href="footer.css"> <!-- Adding footer stylesheet -->
  <link rel="stylesheet" href="css/product-modal.css"> 
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Adding AOS (Animate On Scroll) library for scroll animations -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
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
      <button class="ai-button" onclick="window.location.href='ai-page.php'">
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
    <!-- Dynamic category-specific carousel -->
    <div class="carousel" id="animated-carousel">
      <?php if ($category == 'livingroom'): ?>
        <!-- Living Room Carousel Images -->
        <div class="carousel-image"><img src="lrphoto/IMG_5794.JPG" alt="Living Room Interior" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="lrphoto/IMG_5795.JPG" alt="Living Room Interior" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="lrphoto/IMG_5796.JPG" alt="Living Room Interior" data-aos="fade-right" data-aos-delay="200"></div>
        <div class="carousel-image"><img src="lrphoto/IMG_5798.jpg" alt="Living Room Interior" data-aos="fade-right" data-aos-delay="300"></div>
        <div class="carousel-image"><img src="lrphoto/IMG_5799.JPG" alt="Living Room Interior" data-aos="fade-right" data-aos-delay="400"></div>
        <div class="carousel-image"><img src="lrphoto/IMG_5801.jpg" alt="Living Room Interior" data-aos="fade-right" data-aos-delay="500"></div>
      <?php elseif ($category == 'bedroom'): ?>
        <!-- Bedroom Carousel Images -->
        <div class="carousel-image"><img src="brphoto/IMG_5882.JPG" alt="Bedroom Interior" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="brphoto/IMG_5883.JPG" alt="Bedroom Interior" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="brphoto/IMG_5884.JPG" alt="Bedroom Interior" data-aos="fade-right" data-aos-delay="200"></div>
        <div class="carousel-image"><img src="brphoto/IMG_5885.JPG" alt="Bedroom Interior" data-aos="fade-right" data-aos-delay="300"></div>
        <div class="carousel-image"><img src="brphoto/IMG_5886.JPG" alt="Bedroom Interior" data-aos="fade-right" data-aos-delay="400"></div>
        <div class="carousel-image"><img src="brphoto/IMG_5889.JPG" alt="Bedroom Interior" data-aos="fade-right" data-aos-delay="500"></div>
      <?php elseif ($category == 'kitchen'): ?>
        <!-- Kitchen Carousel Images -->
        <div class="carousel-image"><img src="kitchenphotos/IMG_4728.jpg" alt="Kitchen Interior" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="kitchenphotos/IMG_4729.JPG" alt="Kitchen Interior" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="kitchenphotos/IMG_4730.jpg" alt="Kitchen Interior" data-aos="fade-right" data-aos-delay="200"></div>
        <div class="carousel-image"><img src="kitchenphotos/IMG_4731.jpg" alt="Kitchen Interior" data-aos="fade-right" data-aos-delay="300"></div>
        <div class="carousel-image"><img src="kitchenphotos/IMG_4732.jpg" alt="Kitchen Interior" data-aos="fade-right" data-aos-delay="400"></div>
        <div class="carousel-image"><img src="kitchenphotos/IMG_4737.jpg" alt="Kitchen Interior" data-aos="fade-right" data-aos-delay="500"></div>
      <?php elseif ($category == 'office'): ?>
        <!-- Office Carousel Images -->
        <div class="carousel-image"><img src="officephotos/office1.jpg" alt="Office Interior" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="officephotos/office2.jpg" alt="Office Interior" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="officephotos/office3.jpg" alt="Office Interior" data-aos="fade-right" data-aos-delay="200"></div>
        <div class="carousel-image"><img src="officephotos/office4.jpg" alt="Office Interior" data-aos="fade-right" data-aos-delay="300"></div>
        <div class="carousel-image"><img src="officephotos/office5.jpg" alt="Office Interior" data-aos="fade-right" data-aos-delay="400"></div>
      <?php elseif ($category == 'diningroom'): ?>
        <!-- Dining Room Carousel Images -->
        <div class="carousel-image"><img src="photos/diningroom.PNG" alt="Dining Room Interior" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="photos/diningroom2.jpg" alt="Dining Room Interior" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="photos/diningroom3.jpg" alt="Dining Room Interior" data-aos="fade-right" data-aos-delay="200"></div>
      <?php elseif ($category == 'gameroom'): ?>
        <!-- Game Room Carousel Images -->
        <div class="carousel-image"><img src="grphoto/IMG_6018.jpg" alt="Game Room Interior" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="grphoto/IMG_6019.jpg" alt="Game Room Interior" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="grphoto/IMG_6025.JPG" alt="Game Room Interior" data-aos="fade-right" data-aos-delay="200"></div>
        <div class="carousel-image"><img src="grphoto/IMG_6026.JPG" alt="Game Room Interior" data-aos="fade-right" data-aos-delay="300"></div>
        <div class="carousel-image"><img src="grphoto/IMG_6027.JPG" alt="Game Room Interior" data-aos="fade-right" data-aos-delay="400"></div>
        <div class="carousel-image"><img src="grphoto/IMG_6028.JPG" alt="Game Room Interior" data-aos="fade-right" data-aos-delay="500"></div>
      <?php elseif ($category == 'gym'): ?>
        <!-- Gym Carousel Images -->
        <div class="carousel-image"><img src="gyphoto/IMG_6043.jpg" alt="Gym Interior" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="gyphoto/IMG_6044.JPG" alt="Gym Interior" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="gyphoto/IMG_6046.JPG" alt="Gym Interior" data-aos="fade-right" data-aos-delay="200"></div>
        <div class="carousel-image"><img src="gyphoto/IMG_6047.JPG" alt="Gym Interior" data-aos="fade-right" data-aos-delay="300"></div>
        <div class="carousel-image"><img src="gyphoto/IMG_6048.JPG" alt="Gym Interior" data-aos="fade-right" data-aos-delay="400"></div>
        <div class="carousel-image"><img src="gyphoto/IMG_6049.JPG" alt="Gym Interior" data-aos="fade-right" data-aos-delay="500"></div>
      <?php elseif ($category == 'prayerroom'): ?>
        <!-- Prayer Room Carousel Images -->
        <div class="carousel-image"><img src="pphoto/IMG_6067.JPG" alt="Prayer Room Interior" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="pphoto/IMG_6068.JPG" alt="Prayer Room Interior" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="pphoto/IMG_6069.jpg" alt="Prayer Room Interior" data-aos="fade-right" data-aos-delay="200"></div>
        <div class="carousel-image"><img src="pphoto/IMG_6070.JPG" alt="Prayer Room Interior" data-aos="fade-right" data-aos-delay="300"></div>
        <div class="carousel-image"><img src="pphoto/IMG_6071.JPG" alt="Prayer Room Interior" data-aos="fade-right" data-aos-delay="400"></div>
        <div class="carousel-image"><img src="pphoto/IMG_6072.JPG" alt="Prayer Room Interior" data-aos="fade-right" data-aos-delay="500"></div>
      <?php elseif ($category == 'garden'): ?>
        <!-- Garden Carousel Images -->
        <div class="carousel-image"><img src="garden/IMG_6085.JPG" alt="Garden Furniture" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="garden/IMG_6086.JPG" alt="Garden Furniture" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="garden/IMG_6088.JPG" alt="Garden Furniture" data-aos="fade-right" data-aos-delay="200"></div>
        <div class="carousel-image"><img src="garden/IMG_6089.JPG" alt="Garden Furniture" data-aos="fade-right" data-aos-delay="300"></div>
        <div class="carousel-image"><img src="garden/IMG_6090.JPG" alt="Garden Furniture" data-aos="fade-right" data-aos-delay="400"></div>
        <div class="carousel-image"><img src="garden/IMG_6091.JPG" alt="Garden Furniture" data-aos="fade-right" data-aos-delay="500"></div>
      <?php elseif ($category == 'closet'): ?>
        <!-- Closet Carousel Images -->
        <div class="carousel-image"><img src="closet/IMG_6106.JPG" alt="Closet Furniture" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="closet/IMG_6107.JPG" alt="Closet Furniture" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="closet/IMG_6108.JPG" alt="Closet Furniture" data-aos="fade-right" data-aos-delay="200"></div>
        <div class="carousel-image"><img src="closet/IMG_6109.JPG" alt="Closet Furniture" data-aos="fade-right" data-aos-delay="300"></div>
        <div class="carousel-image"><img src="closet/IMG_6110.JPG" alt="Closet Furniture" data-aos="fade-right" data-aos-delay="400"></div>
        <div class="carousel-image"><img src="closet/IMG_6111.JPG" alt="Closet Furniture" data-aos="fade-right" data-aos-delay="500"></div>
      <?php elseif ($category == 'nursery'): ?>
        <!-- Nursery Carousel Images -->
        <div class="carousel-image"><img src="nursery/IMG-20250503-WA0007.jpg" alt="Nursery Furniture" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="nursery/IMG-20250503-WA0008.jpg" alt="Nursery Furniture" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="nursery/IMG-20250503-WA0009.jpg" alt="Nursery Furniture" data-aos="fade-right" data-aos-delay="200"></div>
        <div class="carousel-image"><img src="nursery/IMG-20250503-WA0010.jpg" alt="Nursery Furniture" data-aos="fade-right" data-aos-delay="300"></div>
        <div class="carousel-image"><img src="nursery/WhatsApp Image 2025-05-03 at 10.31.54_64144aca.jpg" alt="Nursery Furniture" data-aos="fade-right" data-aos-delay="400"></div>
        <div class="carousel-image"><img src="nursery/WhatsApp Image 2025-05-03 at 10.32.49_59eac4d2.jpg" alt="Nursery Furniture" data-aos="fade-right" data-aos-delay="500"></div>
      <?php else: ?>
        <!-- Default Carousel for other categories (workshop, laundryroom, mudroom, guestroom, bathroom) -->
        <div class="carousel-image"><img src="photos/default_banner.jpg" alt="Category Interior" data-aos="fade-right"></div>
        <div class="carousel-image"><img src="photos/gallery_banner.jpg" alt="Category Interior" data-aos="fade-right" data-aos-delay="100"></div>
        <div class="carousel-image"><img src="photos/home_banner.jpg" alt="Category Interior" data-aos="fade-right" data-aos-delay="200"></div>
      <?php endif; ?>
    </div>
    <div class="carousel-overlay">
      <h1 data-aos="fade-down"><?php echo $category_title; ?></h1>
      <div class="breadcrumb" data-aos="fade-up" data-aos-delay="200">
        <a href="home.php"><i class='bx bx-home'></i>Home</a> / 
        <a href="gallery.php">Gallery</a> / <?php echo $category_title; ?>
      </div>
    </div>
  </div>

  <section class="products" data-aos="fade-up" data-aos-delay="50">
    <div class="section-title">
      <h3><?php echo $category_title; ?> Products</h3>
      <div class="dropdown">
        <!-- Style filter with improved styling -->
        <div class="filter-group">
          <select name="style" id="style" class="view-all-btn" onchange="filterProducts()">
            <option class="choose" value="">All Styles</option>
            <?php foreach($styles as $style): ?>
              <option class="options" value="<?php echo htmlspecialchars($style); ?>"><?php echo htmlspecialchars($style); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <?php if(!empty($sizes)): ?>
        <!-- Size filter with improved styling -->
        <div class="filter-group">
          <select name="size" id="size" class="view-all-btn" onchange="filterProducts()">
            <option class="choose" value="">All Sizes</option>
            <?php foreach($sizes as $size): ?>
              <option class="options" value="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        
        <!-- Price range filter with improved styling -->
        <div class="filter-group">
          <select name="priceRange" id="priceRange" class="view-all-btn" onchange="filterProducts()">
            <option class="choose" value="">All Prices</option>
            <option class="options" value="0-100">$0 - $100</option>
            <option class="options" value="100-500">$100 - $500</option>
            <option class="options" value="500-1000">$500 - $1000</option>
            <option class="options" value="1000+">$1000+</option>
          </select>
        </div>
        
        <!-- Sort options with improved styling -->
        <div class="filter-group">
          <select name="sortOptions" id="sortOptions" class="view-all-btn" onchange="filterProducts()">
            <option class="choose" value="">Sort By</option>
            <option class="options" value="price-asc">Price: Low to High</option>
            <option class="options" value="price-desc">Price: High to Low</option>
            <option class="options" value="name-asc">Name: A to Z</option>
            <option class="options" value="name-desc">Name: Z to A</option>
          </select>
        </div>
        
        <!-- Clear filters button with improved styling -->
        <div class="filter-group">
          <button id="clearFilters" class="view-all-btn" style="background-color: #906e2b;" onclick="clearAllFilters()">Clear Filters</button>
        </div>
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
                    data-image="<?php echo htmlspecialchars($product['image_path']); ?>"
                    data-quantity="<?php echo $product['stock_quantity']; ?>">Add to cart</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div id="noResultsMsg" style="width: 100%; padding: 20px; text-align: center; color: #24424c;">
          <p>No products available in this category. Check back soon!</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Related categories section -->
  <section class="categories" data-aos="fade-up">
    <div class="section-title">
      <h3>Explore Other Categories</h3>
    </div>
    
    <div class="category-grid" data-aos="fade-up" data-aos-delay="30">
      <?php if ($category != 'kitchen'): ?>
      <div class="category-item" data-aos="zoom-in" data-aos-delay="50" onclick="window.location.href='category.php?category=kitchen'">
        <span><img src="image/Photoroom_20250415_183143[1].png" alt="kitchen" width="90" height="90" /></span>
        <p>Kitchen</p>
      </div>
      <?php endif; ?>
      
      <?php if ($category != 'livingroom'): ?>
      <div class="category-item" data-aos="zoom-in" data-aos-delay="70" onclick="window.location.href='category.php?category=livingroom'">
        <span><img src="image/Photoroom_20250415_183117[1].png" alt="Living room" width="90" height="90" /></span>
        <p>Living Room</p>
      </div>
      <?php endif; ?>
      
      <?php if ($category != 'bedroom'): ?>
      <div class="category-item" data-aos="zoom-in" data-aos-delay="90" onclick="window.location.href='category.php?category=bedroom'">
        <span><img src="image/Photoroom_20250415_183037[1].png" alt="bedroom" width="90" height="90" /></span>
        <p>Bedroom</p>
      </div>
      <?php endif; ?>
      
      <?php if ($category != 'office'): ?>
      <div class="category-item" data-aos="zoom-in" data-aos-delay="110" onclick="window.location.href='category.php?category=office'">
        <span><img src="image/Photoroom_20250415_182952[1].png" alt="office" width="90" height="90" /></span>
        <p>Office</p>
      </div>
      <?php endif; ?>
      
      <?php if ($category != 'diningroom'): ?>
      <div class="category-item" data-aos="zoom-in" data-aos-delay="130" onclick="window.location.href='category.php?category=diningroom'">
        <span><img src="image/Photoroom_20250415_182806[1].png" alt="dining room" width="90" height="90" /></span>
        <p>Dining Room</p>
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
    const size = document.getElementById('size') ? document.getElementById('size').value : '';
    const priceRange = document.getElementById('priceRange').value;
    const sortOption = document.getElementById('sortOptions').value;
    const productCards = document.querySelectorAll('.product-card');
    
    let visibleCount = 0;
    
    productCards.forEach(card => {
      const cardStyle = card.getAttribute('data-style');
      const cardSize = card.getAttribute('data-size');
      const cardPrice = parseInt(card.getAttribute('data-price'));
      
      // Check if card matches all selected filters
      let matchesStyle = !style || cardStyle === style;
      let matchesSize = !size || cardSize === size;
      let matchesPrice = true;
      
      if (priceRange) {
        if (priceRange === '0-100') {
          matchesPrice = cardPrice >= 0 && cardPrice <= 100;
        } else if (priceRange === '100-500') {
          matchesPrice = cardPrice > 100 && cardPrice <= 500;
        } else if (priceRange === '500-1000') {
          matchesPrice = cardPrice > 500 && cardPrice <= 1000;
        } else if (priceRange === '1000+') {
          matchesPrice = cardPrice > 1000;
        }
      }
      
      // Show or hide based on filter matches
      if (matchesStyle && matchesSize && matchesPrice) {
        card.style.display = 'flex';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });
    
    // Apply sorting if there are visible products and a sort option is selected
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
        msgElement.innerHTML = '<p>No products match your filters. Please try different criteria.</p>';
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
      if (sortOption === 'price-asc') {
        return parseInt(a.getAttribute('data-price')) - parseInt(b.getAttribute('data-price'));
      } else if (sortOption === 'price-desc') {
        return parseInt(b.getAttribute('data-price')) - parseInt(a.getAttribute('data-price'));
      } else if (sortOption === 'name-asc') {
        return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
      } else if (sortOption === 'name-desc') {
        return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
      }
      return 0;
    });
    
    // Remove all products and re-append in sorted order
    products.forEach(product => {
      productGrid.appendChild(product);
    });
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
    if (document.getElementById('size')) {
      document.getElementById('size').value = '';
    }
    document.getElementById('priceRange').value = '';
    document.getElementById('sortOptions').value = '';
    
    // Show message if no results found
    updateNoResultsMessage(foundCount);
    
    // Show clear filters button if search is active
    toggleClearFiltersButton('', '', '', '', searchInput !== '');
  }
  
  // Function to toggle the clear filters button
  // Function to update the clear filters button styling based on filter state
  // This function now only changes the button appearance rather than hiding/showing it
  function toggleClearFiltersButton(style, size, priceRange, sortOption, searchActive = false) {
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    // If any filter is active, highlight the button more prominently
    if (style || size || priceRange || sortOption !== '' || searchActive) {
      clearFiltersBtn.style.backgroundColor = '#24424c'; // More prominent color when filters are active
    } else {
      clearFiltersBtn.style.backgroundColor = '#906e2b'; // Default color when no filters are active
    }
  }
  
  // Function to clear all filters
  function clearAllFilters() {
    document.getElementById('style').value = '';
    if (document.getElementById('size')) {
      document.getElementById('size').value = '';
    }
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
    
    // Reset clear filters button styling to default
    document.getElementById('clearFilters').style.backgroundColor = '#906e2b';
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
