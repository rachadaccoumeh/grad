<?php
session_start();
require_once 'db_connect.php';

$success_message = '';
$error_message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $product_type = $_POST['product_type'] ?? '';
        $style = $_POST['style'] ?? '';
        $material = $_POST['material'] ?? '';
        $wood_type = $_POST['wood_type'] ?? null;
        $fabric_type = $_POST['fabric_type'] ?? null;
        $color = $_POST['color'] ?? '';
        $finish_type = $_POST['finish_type'] ?? '';
        $dimensions = $_POST['dimensions'] ?? '';
        $add_ons = isset($_POST['add_ons']) ? json_encode($_POST['add_ons']) : null;
        $special_requests = $_POST['special_requests'] ?? null;
        // Cast to float to ensure proper numeric handling
        $budget = (float)($_POST['budget'] ?? 0);
        $estimated_price = (float)($_POST['estimated_price'] ?? 0);
        
        // Get user ID if logged in
        $user_id = $_SESSION['user_id'] ?? null;
        
        // Insert into database with updated fields for checkout step
        $stmt = $conn->prepare("INSERT INTO custom_requests 
            (user_id, product_type, style, material, wood_type, fabric_type, color, 
             finish_type, dimensions, add_ons, special_requests, budget, estimated_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
        $stmt->bind_param(
            'issssssssssdd', 
            $user_id, 
            $product_type, 
            $style, 
            $material, 
            $wood_type, 
            $fabric_type, 
            $color, 
            $finish_type, 
            $dimensions, 
            $add_ons, 
            $special_requests, 
            $budget, 
            $estimated_price
        );
        
        if ($stmt->execute()) {
            // Get the ID of the inserted custom request
            $custom_request_id = $conn->insert_id;
            
            // Store the custom request ID in session for checkout
            $_SESSION['custom_request_id'] = $custom_request_id;
            
            // Redirect to the checkout page for the custom request
            header('Location: custom_checkout.php');
            exit;
        } else {
            throw new Exception("Error submitting your request. Please try again later.");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
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
        <button class="ai-button" onclick="window.location.href='gallery.php'">
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
    <!-- Direct form submission to process_custom_request.php with multipart/form-data for file uploads -->
    <!-- Form moved to wrap both preview and options sections to ensure all elements are included in submission -->
    <!-- Form submits to itself first to save request data then redirects to checkout -->
    <form id="customizationForm" method="POST" action="" enctype="multipart/form-data">
    
      <div class="preview-section">
        <h2 class="section-heading">Product Preview</h2>
        
        <!-- Integrated design with side-by-side layout for upload and preview -->
        <div class="preview-upload-container" style="display: flex; gap: 20px; margin-bottom: 30px;">
          <!-- Left side: Upload controls -->
          <div class="upload-controls" style="flex: 1; background: #f5f5f5; border-radius: 8px; padding: 15px; border: 1px solid #ddd;">
            <h3 style="margin-top: 0; color: #333; font-size: 1.2em;">Upload Design Image</h3>
            <p style="margin-bottom: 15px; color: #666;">Select an image of your desired product design</p>
            
            <!-- File input with improved styling -->
            <label for="imageUpload" class="upload-button" style="display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; border-radius: 4px; cursor: pointer; margin-bottom: 15px;">
              <i class="fas fa-upload"></i> Choose Image
            </label>
            <input type="file" id="imageUpload" name="product_image" accept="image/*" style="display:none;">
            
            <!-- Hidden fields for file upload tracking (preserved for functionality) -->
            <input type="hidden" id="imageUploaded" name="image_uploaded" value="0">
            <input type="hidden" name="has_file_input" value="yes">
            
            <!-- Upload status with better styling -->
            <div id="uploadStatus" style="font-size: 0.9em; color: #666; margin-bottom: 10px;">No file selected</div>
            
            <!-- Selected file information with better styling -->
            <div id="selectedFileInfo" style="padding: 10px; background: #f8f8f8; border-radius: 4px; display: none; margin-top: 10px;">
              <strong>Selected file:</strong> <span id="selectedFileName">None</span>
            </div>
          </div>
          
          <!-- Right side: Image preview -->
          <div class="product-preview" id="productPreview" style="flex: 1.5; min-height: 250px; display: flex; align-items: center; justify-content: center; background: #f9f9f9; border-radius: 8px; border: 1px dashed #ccc;">
            <div class="upload-container" style="text-align: center;">
              <i class="fas fa-cloud-upload-alt upload-icon" style="font-size: 48px; color: #999; margin-bottom: 15px;"></i>
              <div class="upload-text">Image preview will appear here</div>
            </div>
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
        
        <!-- File upload logic is now integrated with the product preview section -->
        <div class="form-group">
          <label for="productType">Product Type</label>
          <select class="form-control" id="productType" name="product_type" required>
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
          <select class="form-control" id="style" name="style" required>
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
          <select class="form-control" id="material" name="material" required>
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
          <select class="form-control" id="woodType" name="wood_type">
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
          <select class="form-control" id="fabricType" name="fabric_type">
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
          <input type="hidden" id="selectedColor" name="color" value="" required>
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
          <select class="form-control" id="finishType" name="finish_type" required>
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
            <input type="number" class="form-control" id="width" name="width" placeholder="Width (cm)" min="1" data-price-per-unit="2" required>
            <input type="number" class="form-control" id="depth" name="depth" placeholder="Depth (cm)" min="1" data-price-per-unit="2" required>
            <input type="number" class="form-control" id="height" name="height" placeholder="Height (cm)" min="1" data-price-per-unit="2" required>
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
          <label for="specialRequests">Special Requests (Optional)</label>
          <textarea class="form-control" id="specialRequests" name="special_requests" rows="4" placeholder="Any special instructions or details about your custom product..."></textarea>
        </div>
        
        <!-- Hidden fields for estimated price -->
        <input type="hidden" id="estimated_price" name="estimated_price" value="0">
        <!-- Make sure this field is properly submitted with the form -->
        <input type="hidden" id="budget" name="budget" value="0">
        
        <button type="submit" class="order-button" id="submitCustomization">Submit Request</button>
      </div>
    </form>
  </div>

  <?php if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        ?>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <!-- <button type="submit" class="order-button" id="submitCustomization">
          Place Custom Order
        </button> -->
      </form>
    </div>
  </div>

  <?php if ($success_message): ?>
    <div class="notification success"><?php echo htmlspecialchars($success_message); ?></div>
  <?php endif; ?>
  <?php if ($error_message): ?>
    <div class="notification error"><?php echo htmlspecialchars($error_message); ?></div>
  <?php endif; ?>
  <div class="notification" id="notification"></div>
  <script>
    // Store the estimated price in a hidden field
    function updatePrice() {
      // ... existing updatePrice code ...
      
      // Update the hidden field with the estimated price
      document.getElementById('estimated_price').value = totalPrice;
      
      // ... rest of updatePrice function ...
    }
    
    // Initialize totalPrice variable
    let totalPrice = 0;
    
    // Function to calculate total price
    function calculateTotalPrice() {
        totalPrice = 0;
        
        // Get product type price
        const productType = document.getElementById('productType');
        const productPrice = parseFloat(productType.options[productType.selectedIndex].dataset.price) || 0;
        totalPrice += productPrice;
        
        // Add style price if selected
        const style = document.getElementById('style');
        if (style.value) {
            totalPrice += parseFloat(style.options[style.selectedIndex].dataset.price) || 0;
        }
        
        // Add material price if selected
        const material = document.getElementById('material');
        if (material.value) {
            totalPrice += parseFloat(material.options[material.selectedIndex].dataset.price) || 0;
        }
        
        // Add wood type price if selected and visible
        const woodType = document.getElementById('woodType');
        if (woodType && woodType.style.display !== 'none' && woodType.value) {
            totalPrice += parseFloat(woodType.options[woodType.selectedIndex].dataset.price) || 0;
        }
        
        // Add fabric type price if selected and visible
        const fabricType = document.getElementById('fabricType');
        if (fabricType && fabricType.style.display !== 'none' && fabricType.value) {
            totalPrice += parseFloat(fabricType.options[fabricType.selectedIndex].dataset.price) || 0;
        }
        
        // Add finish type price if selected
        const finishType = document.getElementById('finishType');
        if (finishType.value) {
            totalPrice += parseFloat(finishType.options[finishType.selectedIndex].dataset.price) || 0;
        }
        
        // Add color price if selected
        const selectedColor = document.querySelector('.color-option.selected');
        if (selectedColor) {
            totalPrice += parseFloat(selectedColor.dataset.price) || 0;
        }
        
        // Add add-ons prices
        document.querySelectorAll('.add-on-option.selected').forEach(addOn => {
            totalPrice += parseFloat(addOn.dataset.price) || 0;
        });
        
        // Update the displayed total price
        const totalPriceElement = document.getElementById('totalPrice');
        if (totalPriceElement) {
            totalPriceElement.textContent = '$' + totalPrice.toFixed(2);
        }
        
        return totalPrice;
    }
    
    // Recalculate total price when any relevant field changes
    const priceAffectingElements = [
        'productType', 'style', 'material', 'woodType', 'fabricType', 
        'finishType', 'colorSelect'
    ];
    
    priceAffectingElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', function() {
                // Calculate the total price
                const currentTotal = calculateTotalPrice();
                
                // Update the hidden field with the current total
                document.getElementById('estimated_price').value = currentTotal;
                
                // Log the value for debugging
                console.log('Updated estimated_price to: ' + currentTotal);
            });
        }
    });
    
    // Also recalculate when add-ons are toggled
    document.querySelectorAll('.add-on-option').forEach(option => {
        option.addEventListener('click', calculateTotalPrice);
    });
    
    // Initial calculation and set the hidden field value
    const initialPrice = calculateTotalPrice();
    document.getElementById('estimated_price').value = initialPrice;
    console.log('Initial estimated_price set to: ' + initialPrice);
    
    // Form submission handler via JavaScript (alternative to direct form submission)
    // This is commented out because we're using direct form submission to enable the checkout step
    /*
    document.getElementById('customizationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Recalculate total price before submission
        const currentTotal = calculateTotalPrice();
        
        // Show loading state
        const submitBtn = document.getElementById('submitCustomization');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        
        try {
            // Form validation with debug logging
            function validateForm() {
              console.log('Starting form validation...');
              let isValid = true;
              
              // Clear previous errors
              document.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('has-error');
                const errorMsg = group.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
              });
              
              // Check product type
              const productType = document.querySelector('[name="product_type"]');
              if (!productType || !productType.value) {
                console.log('Product type is required');
                isValid = false;
                showFieldError(productType, 'Product Type is required');
              }
              
              // Check style
              const style = document.querySelector('[name="style"]');
              if (!style || !style.value) {
                console.log('Style is required');
                isValid = false;
                showFieldError(style, 'Style is required');
              }
              
              // Check material
              const material = document.querySelector('[name="material"]');
              if (!material || !material.value) {
                console.log('Material is required');
                isValid = false;
                showFieldError(material, 'Material is required');
              }
              
              // Check finish type
              const finishType = document.querySelector('[name="finish_type"]');
              if (!finishType || !finishType.value) {
                console.log('Finish type is required');
                isValid = false;
                showFieldError(finishType, 'Finish type is required');
              }
              
              // Check dimensions (width, depth, height)
              const width = document.getElementById('width');
              const depth = document.getElementById('depth');
              const height = document.getElementById('height');
              const dimensionsGroup = width?.closest('.form-group');
              
              if (!width?.value || !depth?.value || !height?.value) {
                console.log('All dimensions are required');
                isValid = false;
                if (dimensionsGroup) {
                  dimensionsGroup.classList.add('has-error');
                  if (!dimensionsGroup.querySelector('.error-message')) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.textContent = 'All dimensions are required';
                    errorMsg.style.color = '#f44336';
                    errorMsg.style.marginTop = '5px';
                    errorMsg.style.fontSize = '0.9em';
                    dimensionsGroup.appendChild(errorMsg);
                  }
                }
              }
              
              // Check color selection
              const colorSelected = document.querySelector('.color-option.selected');
              const colorGroup = document.getElementById('colorOptions')?.closest('.form-group');
              const colorInput = document.getElementById('selectedColor');
              
              if (!colorSelected || !colorInput?.value) {
                console.log('Color selection is required');
                isValid = false;
                if (colorGroup) {
                  colorGroup.classList.add('has-error');
                  if (!colorGroup.querySelector('.error-message:not(.color-error)')) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message color-error';
                    errorMsg.textContent = 'Please select a color';
                    errorMsg.style.color = '#f44336';
                    errorMsg.style.marginTop = '5px';
                    errorMsg.style.fontSize = '0.9em';
                    colorGroup.appendChild(errorMsg);
                  }
                }
              } else if (colorGroup) {
                colorGroup.classList.remove('has-error');
                const errorMsg = colorGroup.querySelector('.error-message.color-error');
                if (errorMsg) errorMsg.remove();
              }
              
              console.log('Form validation result:', isValid);
              return isValid;
              
              // Helper function to show field errors
              function showFieldError(element, message) {
                if (!element) return;
                const formGroup = element.closest('.form-group');
                if (formGroup && !formGroup.querySelector('.error-message')) {
                  const errorMsg = document.createElement('div');
                  errorMsg.className = 'error-message';
                  errorMsg.textContent = message;
                  errorMsg.style.color = '#f44336';
                  errorMsg.style.marginTop = '5px';
                  errorMsg.style.fontSize = '0.9em';
                  formGroup.appendChild(errorMsg);
                  formGroup.classList.add('has-error');
                }
              }
            }
            
            if (!validateForm()) {
              throw new Error('Please fill in all required fields');
            }
            
            // Update hidden fields with current values
            document.getElementById('estimated_price').value = currentTotal;
            const budgetValue = document.getElementById('priceRange').value || '0';
            document.getElementById('budget').value = budgetValue;
            
            // Create FormData from the form
            const formData = new FormData(this);
            
            // Add selected color
            const selectedColor = document.querySelector('.color-option.selected');
            if (selectedColor) {
                formData.set('color', selectedColor.getAttribute('data-color'));
            } else {
                throw new Error('Please select a color');
            }
            
            // Add dimensions
            const width = document.getElementById('width').value;
            const depth = document.getElementById('depth').value;
            const height = document.getElementById('height').value;
            const dimensions = `${width}x${depth}x${height}`;
            formData.set('dimensions', dimensions);
            
            // Add selected add-ons with their prices
            const selectedAddOns = [];
            document.querySelectorAll('.add-on-option.active, .add-on-option.selected').forEach(addOn => {
                selectedAddOns.push({
                    name: addOn.getAttribute('data-addon'),
                    price: addOn.getAttribute('data-price')
                });
            });
            // Log selected add-ons for debugging
            console.log('Selected add-ons:', selectedAddOns);
            formData.set('add_ons', JSON.stringify(selectedAddOns));
            
            // Add image file if uploaded - with improved handling and null checks
            // This code safely checks for the existence of elements to prevent errors
            const imageInput = document.getElementById('imageUpload');
            const imageUploadedElement = document.getElementById('imageUploaded');
            const imageUploaded = imageUploadedElement ? imageUploadedElement.value === '1' : false;
            console.log('Image uploaded flag:', imageUploaded);
            
            // Check if there's a file selected
            if (imageInput && imageInput.files && imageInput.files.length > 0) {
                console.log('Files in input:', imageInput.files.length);
                
                const file = imageInput.files[0];
                console.log('Image file details:', {
                    name: file.name,
                    type: file.type,
                    size: file.size + ' bytes'
                });
                
                // Clear any existing file data with the same name to prevent conflicts
                if (formData.has('product_image')) {
                    formData.delete('product_image');
                }
                
                // Add the file to the form data - be explicit about filename
                formData.set('product_image', file, file.name);
                
                // Add a flag to indicate an image was uploaded - set this to 1 always when there's a file
                formData.set('image_uploaded', '1');
                
                console.log('Image file added to form data with name:', file.name);
                
                // Display confirmation for user
                showNotification('info', `Image "${file.name}" will be uploaded with your request`);
            } else {
                console.log('No image selected for upload');
                formData.set('image_uploaded', '0');
            }
            
            // Log all form data for debugging
            console.log('Form data entries:');
            for (let pair of formData.entries()) {
                // Don't log the actual file content, just the name
                if (pair[0] === 'product_image' && pair[1] instanceof File) {
                    console.log(pair[0], '(File)', pair[1].name, pair[1].type, pair[1].size + ' bytes');
                } else {
                    console.log(pair[0], pair[1]);
                }
            }
            
            // Submit the form with improved error handling
            console.log('Submitting form to process_custom_request.php...');
            
            // Make sure we're not sending any unnecessary content-type headers
            // as the browser will set the correct multipart/form-data boundary
            const response = await fetch('process_custom_request.php', {
                method: 'POST',
                body: formData,
                // Don't set Content-Type header - browser will set it automatically with the boundary
            }).catch(err => {
                console.error('Network error during form submission:', err);
                throw new Error('Network error: ' + err.message);
            });
            
            if (!response.ok) {
                console.error('Server error:', response.status, response.statusText);
                throw new Error(`Server error: ${response.status} ${response.statusText}`);
            }
            
            // Parse the JSON response
            let data;
            try {
                data = await response.json();
                console.log('Server response:', data);
            } catch (err) {
                console.error('Error parsing server response:', err);
                throw new Error('Error parsing server response. Please try again.');
            }
            
            // Create a debug panel to show server response details
            const debugPanel = document.createElement('div');
            debugPanel.style.position = 'fixed';
            debugPanel.style.top = '50px';
            debugPanel.style.left = '50%';
            debugPanel.style.transform = 'translateX(-50%)';
            debugPanel.style.backgroundColor = '#f8f9fa';
            debugPanel.style.border = '1px solid #ddd';
            debugPanel.style.borderRadius = '8px';
            debugPanel.style.padding = '20px';
            debugPanel.style.zIndex = '9999';
            debugPanel.style.maxWidth = '80%';
            debugPanel.style.maxHeight = '80vh';
            debugPanel.style.overflow = 'auto';
            debugPanel.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            
            // Format data for debugging display
            let debugHtml = '<h2>Form Submission Response</h2>';
            debugHtml += `<p>Status: <strong>${data.success ? 'SUCCESS' : 'FAILED'}</strong></p>`;
            debugHtml += `<p>Message: ${data.message}</p>`;
            
            // Show image upload details if available
            if (data.image_path) {
                debugHtml += `<p>Image Path: ${data.image_path}</p>`;
            } else {
                debugHtml += '<p><strong>Warning:</strong> No image path returned from server</p>';
            }
            
            // Show any additional debug info
            if (data.debug_info) {
                debugHtml += '<h3>Debug Information</h3>';
                debugHtml += '<ul>';
                for (const [key, value] of Object.entries(data.debug_info)) {
                    debugHtml += `<li><strong>${key}:</strong> ${value}</li>`;
                }
                debugHtml += '</ul>';
            }
            
            // Add close button
            debugHtml += `<div style="margin-top: 20px">
                <button class="debug-close" style="background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">Close</button>
                <button class="debug-continue" style="background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">${data.success ? 'Continue' : 'Try Again'}</button>
            </div>`;
            
            debugPanel.innerHTML = debugHtml;
            document.body.appendChild(debugPanel);
            
            // Add event listeners for the buttons
            document.querySelector('.debug-close').addEventListener('click', () => {
                document.body.removeChild(debugPanel);
            });
            
            document.querySelector('.debug-continue').addEventListener('click', () => {
                document.body.removeChild(debugPanel);
                if (data.success) {
                    // Reset form after successful submission
                    this.reset();
                    // Redirect to gallery page
                    window.location.href = 'gallery.php?success=request_submitted';
                }
            });
            
            if (data.success) {
                // Show a regular notification
                showNotification('success', data.message || 'Your custom request has been submitted successfully!');
            } else {
                throw new Error(data.message || 'Error submitting request');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', error.message || 'Error submitting your request. Please try again.');
        } finally {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });
    */
    
    // Enhanced form submit handler with file upload verification
    document.getElementById('customizationForm').addEventListener('submit', function(e) {
        console.log('Form submission started');
        
        // Show loading state
        const submitBtn = document.getElementById('submitCustomization');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        }
        
        // Validate that required fields are filled
        const requiredFields = ['product_type', 'style', 'material', 'finish_type', 'color'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            const element = document.querySelector(`[name="${field}"]`);
            if (element && !element.value) {
                isValid = false;
                // Add visual feedback
                const formGroup = element.closest('.form-group');
                if (formGroup) formGroup.classList.add('has-error');
            }
        });
        
        // Check dimensions as well
        const width = document.getElementById('width');
        const height = document.getElementById('height');
        const depth = document.getElementById('depth');
        
        if (!width?.value || !height?.value || !depth?.value) {
            isValid = false;
        }
        
        // Important file upload validation
        const fileInput = document.getElementById('imageUpload');
        const imageUploadedFlag = document.getElementById('imageUploaded');
        
        // Double check if there's a file selected but the flag wasn't set
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            console.log('File detected in form submission:', fileInput.files[0].name);
            // Make sure the hidden flag is set
            if (imageUploadedFlag) {
                imageUploadedFlag.value = '1';
                console.log('Image uploaded flag set to 1 during form submission');
            }
        } else {
            console.log('No file selected for upload during form submission');
        }
        
        if (!isValid) {
            e.preventDefault(); // Stop submission if validation fails
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Request';
            }
            alert('Please fill in all required fields before submitting');
            return false;
        }
        
        console.log('Form validation passed, submitting...');
        // The form will be submitted normally since we didn't prevent the default action
        return true;
    });
    
    // Add helper validation functions
    function validateField(field) {
        const element = document.getElementById(field);
        if (!element || !element.value) {
            const formGroup = element?.closest('.form-group');
            if (formGroup) {
                formGroup.classList.add('has-error');
                if (!formGroup.querySelector('.error-message')) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.textContent = field.charAt(0).toUpperCase() + field.slice(1) + ' is required';
                    errorMsg.style.color = '#f44336';
                    formGroup.appendChild(errorMsg);
                }
            }
            return false;
        }
        return true;
    }
    
    // Helper function to show notifications
    function showNotification(type, message) {
        // Remove any existing notifications
        const existingNotifications = document.querySelectorAll('.custom-notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create new notification
        const notification = document.createElement('div');
        notification.className = `custom-notification ${type}`;
        notification.textContent = message;
        
        // Style the notification
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.right = '20px';
        notification.style.padding = '15px 25px';
        notification.style.borderRadius = '5px';
        notification.style.color = 'white';
        notification.style.zIndex = '1000';
        notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(20px)';
        notification.style.transition = 'all 0.3s ease';
        
        if (type === 'success') {
            notification.style.backgroundColor = '#4CAF50';
        } else {
            notification.style.backgroundColor = '#f44336';
        }
        
        document.body.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 10);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(20px)';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
    
    // Modified script for customize.php
// Make sure all JavaScript runs after DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Add a submit handler to ensure price is set before submission
  document.getElementById('customizationForm').addEventListener('submit', function(e) {
    // Get the current calculated price
    const finalPrice = calculateTotalPrice();
    
    // Make sure the hidden field is updated with the final price
    document.getElementById('estimated_price').value = finalPrice;
    
    console.log('Form submitted with estimated_price: ' + finalPrice);
  });
  
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
  const selectedColorInput = document.getElementById('selectedColor');
  
  colorOptionElements.forEach(option => {
    option.addEventListener('click', function() {
      colorOptionElements.forEach(opt => opt.classList.remove('selected'));
      this.classList.add('selected');
      
      // Update the hidden input with the selected color data
      const colorName = this.getAttribute('data-color');
      const colorPrice = this.getAttribute('data-price');
      selectedColorInput.value = `${colorName} ($${colorPrice})`;
      
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

  /**
   * Enhanced file upload handler with improved user feedback and error handling
   * This code manages file selection, validation, and preview generation.
   * It also sets the appropriate flags for server-side processing.
   * 
   * @author RoomGenius Development Team
   * @version 2.0
   */
  // Handle file uploads with the new integrated design
  imageUpload.addEventListener('change', function(e) {
    // Get the selected file
    const file = this.files[0];
    
    // Check if a file was selected
    if (file) {
      // Get UI elements
      const uploadStatus = document.getElementById('uploadStatus');
      const selectedFileInfo = document.getElementById('selectedFileInfo');
      const selectedFileName = document.getElementById('selectedFileName');
      const productPreview = document.getElementById('productPreview');
      
      // Update initial display
      if (uploadStatus) uploadStatus.textContent = 'File selected: ' + file.name;
      if (selectedFileName) selectedFileName.textContent = `${file.name} (${Math.round(file.size/1024)} KB)`;
      if (selectedFileInfo) selectedFileInfo.style.display = 'block';
      
      // Validate file type (only allow images)
      const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
      if (!validImageTypes.includes(file.type)) {
        uploadStatus.innerHTML = 'Error: Please select a valid image file (JPEG, PNG, GIF).';
        uploadStatus.style.color = '#dc3545'; // Red color for error
        return; // Stop processing
      }
      
      // Check file size (limit to 5MB)
      const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB in bytes
      if (file.size > MAX_FILE_SIZE) {
        uploadStatus.innerHTML = 'Error: File size exceeds 5MB limit.';
        uploadStatus.style.color = '#dc3545';
        return;
      }
      
      // If all validations pass, update status
      uploadStatus.innerHTML = 'File selected: ' + file.name;
      uploadStatus.style.color = '#28a745'; // Green color for success
      
      // Create a FileReader to read and display the image
      const reader = new FileReader();
      
      // Set up the onload handler to update the preview once the file is read
      reader.onload = function(e) {
        // Update the preview image
        if (productPreview) {
          productPreview.innerHTML = `<img src="${e.target.result}" alt="Product Preview" class="preview-image" style="max-width: 100%; max-height: 250px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">`;
        }
        
        // Set the uploaded image flag
        const imageUploadedElement = document.getElementById('imageUploaded');
        if (imageUploadedElement) {
          imageUploadedElement.value = '1';
        }
        
        // Store the URL for cart functionality
        uploadedImageUrl = e.target.result;
        console.log('Image preview created successfully');
      };
      
      // Handle errors in file reading
      reader.onerror = function() {
        console.error('FileReader error:', reader.error);
        uploadStatus.innerHTML = 'Error reading file. Please try again.';
        uploadStatus.style.color = '#dc3545';
      };
      reader.readAsDataURL(file);
    } else {
      // Handle case when no file is selected or selection is cancelled
      
      // Update upload status
      if (uploadStatus) {
        uploadStatus.innerHTML = 'No file selected';
        uploadStatus.style.color = '#666';
      }
      
      // Hide the preview
      const selectedFilePreview = document.getElementById('selectedFilePreview');
      if (selectedFilePreview) {
        selectedFilePreview.style.display = 'none';
      }
      
      // Reset the hidden input to ensure server knows no image was selected
      const imageUploadedElement = document.getElementById('imageUploaded');
      if (imageUploadedElement) {
        imageUploadedElement.value = '0';
        console.log('Image upload flag reset to 0');
      }
      
      // Reset the main product preview if it exists
      if (productPreview) {
        // Check if there was already an image preview
        const existingPreview = productPreview.querySelector('.preview-image');
        if (existingPreview) {
          // Restore original upload UI instead of clearing everything
          productPreview.innerHTML = `
            <div class="upload-container">
              <i class="fas fa-cloud-upload-alt upload-icon"></i>
              <div class="upload-text">Upload an image of your desired product</div>
            </div>
          `;
        }
      }
      uploadedImageUrl = '';
      productPreview.innerHTML = `
        <div class="upload-container">
          <i class="fas fa-cloud-upload-alt upload-icon"></i>
          <div class="upload-text">Upload an image of your desired product</div>
          <label for="imageUpload" class="upload-button">
            <i class="fas fa-upload"></i> Choose Image
          </label>
        </div>
      `;
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
      image: productImage,
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