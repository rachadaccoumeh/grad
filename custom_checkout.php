<?php
/**
 * custom_checkout.php - Handles checkout process for custom product requests
 * 
 * This file provides a checkout interface for custom product requests,
 * allowing users to enter shipping and payment information.
 */

session_start();
require_once 'db_connect.php';

// Check if we have a custom request in the session
if (!isset($_SESSION['custom_request_id']) && !isset($_GET['request_id'])) {
    // Redirect to customize page if no custom request is found
    header('Location: customize.php');
    exit;
}

// Get the custom request ID from either session or GET parameter
$custom_request_id = $_SESSION['custom_request_id'] ?? $_GET['request_id'] ?? null;

// Fetch the custom request details from the database
$custom_request = null;
if ($custom_request_id) {
    // Add detailed query to debug the issue
    $stmt = $conn->prepare("SELECT * FROM custom_requests WHERE id = ?");
    $stmt->bind_param('i', $custom_request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $custom_request = $result->fetch_assoc();
        
        // Convert estimated_price to float to ensure proper numeric handling
        if (isset($custom_request['estimated_price'])) {
            $custom_request['estimated_price'] = (float)$custom_request['estimated_price'];
            
            // If price is still 0, write a debugging note
            if ($custom_request['estimated_price'] == 0) {
                error_log("Warning: estimated_price is 0 for custom request ID: {$custom_request_id}");
            }
        } else {
            error_log("Error: estimated_price field not found in custom request ID: {$custom_request_id}");
        }
    } else {
        // Redirect to customize page if custom request is not found
        header('Location: customize.php');
        exit;
    }
}

