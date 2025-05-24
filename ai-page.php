<?php
require_once 'config.php'; // We'll create this next
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AI Design Generator</title>
  <link rel="stylesheet" href="ai-page.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    /* Error message styling */
    .error-message {
      margin: 20px 0;
      animation: fadeIn 0.3s ease-in-out;
    }
    
    .alert-danger {
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
      padding: 15px;
      border-radius: 5px;
      position: relative;
    }
    
    .alert .close {
      position: absolute;
      top: 10px;
      right: 10px;
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: #721c24;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Upload Container */
    .upload-container {
      margin-bottom: 20px;
    }
    
    /* Hide the default file input */
    .file-input {
      width: 0.1px;
      height: 0.1px;
      opacity: 0;
      overflow: hidden;
      position: absolute;
      z-index: -1;
    }
    
    /* Style the label to look like a button/area */
    .upload-label {
      display: block;
      cursor: pointer;
    }
    
    /* Upload Area Styles */
    .upload-area {
      border: 2px dashed #5d43c8;
      border-radius: 8px;
      padding: 2rem;
      text-align: center;
      transition: all 0.3s ease;
      background-color: #f9f9ff;
      margin-bottom: 15px;
    }
    
    .upload-area:hover {
      border-color: #7e61ff;
      background-color: rgba(125, 97, 255, 0.05);
    }
    
    .upload-icon {
      font-size: 3rem;
      color: #7e61ff;
      margin-bottom: 1rem;
      display: block;
    }
    
    .upload-text {
      color: #333;
      margin: 0.5rem 0;
      font-size: 1.1rem;
      font-weight: 500;
    }
    
    .upload-hint {
      color: #666;
      font-size: 0.9rem;
      margin: 0.5rem 0 0;
    }
    
    .image-preview {
      display: none;
      margin-top: 20px;
      text-align: center;
    }
    
    .image-preview img {
      max-width: 100%;
      max-height: 300px;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
      margin: 10px 0;
      display: block;
    }
    
    .change-image-btn {
      background: #5d43c8;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.9rem;
      margin-top: 10px;
      transition: background-color 0.3s ease;
    }
    
    .change-image-btn:hover {
      background: #7e61ff;
    }
    
    /* Loading indicator */
    .loading-indicator {
      text-align: center;
      padding: 20px;
      margin: 20px 0;
    }
    
    .loading-indicator .spinner {
      border: 4px solid rgba(0, 0, 0, 0.1);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border-left-color: #5d43c8;
      animation: spin 1s linear infinite;
      margin: 0 auto 10px;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    /* Generated images */
    .result-container {
      margin-top: 30px;
      padding: 20px;
      background: #f9f9ff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      display: none;
    }
    
    .result-container h2 {
      margin-top: 0;
      color: #333;
      text-align: center;
    }
    
    .generated-image-container {
      margin-bottom: 20px;
      text-align: center;
    }
    
    .generated-image {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 10px;
    }
    
    .download-btn {
      background: #5d43c8;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.9rem;
      transition: background-color 0.3s ease;
    }
    
    .download-btn:hover {
      background: #7e61ff;
    }
    
    /* Result Container */
    .result-container {
      margin-top: 30px;
      padding: 25px;
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      background-color: #fff;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      display: none;
    }
    
    .result-container h2 {
      color: #333;
      margin-top: 0;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }
    
    /* Generated Images */
    .generated-image-card {
      background: #fff;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    
    .generated-image {
      max-width: 100%;
      border-radius: 8px;
      margin-bottom: 15px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }
    
    .generated-image:hover {
      transform: scale(1.02);
    }
    
    /* Buttons */
    .download-btn {
      background: #5d43c8;
      color: white;
      padding: 10px 24px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
    }
    
    .download-btn:hover {
      background: #7e61ff;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(125, 97, 255, 0.3);
    }
    
    .download-btn i {
      font-size: 14px;
    }
    
    /* Loading Spinner */
    .spinner {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    
    .spinner-border {
      width: 1.2rem;
      height: 1.2rem;
      border-width: 0.15em;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <i class="fas fa-brain"></i>
      <i class="fas fa-couch"></i>
      RoomGenius
    </div>
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
      </div>
      <div class="ai-button-container">
        <button class="ai-button" onclick="window.location.href='gallery.php'">
          <i class="fas fa-images"></i> Gallery
        </button>
      </div>
    </div>
  </header>

  <div class="main-container">
    <h1 class="page-title">AI-Powered Room Design Generator</h1>
    
    <form id="aiDesignForm" enctype="multipart/form-data" method="POST">
      <div class="main-content">
        <div class="preview-area">
          <div class="upload-container">
            <label for="imageUpload" class="upload-label">
              <div class="upload-area">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p class="upload-text">Click to upload an image or drag and drop</p>
                <p class="upload-hint">PNG, JPG, or WebP (Max 5MB)</p>
              </div>
              <input type="file" id="imageUpload" name="imageUpload" class="file-input" accept="image/*" required>
            </label>
            
            <div id="imagePreview" class="image-preview">
              <h3>Your Image:</h3>
              <img id="previewImage" src="#" alt="Preview">
            </div>
          </div>
        </div>
        
        <div class="control-panel">
          <div class="step">
            <div class="step-header">
              <div class="step-number">1</div>
              <h3>Room Details</h3>
            </div>
            
            <div class="form-group">
              <label>Room Type</label>
              <select class="form-control" id="roomType" name="roomType" required>
                <option value="living room">Living Room</option>
                <option value="bedroom">Bedroom</option>
                <option value="kitchen">Kitchen</option>
                <option value="bathroom">Bathroom</option>
                <option value="office">Office</option>
                <option value="dining room">Dining Room</option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Design Style</label>
              <select class="form-control" id="designStyle" name="designStyle" required>
                <option value="scandinavian">Scandinavian</option>
                <option value="modern">Modern</option>
                <option value="minimalist">Minimalist</option>
                <option value="industrial">Industrial</option>
                <option value="traditional">Traditional</option>
                <option value="rustic">Rustic</option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Color Scheme</label>
              <input type="text" class="form-control" id="colorScheme" name="colorScheme" placeholder="e.g., neutral with blue accents" required>
            </div>
          </div>
          
          <div class="step">
            <div class="step-header">
              <div class="step-number">2</div>
              <h3>Design Preferences</h3>
            </div>
            
            <div class="form-group">
              <label>Design Style Intensity</label>
              <input type="range" class="ai-slider" id="styleIntensity" name="styleIntensity" min="1" max="4" value="2">
              <div class="slider-labels">
                <span class="slider-label">Subtle</span>
                <span class="slider-label">Moderate</span>
                <span class="slider-label">Strong</span>
                <span class="slider-label">Bold</span>
              </div>
            </div>
            
            <div class="form-group">
              <label>Additional Instructions</label>
              <textarea class="form-control" id="additionalInstructions" name="additionalInstructions" rows="3" placeholder="Any specific requirements or preferences..."></textarea>
            </div>
            
            <div class="form-group">
              <label>Number of Variations</label>
              <select class="form-control" id="variationCount" name="variationCount">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="4">4</option>
              </select>
            </div>
          </div>
          
          <button type="submit" class="generate-btn" id="generateBtn">
            <span id="btnText">Generate Design</span>
            <div id="spinner" class="spinner" style="display: none;">
              <div class="spinner-border text-light" role="status">
                <span class="sr-only">Loading...</span>
              </div>
              <span style="margin-left: 10px;">Generating...</span>
            </div>
          </button>
        </div>
      </div>
    </form>
    
    <!-- Results Section -->
    <div id="resultContainer" class="result-container">
      <h2>Generated Design</h2>
      <div id="generatedImages"></div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const imageUpload = document.getElementById('imageUpload');
      const previewImage = document.getElementById('previewImage');
      const imagePreview = document.querySelector('.image-preview');
      const form = document.getElementById('aiDesignForm');
      
      // Get the upload area element
      const uploadArea = document.querySelector('.upload-area');
      
      // Handle file selection
      imageUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Basic validation
        const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
          alert('Please select a valid image (JPEG, PNG, or WebP).');
          return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
          alert('File is too large. Maximum size is 5MB.');
          return;
        }
        
        // Show preview and hide upload area
        const reader = new FileReader();
        reader.onload = function(e) {
          // Hide upload area and show preview
          uploadArea.style.display = 'none';
          previewImage.src = e.target.result;
          imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      });
      
      // Add a way to change the image
      const changeImageBtn = document.createElement('button');
      changeImageBtn.textContent = 'Change Image';
      changeImageBtn.className = 'change-image-btn';
      changeImageBtn.type = 'button';
      changeImageBtn.onclick = function() {
        imageUpload.value = ''; // Clear the file input
        uploadArea.style.display = 'block';
        imagePreview.style.display = 'none';
      };
      imagePreview.appendChild(changeImageBtn);
      
      // Handle form submission with AJAX
      form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        // Basic validation
        if (!imageUpload.files || !imageUpload.files[0]) {
          alert('Please select an image first.');
          return;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        
        // Hide any previous results
        const resultContainer = document.getElementById('resultContainer');
        if (resultContainer) {
          resultContainer.style.display = 'none';
        }
        
        // Create FormData object with additional debug logging
        const formData = new FormData(form);
        
        // Debug file upload - check if the file is being included properly
        console.log('Debug: Checking file upload...'); 
        const uploadedFile = imageUpload.files[0];
        console.log('Debug: File selected:', uploadedFile ? uploadedFile.name + ' (' + uploadedFile.size + ' bytes)' : 'No file selected');
        
        // Log all form data for debugging
        console.log('Debug: Form data contents:');
        for (let pair of formData.entries()) {
          console.log(pair[0] + ': ' + (pair[0] === 'imageUpload' ? 'File: ' + pair[1].name : pair[1]));
        }
        
        // Show loading indicator
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'loading-indicator';
        loadingIndicator.innerHTML = '<div class="spinner"></div><p>Generating your design, please wait...</p>';
        form.parentNode.insertBefore(loadingIndicator, form.nextSibling);
        
        // Send AJAX request
        fetch('generate_design.php', {
          method: 'POST',
          body: formData
        })
        .then(response => {
          // First check if the response is ok
          if (!response.ok) {
            // Special handling for rate limit errors (429)
            if (response.status === 429) {
              // Try to get the detailed message from the response
              return response.json().then(errorData => {
                // Add a comment with the detailed error information for debugging
                console.log('Rate limit error details:', errorData);
                // Throw a user-friendly error message
                throw new Error('The AI design service is temporarily unavailable due to high demand. Please try again in 15-20 minutes when the quota resets.');
              }).catch(e => {
                // If JSON parsing fails, provide a generic rate limit message
                throw new Error('The AI service has reached its quota limit. Please try again later.');
              });
            }
            
            // For other errors, try to parse the response as JSON to get detailed error message
            return response.json().then(errorData => {
              // If we have a structured error response, use it
              if (errorData && errorData.error) {
                throw new Error(errorData.error);
              } else {
                // Otherwise use the status text
                throw new Error(`Server error: ${response.status} ${response.statusText}`);
              }
            }).catch(e => {
              // If JSON parsing fails, use the status text
              throw new Error(`Server error: ${response.status} ${response.statusText}`);
            });
          }
          return response.json();
        })
        .then(data => {
          // Remove loading indicator
          if (loadingIndicator.parentNode) {
            loadingIndicator.parentNode.removeChild(loadingIndicator);
          }
          
          if (data.success && data.images && data.images.length > 0) {
            // Display the generated images
            displayGeneratedImages(data.images);
          } else {
            throw new Error(data.error || 'No images were generated. Please try again.');
          }
        })
        .catch(error => {
          // Remove loading indicator
          if (loadingIndicator.parentNode) {
            loadingIndicator.parentNode.removeChild(loadingIndicator);
          }
          
          console.error('Error:', error);
          
          // Create a more user-friendly error message based on the error
          let errorMessage = error.message || 'Failed to generate design. Please try again.';
          
          // Check for specific error types
          if (errorMessage.includes('rate limit') || errorMessage.includes('quota') || errorMessage.includes('unavailable due to high demand') || errorMessage.includes('429')) {
            // Add a more detailed and helpful message for rate limit errors
            errorMessage = 'The AI design generation service is temporarily unavailable due to high demand. This happens when the service reaches its limit with the AI provider. Please try again in 15-20 minutes when the quota resets.';
          } else if (errorMessage.includes('file size')) {
            errorMessage = 'The uploaded image is too large. Please use an image smaller than 5MB.';
          } else if (errorMessage.includes('file type') || errorMessage.includes('MIME type')) {
            errorMessage = 'Invalid file type. Please upload a JPEG, PNG, or WebP image.';
          }
          
          // Add a comment with technical details for development debugging
          console.log('Technical error details:', error);
          
          // Display error in a more user-friendly way
          const errorContainer = document.createElement('div');
          errorContainer.className = 'error-message';
          errorContainer.innerHTML = `<div class="alert alert-danger" role="alert">
            <strong>Error:</strong> ${errorMessage}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>`;
          
          // Add the error message to the page
          form.parentNode.insertBefore(errorContainer, form.nextSibling);
          
          // Auto-dismiss after 10 seconds
          setTimeout(() => {
            if (errorContainer.parentNode) {
              errorContainer.parentNode.removeChild(errorContainer);
            }
          }, 10000);
        })
        .finally(() => {
          // Reset button state
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;
        });
      });
      
      // Function to handle error message close button clicks
      document.addEventListener('click', function(e) {
        // Check if the clicked element is a close button for an alert
        if (e.target.closest('.alert .close')) {
          const alert = e.target.closest('.alert');
          if (alert && alert.parentNode) {
            // Find the parent error-message container and remove it
            const errorContainer = alert.closest('.error-message');
            if (errorContainer && errorContainer.parentNode) {
              errorContainer.parentNode.removeChild(errorContainer);
            }
          }
        }
      });
      
      // Function to display generated images
      function displayGeneratedImages(images) {
        // Create or get result container
        let resultContainer = document.getElementById('resultContainer');
        if (!resultContainer) {
          resultContainer = document.createElement('div');
          resultContainer.id = 'resultContainer';
          resultContainer.className = 'result-container';
          document.querySelector('.main-container').appendChild(resultContainer);
        }
        
        // Clear previous results
        resultContainer.innerHTML = '<h2>Generated Designs</h2>';
        
        // Add each image to the container
        images.forEach((imageData, index) => {
          const imgContainer = document.createElement('div');
          imgContainer.className = 'generated-image-container';
          
          const img = document.createElement('img');
          img.src = 'data:image/jpeg;base64,' + imageData;
          img.alt = 'Generated Design ' + (index + 1);
          img.className = 'generated-image';
          
          const downloadBtn = document.createElement('button');
          downloadBtn.className = 'download-btn';
          downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download';
          downloadBtn.onclick = function() {
            downloadImage(img.src, 'design-' + (index + 1) + '.jpg');
          };
          
          imgContainer.appendChild(img);
          imgContainer.appendChild(downloadBtn);
          resultContainer.appendChild(imgContainer);
        });
        
        // Show the result container
        resultContainer.style.display = 'block';
        resultContainer.scrollIntoView({ behavior: 'smooth' });
      }
    });
    
    // Search function
    window.searchProducts = function() {
      const query = document.getElementById('searchInput')?.value.trim();
      if (query) {
        window.location.href = 'search.php?q=' + encodeURIComponent(query);
      }
    };
    
    // Download function
    window.downloadImage = function(imageSrc, filename) {
      // Create a temporary link element
      const link = document.createElement('a');
      link.href = imageSrc;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };
  </script>
</body>
</html>
