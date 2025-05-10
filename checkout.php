<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - RoomGenius</title>
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
      border-bottom: 1px solid rgba(36, 66, 76, 0.2);
    }

    .logo {
      font-weight: bold;
      font-size: 24px;
      color: #24424c;
      filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
      text-decoration: none;
    }

    /* Main Content */
    .checkout-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
    }

    @media (max-width: 768px) {
      .checkout-container {
        grid-template-columns: 1fr;
      }
    }

    .checkout-section {
      background-color: #fff8e3;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .section-title {
      font-size: 24px;
      color: #24424c;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid rgba(36, 66, 76, 0.2);
    }

    /* Order Summary */
    .order-summary {
      margin-bottom: 30px;
    }

    .cart-item {
      display: grid;
      grid-template-columns: 80px 1fr auto;
      gap: 15px;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(36, 66, 76, 0.1);
    }

    .cart-item:last-child {
      border-bottom: none;
    }

    .item-image {
      width: 80px;
      height: 80px;
      border-radius: 5px;
      overflow: hidden;
    }

    .item-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .item-details {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .item-name {
      font-weight: bold;
      color: #24424c;
      margin-bottom: 5px;
    }

    .item-quantity {
      font-size: 14px;
      color: #666;
    }

    .item-price {
      font-weight: bold;
      color: #24424c;
      align-self: center;
    }

    .price-details {
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid rgba(36, 66, 76, 0.2);
    }

    .price-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }

    .price-row.total {
      font-weight: bold;
      font-size: 18px;
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid rgba(36, 66, 76, 0.2);
    }

    /* Form Styles */
    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      color: #24424c;
      font-weight: bold;
    }

    input, select {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
    }

    input:focus, select:focus {
      outline: none;
      border-color: #24424c;
    }

    .row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }

    .form-section {
      margin-bottom: 30px;
    }

    .form-section-title {
      font-size: 18px;
      color: #24424c;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid rgba(36, 66, 76, 0.2);
    }

    /* Payment Icons */
    .payment-icons {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }

    .payment-icon {
      width: 40px;
      height: 25px;
      background-color: #ddd;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      color: #24424c;
    }

    /* Payment Method Selection */
    .payment-methods {
      margin-bottom: 20px;
    }

    .payment-method {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .payment-method:hover {
      background-color: #f5f5f5;
    }

    .payment-method.selected {
      border-color: #24424c;
      background-color: rgba(36, 66, 76, 0.05);
    }

    .payment-method input {
      margin-right: 10px;
      width: auto;
    }

    .payment-method-label {
      flex-grow: 1;
    }

    .card-details {
      margin-top: 15px;
    }

    /* Button */
    .place-order-btn {
      background-color: #24424c;
      color: white;
      padding: 15px;
      width: 100%;
      border: none;
      border-radius: 5px;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s;
      margin-top: 20px;
    }

    .place-order-btn:hover {
      background-color: #1b323a;
    }

    .place-order-btn:disabled {
      background-color: #cccccc;
      cursor: not-allowed;
    }

    /* Empty Cart */
    .empty-cart-message {
      text-align: center;
      padding: 40px 0;
      color: #666;
    }

    .back-to-shop {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #24424c;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      transition: background-color 0.3s;
    }

    .back-to-shop:hover {
      background-color: #1b323a;
    }

    /* Order Confirmation */
    .order-confirmation {
      display: none;
      text-align: center;
      padding: 40px 0;
    }

    .confirmation-icon {
      font-size: 48px;
      color: #24424c;
      margin-bottom: 20px;
    }

    .confirmation-message {
      font-size: 24px;
      color: #24424c;
      margin-bottom: 20px;
    }

    .order-id {
      font-size: 18px;
      color: #666;
      margin-bottom: 30px;
    }

    .continue-shopping {
      display: inline-block;
      padding: 10px 20px;
      background-color: #24424c;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      transition: background-color 0.3s;
    }

    .continue-shopping:hover {
      background-color: #1b323a;
    }

    /* Map Styles */
    #map {
      width: 100%;
      height: 300px; /* Increased height for better visibility */
      border-radius: 5px;
      margin-bottom: 20px;
      border: 1px solid #ddd; /* Added border for visibility */
    }

    .location-btn {
      display: inline-block;
      background-color: #24424c;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      margin-top: 5px;
    }

    .location-btn:hover {
      background-color: #1b323a;
    }

    .free-shipping-badge {
      background-color: #24424c;
      color: white;
      padding: 3px 8px;
      border-radius: 3px;
      font-size: 12px;
      margin-left: 10px;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Make sure Leaflet CSS is loaded before the JS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
</head>

<body>
  <header>
    <a href="index.html" class="logo">
      <i class="fas fa-brain"></i>
      <i class="fas fa-couch"></i>
      RoomGenius
    </a>
  </header>

  <div class="checkout-container" id="checkoutContainer">
    <div class="checkout-section">
      <h2 class="section-title">Order Summary</h2>
      <div id="orderSummary" class="order-summary">
        <!-- Cart items will be dynamically added here -->
        <div class="empty-cart-message" id="emptyCartMessage" style="display: none;">
          <p>Your cart is empty!</p>
          <a href="gallery.php" class="back-to-shop">Back to Shop</a>
        </div>
      </div>
    </div>

    <div class="checkout-section">
      <h2 class="section-title">Checkout Information</h2>
      
      <div class="form-section">
        <h3 class="form-section-title">Shipping Information</h3>
        <div class="form-group">
          <label for="fullName">Full Name</label>
          <input type="text" id="fullName" required>
        </div>
        
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" required>
        </div>
        
        <div class="form-group">
          <label for="address">Street Address</label>
          <input type="text" id="address" required>
        </div>
        
        <div class="row">
          <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" required>
          </div>
          <div class="form-group">
            <label for="zipCode">ZIP Code</label>
            <input type="text" id="zipCode" required>
          </div>
        </div>
        
        <div class="form-group">
          <label for="country">Governorate</label>
          <select id="country" required>
            <option value="">Select an option</option>
            <option value="us">Akkar</option>
            <option value="us">Minieh-Dnniyeh</option>
            <option value="ca">Zahle</option>
            <option value="uk">Zgharta</option>
            <option value="af">koura</option>
            <option value="al">bcharee</option>
            <option value="dz">Batroun</option>
            <option value="ar">Jbeil</option>
            <option value="au">Kesrouane</option>
            <option value="at">Baalbek</option>
            <option value="bh">Maten</option>
            <option value="bd">Baabda</option>
            <option value="be">Aalay</option>
            <option value="br">Chouf</option>
            <option value="cn">Jezzine</option>
            <option value="co">Saida</option>
            <option value="eg">Nabatiyeh</option>
            <option value="fr">Tyre</option>
            <option value="de">Marjaayoun</option>
            <option value="gr">Bint Jbeil</option>
            <option value="hk">Hermel</option>
            <option value="in">Tripoli</option>
            <option value="id">Beirut</option>
            <option value="ir">West Bekaa</option>
            <option value="iq">Hasbaiyya</option>
            <option value="ie">Rachaiyya</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" required>
        </div>

        <div class="form-group">
          <label>Your Location</label>
          <div id="map"></div>
          <button type="button" id="getLocationBtn" class="location-btn">
            <i class="fas fa-location-arrow"></i> Share My Location
          </button>
          <p id="locationStatus" style="margin-top: 5px; font-size: 14px; color: #666;">Click on the map to select your delivery location or use the button above to detect your current location.</p>
        </div>
      </div>
      
      <div class="form-section">
        <h3 class="form-section-title">Payment Information</h3>
        
        <div class="payment-icons">
          <div class="payment-icon">Visa</div>
          <div class="payment-icon">MC</div>
          <div class="payment-icon">Amex</div>
          <div class="payment-icon">PayPal</div>
          <div class="payment-icon">COD</div>
        </div>
        
        <div class="payment-methods">
          <div class="payment-method selected" data-method="card">
            <input type="radio" name="payment" id="cardPayment" checked>
            <label for="cardPayment" class="payment-method-label">Credit/Debit Card</label>
          </div>
          
          <div class="payment-method" data-method="cod">
            <input type="radio" name="payment" id="codPayment">
            <label for="codPayment" class="payment-method-label">Cash on Delivery</label>
          </div>
        </div>
        
        <div id="cardDetails" class="card-details">
          <div class="form-group">
            <label for="cardName">Name on Card</label>
            <input type="text" id="cardName" required>
          </div>
          
          <div class="form-group">
            <label for="cardNumber">Card Number</label>
            <input type="text" id="cardNumber" maxlength="19" required>
          </div>
          
          <div class="row">
            <div class="form-group">
              <label for="expiry">Expiration Date (MM/YY)</label>
              <input type="text" id="expiry" placeholder="MM/YY" maxlength="5" required>
            </div>
            <div class="form-group">
              <label for="cvv">Security Code (CVV)</label>
              <input type="text" id="cvv" maxlength="4" required>
            </div>
          </div>
        </div>
      </div>
      
      <button type="button" id="placeOrderBtn" class="place-order-btn">Place Order</button>
    </div>
  </div>

  <!-- Order Confirmation -->
  <div class="order-confirmation" id="orderConfirmation">
    <div class="confirmation-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    <h2 class="confirmation-message">Thank you for your order!</h2>
    <p class="order-id">Order #<span id="orderId"></span></p>
    <p>We've sent a confirmation email with your order details.</p>
    <p>Your RoomGenius selection will be delivered soon!</p>
    <a href="gallery.php" class="continue-shopping">Continue Shopping</a>
  </div>

  <!-- Always load Leaflet JS after the HTML elements are defined, especially the map div -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
  
  <script>
    // Initialize map (will be called after the document loads)
    let map, marker;
    
    function initMap() {
      // Default location (center of map) - Lebanon coordinates
      const defaultLocation = [33.8547, 35.8623]; // Beirut, Lebanon [lat, lng]
      
      // Check if the map div exists
      const mapDiv = document.getElementById('map');
      if (!mapDiv) {
        console.error("Map container not found!");
        return;
      }
      
      try {
        // Create map in the "map" div with increased zoom level for better visibility
        map = L.map('map').setView(defaultLocation, 13);
        
        // Use Carto tiles which have English labels
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
          subdomains: 'abcd',
          maxZoom: 19
        }).addTo(map);
        
        // Add a marker at the default location
        marker = L.marker(defaultLocation, {
          draggable: true
        }).addTo(map);
        
        // Update location when marker is dragged
        marker.on('dragend', function() {
          const position = marker.getLatLng();
          document.getElementById("locationStatus").textContent = `Selected: ${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}`;
        });
        
        // Allow clicking on map to move marker
        map.on('click', function(e) {
          marker.setLatLng(e.latlng);
          document.getElementById("locationStatus").textContent = `Selected: ${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}`;
        });
        
        // Force map to refresh by triggering a resize event
        setTimeout(function() {
          map.invalidateSize();
        }, 100);
        
        console.log("Map initialization complete!");
      } catch (error) {
        console.error("Error initializing map:", error);
      }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize map when document is loaded
      try {
        initMap();
        console.log("DOMContentLoaded event fired, map initialization called");
      } catch (e) {
        console.error("Error during map initialization:", e);
      }



      
      // Load cart from localStorage
      let cart = [];
      const savedCart = localStorage.getItem('roomGeniusCart');
      if (savedCart) {
        try {
          cart = JSON.parse(savedCart);
        } catch (e) {
          console.error("Error parsing cart from localStorage:", e);
        }
      }
      
      const orderSummary = document.getElementById('orderSummary');
      const emptyCartMessage = document.getElementById('emptyCartMessage');
      const checkoutContainer = document.getElementById('checkoutContainer');
      const orderConfirmation = document.getElementById('orderConfirmation');
      const placeOrderBtn = document.getElementById('placeOrderBtn');
      
      // Payment method selection
      const paymentMethods = document.querySelectorAll('.payment-method');
      const cardDetails = document.getElementById('cardDetails');
      
      paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
          // Update radio button
          this.querySelector('input[type="radio"]').checked = true;
          
          // Update selected class
          paymentMethods.forEach(m => m.classList.remove('selected'));
          this.classList.add('selected');
          
          // Show/hide card details based on selection
          if (this.dataset.method === 'card') {
            cardDetails.style.display = 'block';
          } else {
            cardDetails.style.display = 'none';
          }
        });
      });
      
      // Get location button
      const getLocationBtn = document.getElementById('getLocationBtn');
      const locationStatus = document.getElementById('locationStatus');
      
      getLocationBtn.addEventListener('click', function() {
        if (navigator.geolocation) {
          locationStatus.textContent = "Getting your location...";
          
          navigator.geolocation.getCurrentPosition(
            function(position) {
              const lat = position.coords.latitude;
              const lng = position.coords.longitude;
              const newLatLng = [lat, lng];
              
              map.setView(newLatLng, 15);
              marker.setLatLng(newLatLng);
              locationStatus.textContent = `Selected: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            },
            function(error) {
              console.error("Geolocation error:", error);
              locationStatus.textContent = "Error: Could not get your location. Please select manually on the map.";
            },
            {
              enableHighAccuracy: true,
              timeout: 5000,
              maximumAge: 0
            }
          );
        } else {
          locationStatus.textContent = "Error: Geolocation is not supported by your browser.";
        }
      });
      
      // If cart is empty, show message
      if (cart.length === 0) {
        emptyCartMessage.style.display = 'block';
        placeOrderBtn.disabled = true;
      } else {
        // Display cart items
        let subtotal = 0;
        let totalItems = 0;
        
        cart.forEach(item => {
          const itemTotal = item.price * item.quantity;
          subtotal += itemTotal;
          totalItems += item.quantity;
          
          const cartItemElement = document.createElement('div');
          cartItemElement.className = 'cart-item';
          cartItemElement.innerHTML = `
            <div class="item-image">
              <img src="${item.img}" alt="${item.name}">
            </div>
            <div class="item-details">
              <div class="item-name">${item.name}</div>
              <div class="item-quantity">Quantity: ${item.quantity}</div>
            </div>
            <div class="item-price">$${itemTotal.toFixed(2)}</div>
          `;
          
          orderSummary.appendChild(cartItemElement);
        });
        
        // Add price breakdown
        const shipping = 0.00; // Free shipping
        const tax = subtotal * 0.08; // 8% tax rate
        const total = subtotal + shipping + tax;
        
        const priceDetailsElement = document.createElement('div');
        priceDetailsElement.className = 'price-details';
        priceDetailsElement.innerHTML = `
          <div class="price-row">
            <div>Subtotal (${totalItems} items)</div>
            <div>$${subtotal.toFixed(2)}</div>
          </div>
          <div class="price-row">
            <div>Shipping<span class="free-shipping-badge">FREE</span></div>
            <div>$${shipping.toFixed(2)}</div>
          </div>
          <div class="price-row">
            <div>Tax (8%)</div>
            <div>$${tax.toFixed(2)}</div>
          </div>
          <div class="price-row total">
            <div>Total</div>
            <div>$${total.toFixed(2)}</div>
          </div>
        `;
        
        orderSummary.appendChild(priceDetailsElement);
      }
      
      // Card number formatting
      const cardNumberInput = document.getElementById('cardNumber');
      cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
        let formattedValue = '';
        
        for (let i = 0; i < value.length; i++) {
          if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
          }
          formattedValue += value[i];
        }
        
        e.target.value = formattedValue;
      });
      
      // Expiry date formatting
      const expiryInput = document.getElementById('expiry');
      expiryInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
        
        if (value.length > 2) {
          value = value.substring(0, 2) + '/' + value.substring(2);
        }
        
        e.target.value = value;
      });
      
      // Place order button click handler
      placeOrderBtn.addEventListener('click', function() {
        const fullName = document.getElementById('fullName').value;
        const email = document.getElementById('email').value;
        const address = document.getElementById('address').value;
        const city = document.getElementById('city').value;
        const zipCode = document.getElementById('zipCode').value;
        const country = document.getElementById('country').value;
        const phone = document.getElementById('phone').value;
        
        // Check if card payment is selected
        const isCardPayment = document.getElementById('cardPayment').checked;
        
        // Basic validation for all fields
        if (!fullName || !email || !address || !city || !zipCode || !country || !phone) {
          alert('Please fill in all required shipping information fields.');
          return;
        }
        
        // Validate card details only if card payment is selected
        if (isCardPayment) {
          const cardName = document.getElementById('cardName').value;
          const cardNumber = document.getElementById('cardNumber').value;
          const expiry = document.getElementById('expiry').value;
          const cvv = document.getElementById('cvv').value;
          
          if (!cardName || !cardNumber || !expiry || !cvv) {
            alert('Please fill in all card details.');
            return;
          }
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
          alert('Please enter a valid email address.');
          return;
        }
        
        // Check if location is selected
        const locationStatus = document.getElementById('locationStatus').textContent;
        if (!locationStatus.includes('Selected:')) {
          alert('Please share or select your location on the map.');
          return;
        }
        
        // Generate random order ID
        const orderId = 'RG' + Math.floor(100000 + Math.random() * 900000);
        document.getElementById('orderId').textContent = orderId;
        
        // Clear the cart in localStorage
        localStorage.removeItem('roomGeniusCart');
        
        // Show order confirmation
        checkoutContainer.style.display = 'none';
        orderConfirmation.style.display = 'block';
      });
      
      // Force map to render correctly by triggering window resize
      window.addEventListener('load', function() {
        setTimeout(function() {
          if (map) {
            map.invalidateSize();
          }
        }, 500);
      });
    });
  </script>
</body>

</html>