// Process form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $fullName = $_POST['fullName'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $zipCode = $_POST['zipCode'] ?? '';
        $governorate = $_POST['governorate'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $latitude = $_POST['latitude'] ?? 0;
        $longitude = $_POST['longitude'] ?? 0;
        $paymentMethod = $_POST['paymentMethod'] ?? '';
        
        // Card details if payment method is credit card
        $cardName = '';
        $cardNumber = '';
        $cardExpiry = '';
        
        if ($paymentMethod === 'credit_card') {
            $cardName = $_POST['cardName'] ?? '';
            $cardNumber = $_POST['cardNumber'] ?? '';
            $cardExpiry = $_POST['expiry'] ?? '';
        }
        
        // Generate a unique order ID (shortened to fit the database column)
        // Using 'CU' prefix instead of 'CUST' and limiting the random number to 2 digits
        $orderId = 'CU' . date('YmdHis') . rand(10, 99);
        
        // Update the custom request with checkout information
        $stmt = $conn->prepare("
            UPDATE custom_requests SET 
                order_id = ?,
                address = ?,
                city = ?,
                zip_code = ?,
                governorate = ?,
                phone = ?,
                latitude = ?,
                longitude = ?,
                payment_method = ?,
                card_name = ?,
                card_number = ?,
                card_expiry = ?,
                payment_status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $paymentStatus = 'pending'; // Default payment status
        
        $stmt->bind_param(
            'ssssssddsssssi',
            $orderId,
            $address,
            $city,
            $zipCode,
            $governorate,
            $phone,
            $latitude,
            $longitude,
            $paymentMethod,
            $cardName,
            $cardNumber,
            $cardExpiry,
            $paymentStatus,
            $custom_request_id
        );
        
        if ($stmt->execute()) {
            // Clear the session variable
            unset($_SESSION['custom_request_id']);
            
            // Set success message
            $success_message = "Your custom order has been placed successfully! Order ID: " . $orderId;
        } else {
            throw new Exception("Error processing your order. Please try again later.");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get user information if logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Custom Order Checkout - RoomGenius</title>
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

    /* Custom Request Summary */
    .custom-request-summary {
      margin-bottom: 30px;
    }

    .custom-item {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 15px;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(36, 66, 76, 0.1);
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

    .item-description {
      font-size: 14px;
      color: #666;
      margin-bottom: 5px;
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
      margin-bottom: 8px;
      font-weight: bold;
      color: #24424c;
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
    }

    /* Payment Methods */
    .payment-methods {
      margin-top: 20px;
    }

    .payment-method {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .payment-method:hover {
      border-color: #24424c;
    }

    .payment-method.active {
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
    }

    .place-order-btn:hover {
      background-color: #1b323a;
    }

    /* Order Confirmation */
    .order-confirmation {
      display: none;
      max-width: 800px;
      margin: 50px auto;
      padding: 30px;
      background-color: #fff8e3;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .order-confirmation-icon {
      font-size: 60px;
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

    /* Map */
    #map {
      height: 300px;
      margin-top: 15px;
      border-radius: 5px;
      z-index: 1;
    }

    .location-btn {
      background-color: #24424c;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 10px;
      transition: background-color 0.3s;
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
    
    /* Success and Error Messages */
    .notification {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      font-weight: bold;
    }
    
    .notification.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .notification.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
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

  <?php if ($success_message): ?>
  <div class="order-confirmation" style="display: block;">
    <div class="order-confirmation-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    <div class="order-confirmation-content">
      <h2 class="confirmation-message">Thank You for Your Custom Order!</h2>
      <p class="order-id">Order ID: <?php echo htmlspecialchars($orderId); ?></p>
      <p>We've received your custom order request and will begin processing it right away.</p>
      <p>You will receive an email confirmation shortly.</p>
      <a href="gallery.php" class="continue-shopping">Continue Shopping</a>
    </div>
  </div>
  <?php else: ?>
  
  <div class="checkout-container" id="checkoutContainer">
    <div class="checkout-section">
      <h2 class="section-title">Custom Order Summary</h2>
      <div class="custom-request-summary">
        <?php if ($custom_request): ?>
        <div class="custom-item">
          <div class="item-details">
            <div class="item-name">Custom <?php echo htmlspecialchars($custom_request['product_type']); ?></div>
            <div class="item-description">
              <?php 
                // Create a description from the custom request details
                $description = htmlspecialchars($custom_request['style']) . ' style, ' . 
                              htmlspecialchars($custom_request['material']);
                
                // Add wood or fabric type if available
                if (!empty($custom_request['wood_type'])) {
                  $description .= ' (' . htmlspecialchars($custom_request['wood_type']) . ' wood)';
                } elseif (!empty($custom_request['fabric_type'])) {
                  $description .= ' (' . htmlspecialchars($custom_request['fabric_type']) . ' fabric)';
                }
                
                // Add color and finish
                $description .= ', ' . htmlspecialchars($custom_request['color']) . ' color, ' . 
                               htmlspecialchars($custom_request['finish_type']) . ' finish';
                
                // Add dimensions
                if (!empty($custom_request['dimensions'])) {
                  $description .= ', ' . htmlspecialchars($custom_request['dimensions']);
                }
                
                echo $description;
              ?>
            </div>
            <?php if (!empty($custom_request['special_requests'])): ?>
            <div class="item-description">
              <strong>Special requests:</strong> <?php echo htmlspecialchars($custom_request['special_requests']); ?>
            </div>
            <?php endif; ?>
          </div>
          <div class="item-price">$<?php echo number_format((float)$custom_request['estimated_price'], 2); ?></div>
        </div>
        
        <div class="price-details">
          <div class="price-row">
            <span>Subtotal</span>
            <span>$<?php echo number_format((float)$custom_request['estimated_price'], 2); ?></span>
          </div>
          <div class="price-row">
            <span>Shipping</span>
            <span class="free-shipping">
              <i class="fas fa-truck"></i> Free Shipping
            </span>
          </div>
          <div class="price-row">
            <span>Tax (5%)</span>
            <span>$<?php echo number_format((float)$custom_request['estimated_price'] * 0.05, 2); ?></span>
          </div>
          <div class="price-row total">
            <span>Total</span>
            <span>$<?php echo number_format((float)$custom_request['estimated_price'] * 1.05, 2); ?></span>
          </div>
        </div>
        <?php else: ?>
        <div class="empty-cart-message">
          <p>No custom order found!</p>
          <a href="customize.php" class="back-to-shop">Back to Customization</a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="checkout-section">
      <h2 class="section-title">Checkout Information</h2>
      
      <?php if ($error_message): ?>
      <div class="notification error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php endif; ?>
      
      <form id="checkoutForm" method="POST" action="">
        <div class="form-section">
          <h3 class="form-section-title">Shipping Information</h3>
          <div class="form-group">
            <label for="fullName">Full Name</label>
            <input type="text" id="fullName" name="fullName" required value="<?php echo isset($user) ? htmlspecialchars($user['name']) : ''; ?>">
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($user) ? htmlspecialchars($user['email']) : ''; ?>">
          </div>
          <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" required>
          </div>
          <div class="row">
            <div class="form-group">
              <label for="city">City</label>
              <input type="text" id="city" name="city" required>
            </div>
            <div class="form-group">
              <label for="zipCode">ZIP Code</label>
              <input type="text" id="zipCode" name="zipCode" required>
            </div>
          </div>
          <div class="form-group">
            <label for="governorate">Governorate/State</label>
            <input type="text" id="governorate" name="governorate" required>
          </div>
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" required>
          </div>
          
          <div class="form-group">
            <label for="location">Delivery Location</label>
            <div id="map"></div>
            <button type="button" id="getCurrentLocation" class="location-btn">
              <i class="fas fa-map-marker-alt"></i> Use My Current Location
            </button>
            <input type="hidden" id="latitude" name="latitude" value="33.8547">
            <input type="hidden" id="longitude" name="longitude" value="35.8623">
          </div>
        </div>
        
        <div class="form-section">
          <h3 class="form-section-title">Payment Method</h3>
          <div class="payment-methods">
            <div class="payment-method active" data-method="cash_on_delivery">
              <input type="radio" id="cashOnDelivery" name="paymentMethod" value="cash_on_delivery" checked>
              <label for="cashOnDelivery" class="payment-method-label">Cash on Delivery</label>
              <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="payment-method" data-method="credit_card">
              <input type="radio" id="creditCard" name="paymentMethod" value="credit_card">
              <label for="creditCard" class="payment-method-label">Credit Card</label>
              <i class="fas fa-credit-card"></i>
            </div>
            
            <div class="card-details" style="display: none;">
              <div class="form-group">
                <label for="cardName">Name on Card</label>
                <input type="text" id="cardName" name="cardName">
              </div>
              <div class="form-group">
                <label for="cardNumber">Card Number</label>
                <input type="text" id="cardNumber" name="cardNumber" placeholder="XXXX XXXX XXXX XXXX">
              </div>
              <div class="row">
                <div class="form-group">
                  <label for="expiry">Expiry Date</label>
                  <input type="text" id="expiry" name="expiry" placeholder="MM/YY">
                </div>
                <div class="form-group">
                  <label for="cvv">CVV</label>
                  <input type="text" id="cvv" name="cvv" placeholder="XXX">
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <button type="submit" class="place-order-btn">Place Custom Order</button>
      </form>
    </div>
  </div>
  
  <?php endif; ?>

  <!-- Leaflet JS for map functionality -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize map
      let map = null;
      let marker = null;
      const defaultLat = 33.8547;
      const defaultLng = 35.8623;
      
      // Initialize the map if the map container exists
      const mapContainer = document.getElementById('map');
      if (mapContainer) {
        map = L.map('map').setView([defaultLat, defaultLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add initial marker
        marker = L.marker([defaultLat, defaultLng], {
          draggable: true
        }).addTo(map);
        
        // Update coordinates when marker is dragged
        marker.on('dragend', function(e) {
          const position = marker.getLatLng();
          document.getElementById('latitude').value = position.lat;
          document.getElementById('longitude').value = position.lng;
        });
        
        // Update marker when clicking on map
        map.on('click', function(e) {
          marker.setLatLng(e.latlng);
          document.getElementById('latitude').value = e.latlng.lat;
          document.getElementById('longitude').value = e.latlng.lng;
        });
        
        // Get current location button
        const getCurrentLocationBtn = document.getElementById('getCurrentLocation');
        if (getCurrentLocationBtn) {
          getCurrentLocationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
              navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                map.setView([lat, lng], 15);
                marker.setLatLng([lat, lng]);
                
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
              }, function(error) {
                console.error('Error getting location:', error);
                alert('Could not get your location. Please select it manually on the map.');
              });
            } else {
              alert('Geolocation is not supported by your browser. Please select your location manually on the map.');
            }
          });
        }
      }
      
      // Payment method selection
      const paymentMethods = document.querySelectorAll('.payment-method');
      const cardDetails = document.querySelector('.card-details');
      
      paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
          // Remove active class from all methods
          paymentMethods.forEach(m => m.classList.remove('active'));
          
          // Add active class to clicked method
          this.classList.add('active');
          
          // Check the radio button
          const radio = this.querySelector('input[type="radio"]');
          radio.checked = true;
          
          // Show/hide card details based on selected method
          if (this.dataset.method === 'credit_card') {
            cardDetails.style.display = 'block';
          } else {
            cardDetails.style.display = 'none';
          }
        });
      });
      
      // Form validation
      const checkoutForm = document.getElementById('checkoutForm');
      if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
          const fullName = document.getElementById('fullName').value;
          const email = document.getElementById('email').value;
          const address = document.getElementById('address').value;
          const city = document.getElementById('city').value;
          const zipCode = document.getElementById('zipCode').value;
          const phone = document.getElementById('phone').value;
          
          if (!fullName || !email || !address || !city || !zipCode || !phone) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return;
          }
          
          // Validate credit card details if credit card payment is selected
          const isCreditCardSelected = document.querySelector('input[name="paymentMethod"]:checked').value === 'credit_card';
          
          if (isCreditCardSelected) {
            const cardName = document.getElementById('cardName').value;
            const cardNumber = document.getElementById('cardNumber').value;
            const expiry = document.getElementById('expiry').value;
            const cvv = document.getElementById('cvv').value;
            
            if (!cardName || !cardNumber || !expiry || !cvv) {
              e.preventDefault();
              alert('Please fill in all credit card details.');
              return;
            }
            
            // Basic card number validation
            if (!/^\d{16}$/.test(cardNumber.replace(/\s/g, ''))) {
              e.preventDefault();
              alert('Please enter a valid 16-digit card number.');
              return;
            }
            
            // Basic expiry date validation (MM/YY format)
            if (!/^\d{2}\/\d{2}$/.test(expiry)) {
              e.preventDefault();
              alert('Please enter a valid expiry date in MM/YY format.');
              return;
            }
            
            // Basic CVV validation (3 or 4 digits)
            if (!/^\d{3,4}$/.test(cvv)) {
              e.preventDefault();
              alert('Please enter a valid CVV (3 or 4 digits).');
              return;
            }
          }
        });
      }
      
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
