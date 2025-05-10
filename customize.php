<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RoomGenius Customization</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="customize.css">
  <style>
    
  </style>
</head>

<body class="showCart">
  <header>
    <div class="logo">
      <i class="fas fa-brain"></i>
      <i class="fas fa-couch"></i>
      RoomGenius
    </div>

    
    
    <div class="right-section">
      <div class="icons">
        <span class="cart-container" id="cartIcon">
          <i class="fas fa-shopping-cart"></i>
          <span class="cart-count" id="cartCount">0</span>
        </span>
        <span class="user-container">
          <i class="fas fa-user"></i>
        </span>
      </div>
      <div class="ai-button-container">
        <button class="ai-button" onclick="window.location.href='ai-page.html'">
        <i class='bx bx-image'></i>Gallery
        </button>
      </div>
    </div>
  </header>

  <div class="cartTab">
    <h1>Shopping Cart</h1>
    <div class="listCart">
      <div class="empty-cart">Your cart is empty</div>
    </div>
    <div class="btn">
      <button class="close">CLOSE</button>
      <button class="checkOut">CHECK OUT</button>
    </div>
  </div>
  <script src="cart.js"></script>

  <div class="page-title">
    <h1>Customize Your Product</h1>
    <p>Didn't find what you're looking for? Create your own custom piece that perfectly fits your space and style.</p>
  </div>

  <div class="customization-container">
    <div class="preview-section">
      <h2 class="section-heading">Product Preview</h2>
      <div class="product-preview" id="productPreview">
        <div class="upload-container">
          <i class="fas fa-cloud-upload-alt upload-icon"></i>
          <div class="upload-text">Upload an image of your desired product</div>
          <label for="imageUpload" class="upload-button">
            <i class="fas fa-upload"></i> Choose Image
          </label>
          <input type="file" id="imageUpload" class="file-input" accept="image/*">
        </div>
      </div>
      <div class="price-estimate">
        <h3>Estimated Price</h3>
        <div class="price" id="priceDisplay">$0.00</div>
        <p>Final price may vary based on customization details</p>
        <div class="price-alert" id="priceAlert">
          Warning: Your selections exceed your budget!
        </div>
      </div>
      
      <!-- Add price breakdown section -->
      <div class="price-breakdown" id="priceBreakdown">
        <h4>Price Breakdown</h4>
        <div id="priceBreakdownItems">
          <!-- Price items will be dynamically added here -->
        </div>
        <div class="total-price">
          <span>Total:</span>
          <span id="totalPriceValue">$0.00</span>
        </div>
      </div>
    </div>

    <div class="options-section">
      <h2 class="section-heading">Customization Options</h2>
      <form id="customizationForm">
        <div class="form-group">
          <label for="productType">Product Type</label>
          <select class="form-control" id="productType">
            <option value="">Select a product type</option>
            <option value="sofa" data-price="800">Sofa ($800)</option>
            <option value="chair" data-price="350">Chair ($350)</option>
            <option value="table" data-price="600">Table ($600)</option>
            <option value="bed" data-price="1200">Bed ($1200)</option>
            <option value="cabinet" data-price="500">Cabinet ($500)</option>
            <option value="shelving" data-price="400">Shelving ($400)</option>
            <option value="desk" data-price="550">Desk ($550)</option>
            <option value="ottoman" data-price="250">Ottoman ($250)</option>
            <option value="other" data-price="300">Other (specify in notes) ($300)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="style">Style</label>
          <select class="form-control" id="style">
            <option value="">Select a style</option>
            <option value="Modern" data-price="150">Modern ($150)</option>
            <option value="Traditional" data-price="175">Traditional ($175)</option>
            <option value="Rustic" data-price="200">Rustic ($200)</option>
            <option value="Minimalist" data-price="100">Minimalist ($100)</option>
            <option value="Industrial" data-price="225">Industrial ($225)</option>
            <option value="Scandinavian" data-price="175">Scandinavian ($175)</option>
            <option value="Bohemian" data-price="190">Bohemian ($190)</option>
            <option value="Contemporary" data-price="160">Contemporary ($160)</option>
            <option value="Coastal" data-price="180">Coastal ($180)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="material">Primary Material</label>
          <select class="form-control" id="material">
            <option value="">Select a material</option>
            <option value="wood" data-price="250">Wood ($250)</option>
            <option value="fabric" data-price="180">Fabric ($180)</option>
            <option value="leather" data-price="450">Leather ($450)</option>
            <option value="metal" data-price="300">Metal ($300)</option>
            <option value="glass" data-price="280">Glass ($280)</option>
            <option value="plastic" data-price="120">Plastic ($120)</option>
            <option value="stone" data-price="650">Stone ($650)</option>
            <option value="composite" data-price="220">Composite ($220)</option>
          </select>
        </div>

        <div class="form-group" id="woodTypeGroup" style="display: none;">
          <label for="woodType">Wood Type</label>
          <select class="form-control" id="woodType">
            <option value="">Select wood type</option>
            <option value="oak" data-price="200">Oak ($200)</option>
            <option value="pine" data-price="120">Pine ($120)</option>
            <option value="maple" data-price="220">Maple ($220)</option>
            <option value="walnut" data-price="350">Walnut ($350)</option>
            <option value="cherry" data-price="320">Cherry ($320)</option>
            <option value="mahogany" data-price="380">Mahogany ($380)</option>
            <option value="teak" data-price="400">Teak ($400)</option>
            <option value="birch" data-price="180">Birch ($180)</option>
          </select>
        </div>

        <div class="form-group" id="fabricTypeGroup" style="display: none;">
          <label for="fabricType">Fabric Type</label>
          <select class="form-control" id="fabricType">
            <option value="">Select fabric type</option>
            <option value="cotton" data-price="100">Cotton ($100)</option>
            <option value="linen" data-price="150">Linen ($150)</option>
            <option value="velvet" data-price="220">Velvet ($220)</option>
            <option value="microfiber" data-price="120">Microfiber ($120)</option>
            <option value="polyester" data-price="80">Polyester ($80)</option>
            <option value="wool" data-price="180">Wool ($180)</option>
            <option value="silk" data-price="350">Silk ($350)</option>
            <option value="canvas" data-price="90">Canvas ($90)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="color">Color</label>
          <select class="form-control" id="colorSelect">
            <option value="">Select color style</option>
            <option value="solid" data-price="50">Solid Color ($50)</option>
            <option value="pattern" data-price="120">Pattern ($120)</option>
            <option value="natural" data-price="30">Natural Finish ($30)</option>
            <option value="stained" data-price="75">Stained ($75)</option>
          </select>
          <div class="color-options" id="colorOptions">
            <div class="color-option" style="background-color: #8B4513;" data-color="Brown" data-price="40" title="Brown ($40)"></div>
            <div class="color-option" style="background-color: #D2B48C;" data-color="Beige" data-price="30" title="Beige ($30)"></div>
            <div class="color-option" style="background-color: #808080;" data-color="Gray" data-price="35" title="Gray ($35)"></div>
            <div class="color-option" style="background-color: #000000;" data-color="Black" data-price="25" title="Black ($25)"></div>
            <div class="color-option" style="background-color: #FFFFFF; border: 1px solid #ddd;" data-color="White" data-price="20" title="White ($20)"></div>
            <div class="color-option" style="background-color: #1E3A5F;" data-color="Navy" data-price="45" title="Navy ($45)"></div>
            <div class="color-option" style="background-color: #006400;" data-color="Green" data-price="50" title="Green ($50)"></div>
            <div class="color-option" style="background-color: #8B0000;" data-color="Red" data-price="55" title="Red ($55)"></div>
          </div>
        </div>

        <div class="form-group">
          <label>Finish Type</label>
          <select class="form-control" id="finishType">
            <option value="">Select finish</option>
            <option value="matte" data-price="50">Matte ($50)</option>
            <option value="glossy" data-price="80">Glossy ($80)</option>
            <option value="semi-gloss" data-price="65">Semi-Gloss ($65)</option>
            <option value="satin" data-price="70">Satin ($70)</option>
            <option value="distressed" data-price="90">Distressed ($90)</option>
            <option value="antique" data-price="100">Antique ($100)</option>
          </select>
        </div>

        <div class="form-group">
          <label>Dimensions</label>
          <div class="dimension-inputs">
            <input type="number" class="form-control" id="width" placeholder="Width (cm)" min="1" data-price-per-unit="2">
            <input type="number" class="form-control" id="depth" placeholder="Depth (cm)" min="1" data-price-per-unit="2">
            <input type="number" class="form-control" id="height" placeholder="Height (cm)" min="1" data-price-per-unit="2">
          </div>
          <p style="margin-top: 5px; font-size: 12px; color: #666;">
            Each dimension costs $2 per cm
          </p>
        </div>

        <div class="form-group">
          <label>Shape/Configuration</label>
          <select class="form-control" id="shapeConfig">
            <option value="">Select shape/configuration</option>
            <option value="rectangular" data-price="50">Rectangular ($50)</option>
            <option value="round" data-price="70">Round ($70)</option>
            <option value="square" data-price="50">Square ($50)</option>
            <option value="oval" data-price="80">Oval ($80)</option>
            <option value="L-shaped" data-price="150">L-Shaped ($150)</option>
            <option value="U-shaped" data-price="200">U-Shaped ($200)</option>
            <option value="modular" data-price="180">Modular ($180)</option>
            <option value="custom" data-price="250">Custom (specify in notes) ($250)</option>
          </select>
        </div>

        <div class="form-group">
          <label>Price Range</label>
          <input type="range" class="range-slider" id="priceRange" min="200" max="5000" step="100" value="1000">
          <div class="range-info">
            <span>$200</span>
            <span id="priceRangeValue">$1000</span>
            <span>$5000+</span>
          </div>
          <p style="margin-top: 10px; font-size: 12px; color: #666;">
            Note: Higher price ranges allow for premium materials and features
          </p>
        </div>

        <div class="form-group">
          <label>Add-on Features</label>
          <div class="add-on-options" id="addOnOptions">
            <div class="add-on-option" data-addon="storage" data-price="120">Storage ($120)</div>
            <div class="add-on-option" data-addon="cushions" data-price="80">Extra Cushions ($80)</div>
            <div class="add-on-option" data-addon="pillows" data-price="60">Decorative Pillows ($60)</div>
            <div class="add-on-option" data-addon="usb" data-price="90">USB Ports ($90)</div>
            <div class="add-on-option" data-addon="lighting" data-price="150">Integrated Lighting ($150)</div>
            <div class="add-on-option" data-addon="extendable" data-price="200">Extendable/Adjustable ($200)</div>
          </div>
        </div>

        <div class="form-group">
          <label for="notes">Additional Notes</label>
          <textarea class="form-control" id="notes" rows="4" placeholder="Any specific details or requirements..."></textarea>
        </div>

        <button type="button" class="order-button" id="orderButton">
          Place Custom Order
        </button>
      </form>
    </div>
  </div>

  <div class="notification" id="notification"></div>
  <script>
    // Modified script for customize.php
document.addEventListener('DOMContentLoaded', function() {
  // Cart functionality
  const cartIcon = document.getElementById('cartIcon');
  const cartTab = document.querySelector('.cartTab');
  const closeBtn = document.querySelector('.close');
  const cartCount = document.getElementById('cartCount');
  const listCart = document.querySelector('.listCart');
  const emptyCartMsg = document.querySelector('.empty-cart');
  
  // Product customization elements
  const productType = document.getElementById('productType');
  const style = document.getElementById('style');
  const material = document.getElementById('material');
  const woodTypeGroup = document.getElementById('woodTypeGroup');
  const fabricTypeGroup = document.getElementById('fabricTypeGroup');
  const woodType = document.getElementById('woodType');
  const fabricType = document.getElementById('fabricType');
  const colorSelect = document.getElementById('colorSelect');
  const colorOptions = document.getElementById('colorOptions');
  const finishType = document.getElementById('finishType');
  const width = document.getElementById('width');
  const depth = document.getElementById('depth');
  const height = document.getElementById('height');
  const shapeConfig = document.getElementById('shapeConfig');
  const priceRange = document.getElementById('priceRange');
  const priceRangeValue = document.getElementById('priceRangeValue');
  const addOnOptions = document.getElementById('addOnOptions');
  const notes = document.getElementById('notes');
  const orderButton = document.getElementById('orderButton');
  const imageUpload = document.getElementById('imageUpload');
  const productPreview = document.getElementById('productPreview');
  const priceDisplay = document.getElementById('priceDisplay');
  const priceAlert = document.getElementById('priceAlert');
  const priceBreakdown = document.getElementById('priceBreakdown');
  const priceBreakdownItems = document.getElementById('priceBreakdownItems');
  const totalPriceValue = document.getElementById('totalPriceValue');
  const notification = document.getElementById('notification');

  let selectedColor = '';
  let selectedAddOns = [];
  let uploadedImageUrl = ''; // To store the uploaded image URL

  // Material dependent fields
  material.addEventListener('change', function() {
    const selectedMaterial = this.value;
    
    // Hide all material-specific groups first
    woodTypeGroup.style.display = 'none';
    fabricTypeGroup.style.display = 'none';
    
    // Show the relevant group based on selection
    if (selectedMaterial === 'wood') {
      woodTypeGroup.style.display = 'block';
    } else if (selectedMaterial === 'fabric') {
      fabricTypeGroup.style.display = 'block';
    }
    
    updatePrice();
  });

  // Color selection
  colorSelect.addEventListener('change', function() {
    if (this.value) {
      colorOptions.style.display = 'flex';
    } else {
      colorOptions.style.display = 'none';
    }
    updatePrice();
  });

  // Individual color option selection
  const colorOptionElements = document.querySelectorAll('.color-option');
  colorOptionElements.forEach(option => {
    option.addEventListener('click', function() {
      // Remove active class from all color options
      colorOptionElements.forEach(opt => opt.classList.remove('active'));
      
      // Add active class to selected option
      this.classList.add('active');
      selectedColor = this.getAttribute('data-color');
      
      updatePrice();
    });
  });

  // Add-on options selection
  const addOnOptionElements = document.querySelectorAll('.add-on-option');
  addOnOptionElements.forEach(option => {
    option.addEventListener('click', function() {
      this.classList.toggle('active');
      
      const addon = this.getAttribute('data-addon');
      if (this.classList.contains('active')) {
        if (!selectedAddOns.includes(addon)) {
          selectedAddOns.push(addon);
        }
      } else {
        selectedAddOns = selectedAddOns.filter(item => item !== addon);
      }
      
      updatePrice();
    });
  });

  // Price range slider
  priceRange.addEventListener('input', function() {
    priceRangeValue.textContent = '$' + this.value;
    checkBudget();
  });

  // Add event listeners to all fields that affect price
  const priceAffectingFields = [
    productType, style, material, woodType, fabricType, 
    colorSelect, finishType, width, depth, height, shapeConfig
  ];
  
  priceAffectingFields.forEach(field => {
    if (field) {
      field.addEventListener('change', updatePrice);
    }
  });

  // For dimension fields, use input event to capture changes as they type
  const dimensionFields = [width, depth, height];
  dimensionFields.forEach(field => {
    field.addEventListener('input', updatePrice);
  });

  // Image upload preview
  imageUpload.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        uploadedImageUrl = e.target.result;
        productPreview.innerHTML = `<img src="${uploadedImageUrl}" alt="Product Preview" class="preview-image">`;
      };
      reader.readAsDataURL(file);
    }
  });

  // Calculate total price based on all selections
  function updatePrice() {
    let totalPrice = 0;
    let priceBreakdownHTML = '';
    
    // Base price from product type
    if (productType.value) {
      const productBasePrice = parseFloat(productType.options[productType.selectedIndex].getAttribute('data-price'));
      totalPrice += productBasePrice;
      priceBreakdownHTML += `<div class="price-item">
        <span>Base price (${productType.options[productType.selectedIndex].text})</span>
        <span>$${productBasePrice.toFixed(2)}</span>
      </div>`;
    }
    
    // Style price
    if (style.value) {
      const stylePrice = parseFloat(style.options[style.selectedIndex].getAttribute('data-price'));
      totalPrice += stylePrice;
      priceBreakdownHTML += `<div class="price-item">
        <span>Style (${style.options[style.selectedIndex].text})</span>
        <span>$${stylePrice.toFixed(2)}</span>
      </div>`;
    }
    
    // Material price
    if (material.value) {
      const materialPrice = parseFloat(material.options[material.selectedIndex].getAttribute('data-price'));
      totalPrice += materialPrice;
      priceBreakdownHTML += `<div class="price-item">
        <span>Material (${material.options[material.selectedIndex].text})</span>
        <span>$${materialPrice.toFixed(2)}</span>
      </div>`;
      
      // Add wood type price if material is wood
      if (material.value === 'wood' && woodType.value) {
        const woodPrice = parseFloat(woodType.options[woodType.selectedIndex].getAttribute('data-price'));
        totalPrice += woodPrice;
        priceBreakdownHTML += `<div class="price-item">
          <span>Wood Type (${woodType.options[woodType.selectedIndex].text})</span>
          <span>$${woodPrice.toFixed(2)}</span>
        </div>`;
      }
      
      // Add fabric type price if material is fabric
      if (material.value === 'fabric' && fabricType.value) {
        const fabricPrice = parseFloat(fabricType.options[fabricType.selectedIndex].getAttribute('data-price'));
        totalPrice += fabricPrice;
        priceBreakdownHTML += `<div class="price-item">
          <span>Fabric Type (${fabricType.options[fabricType.selectedIndex].text})</span>
          <span>$${fabricPrice.toFixed(2)}</span>
        </div>`;
      }
    }
    
    // Color style price
    if (colorSelect.value) {
      const colorStylePrice = parseFloat(colorSelect.options[colorSelect.selectedIndex].getAttribute('data-price'));
      totalPrice += colorStylePrice;
      priceBreakdownHTML += `<div class="price-item">
        <span>Color Style (${colorSelect.options[colorSelect.selectedIndex].text})</span>
        <span>$${colorStylePrice.toFixed(2)}</span>
      </div>`;
      
      // Add specific color price if selected
      const activeColor = document.querySelector('.color-option.active');
      if (activeColor) {
        const colorPrice = parseFloat(activeColor.getAttribute('data-price'));
        totalPrice += colorPrice;
        priceBreakdownHTML += `<div class="price-item">
          <span>Color (${activeColor.getAttribute('data-color')})</span>
          <span>$${colorPrice.toFixed(2)}</span>
        </div>`;
      }
    }
    
    // Finish type price
    if (finishType.value) {
      const finishPrice = parseFloat(finishType.options[finishType.selectedIndex].getAttribute('data-price'));
      totalPrice += finishPrice;
      priceBreakdownHTML += `<div class="price-item">
        <span>Finish (${finishType.options[finishType.selectedIndex].text})</span>
        <span>$${finishPrice.toFixed(2)}</span>
      </div>`;
    }
    
    // Dimensions price
    let dimensionsTotal = 0;
    let dimensionsText = '';
    
    if (width.value) {
      const widthPrice = parseFloat(width.value) * parseFloat(width.getAttribute('data-price-per-unit'));
      dimensionsTotal += widthPrice;
      dimensionsText += `Width: ${width.value}cm ($${widthPrice.toFixed(2)}) `;
    }
    
    if (depth.value) {
      const depthPrice = parseFloat(depth.value) * parseFloat(depth.getAttribute('data-price-per-unit'));
      dimensionsTotal += depthPrice;
      dimensionsText += `Depth: ${depth.value}cm ($${depthPrice.toFixed(2)}) `;
    }
    
    if (height.value) {
      const heightPrice = parseFloat(height.value) * parseFloat(height.getAttribute('data-price-per-unit'));
      dimensionsTotal += heightPrice;
      dimensionsText += `Height: ${height.value}cm ($${heightPrice.toFixed(2)})`;
    }
    
    if (dimensionsTotal > 0) {
      totalPrice += dimensionsTotal;
      priceBreakdownHTML += `<div class="price-item">
        <span>Dimensions (${dimensionsText})</span>
        <span>$${dimensionsTotal.toFixed(2)}</span>
      </div>`;
    }
    
    // Shape/configuration price
    if (shapeConfig.value) {
      const shapePrice = parseFloat(shapeConfig.options[shapeConfig.selectedIndex].getAttribute('data-price'));
      totalPrice += shapePrice;
      priceBreakdownHTML += `<div class="price-item">
        <span>Shape (${shapeConfig.options[shapeConfig.selectedIndex].text})</span>
        <span>$${shapePrice.toFixed(2)}</span>
      </div>`;
    }
    
    // Add-on features
    let addonsTotal = 0;
    let addonsText = '';
    
    selectedAddOns.forEach(addon => {
      const addonElement = document.querySelector(`.add-on-option[data-addon="${addon}"]`);
      if (addonElement) {
        const addonPrice = parseFloat(addonElement.getAttribute('data-price'));
        addonsTotal += addonPrice;
        addonsText += `${addonElement.textContent} `;
      }
    });
    
    if (addonsTotal > 0) {
      totalPrice += addonsTotal;
      priceBreakdownHTML += `<div class="price-item">
        <span>Add-ons (${addonsText})</span>
        <span>$${addonsTotal.toFixed(2)}</span>
      </div>`;
    }
    
    // Update price display
    priceDisplay.textContent = '$' + totalPrice.toFixed(2);
    priceBreakdownItems.innerHTML = priceBreakdownHTML;
    totalPriceValue.textContent = '$' + totalPrice.toFixed(2);
    
    // Check if price exceeds budget
    checkBudget(totalPrice);
    
    return totalPrice;
  }

  // Check if price exceeds budget and show warning
  function checkBudget(currentPrice) {
    const budget = parseFloat(priceRange.value);
    if (currentPrice > budget) {
      priceAlert.style.display = 'block';
    } else {
      priceAlert.style.display = 'none';
    }
  }

  // Show notification
  function showNotification(message, type) {
    notification.textContent = message;
    notification.className = 'notification ' + type;
    notification.style.display = 'block';
    
    setTimeout(() => {
      notification.style.display = 'none';
    }, 3000);
  }

  // Place order button click handler
  orderButton.addEventListener('click', function() {
    const finalPrice = updatePrice();
    const budget = parseFloat(priceRange.value);
    
    if (finalPrice > budget) {
      showNotification('Warning: Your selections exceed your budget!', 'warning');
      return;
    }
    
    if (!productType.value) {
      showNotification('Please select a product type', 'error');
      return;
    }
    
    if (!material.value) {
      showNotification('Please select a material', 'error');
      return;
    }
    
    // Get product type text
    const productTypeText = productType.options[productType.selectedIndex].text.replace(/\(\$[0-9.]+\)/, '').trim();
    const materialText = material.value ? material.options[material.selectedIndex].text.replace(/\(\$[0-9.]+\)/, '').trim() : '';
    const styleText = style.value ? style.options[style.selectedIndex].text.replace(/\(\$[0-9.]+\)/, '').trim() : '';
    
    // Create product description with details
    let productDescription = `${styleText} ${materialText}`;
    
    // Add wood or fabric type if applicable
    if (material.value === 'wood' && woodType.value) {
      productDescription += ` (${woodType.options[woodType.selectedIndex].text.replace(/\(\$[0-9.]+\)/, '').trim()})`;
    } else if (material.value === 'fabric' && fabricType.value) {
      productDescription += ` (${fabricType.options[fabricType.selectedIndex].text.replace(/\(\$[0-9.]+\)/, '').trim()})`;
    }
    
    // Add dimensions if provided
    if (width.value && height.value) {
      productDescription += ` - ${width.value}x${height.value}cm`;
    }
    
    // Generate a placeholder image if no image was uploaded
    let productImage = uploadedImageUrl || '/api/placeholder/400/320';
    
    // Create custom product item with all necessary info for cart
    const customItem = {
      id: 'custom-' + Date.now(),
      name: `Custom ${productTypeText}`,
      price: finalPrice,
      img: productImage,
      quantity: 1,
      description: productDescription
    };
    
    // Access the global cart manager from cart.js
    if (window.cartManager) {
      // Add item to cart using the global cart manager
      window.cartManager.cart.push(customItem);
      window.cartManager.saveCart();
      window.cartManager.updateCartUI();
      window.cartManager.updateCartCount();
      window.cartManager.showNotification(`Custom ${productTypeText} added to cart!`);
    } else {
      // Fallback if global cart manager is not available
      // Try to load cart from localStorage
      let cart = [];
      const savedCart = localStorage.getItem('roomGeniusCart');
      if (savedCart) {
        cart = JSON.parse(savedCart);
      }
      
      // Add item to cart
      cart.push(customItem);
      
      // Save to localStorage
      localStorage.setItem('roomGeniusCart', JSON.stringify(cart));
      
      // Update cart count
      const cartCountElement = document.getElementById('cartCount');
      if (cartCountElement) {
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        cartCountElement.textContent = totalItems;
      }
      
      // Show notification
      showNotification(`Custom ${productTypeText} added to cart!`, 'success');
    }
    
    // Reset form
    resetForm();
  });

  // Reset form
  function resetForm() {
    document.getElementById('customizationForm').reset();
    productPreview.innerHTML = `
      <div class="upload-container">
        <i class="fas fa-cloud-upload-alt upload-icon"></i>
        <div class="upload-text">Upload an image of your desired product</div>
        <label for="imageUpload" class="upload-button">
          <i class="fas fa-upload"></i> Choose Image
        </label>
        <input type="file" id="imageUpload" class="file-input" accept="image/*">
      </div>
    `;
    woodTypeGroup.style.display = 'none';
    fabricTypeGroup.style.display = 'none';
    colorOptions.style.display = 'none';
    selectedColor = '';
    selectedAddOns = [];
    uploadedImageUrl = '';
    document.querySelectorAll('.add-on-option').forEach(opt => opt.classList.remove('active'));
    document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
    
    updatePrice();
  }

  // Initialize
  updatePrice();
  priceAlert.style.display = 'none';
});
    </script>

</body>
</html>