/**
 * Product Modal Functionality
 * This script handles the product modal popup when a user clicks on a product
 * Enhanced with image zoom and quantity selector functionality
 */

document.addEventListener('DOMContentLoaded', function() {
  // Get the modal element
  const modal = document.getElementById('productModal');
  
  // Get the close button
  const closeBtn = document.querySelector('.close-modal');
  
  // Get all product cards
  const productCards = document.querySelectorAll('.product-card');
  
  // Add click event to all product cards
  productCards.forEach(card => {
    // Add pointer cursor to indicate clickable
    card.style.cursor = 'pointer';
    
    // Add click event listener to show modal
    card.addEventListener('click', function(e) {
      // Don't show modal if clicking on buttons inside the card
      if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
        return;
      }
      
      // Get product data from card attributes
      const productId = card.getAttribute('data-id');
      const productName = card.getAttribute('data-name');
      const productPrice = card.getAttribute('data-price');
      const productDescription = card.getAttribute('data-description');
      const productStyle = card.getAttribute('data-style') || 'Not specified';
      const productSize = card.getAttribute('data-size') || 'Not specified';
      const productQuantity = parseInt(card.getAttribute('data-quantity') || '0');
      const productCategory = document.querySelector('.section-title h3').textContent.replace(' Products', '') || 'All Products';
      const productImage = card.querySelector('img').src;
      
      // Set modal content
      document.getElementById('modalProductName').textContent = productName;
      document.getElementById('modalProductPrice').textContent = formatPrice(productPrice);
      document.getElementById('modalProductDescription').textContent = productDescription;
      document.getElementById('modalProductCategory').textContent = productCategory;
      document.getElementById('modalProductStyle').textContent = productStyle;
      document.getElementById('modalProductSize').textContent = productSize;
      
      // Update availability status based on quantity
      const availabilityElement = document.getElementById('modalProductAvailability');
      if (productQuantity <= 0) {
        availabilityElement.textContent = 'Out of Stock';
        availabilityElement.className = 'out-of-stock';
      } else {
        availabilityElement.textContent = 'In Stock';
        availabilityElement.className = 'in-stock';
      }
      document.getElementById('modalProductImage').src = productImage;
      
      // Reset quantity to 1
      if (document.getElementById('quantity')) {
        document.getElementById('quantity').value = 1;
      }
      
      // Set data attributes for add to cart button
      const addToCartBtn = document.getElementById('modalAddToCartBtn');
      addToCartBtn.setAttribute('data-product-id', productId);
      addToCartBtn.setAttribute('data-id', productId);
      addToCartBtn.setAttribute('data-name', productName);
      addToCartBtn.setAttribute('data-price', productPrice);
      addToCartBtn.setAttribute('data-image', productImage);
      
      // Show the modal
      modal.style.display = 'block';
      
      // Prevent scrolling on the body when modal is open
      document.body.style.overflow = 'hidden';
    });
  });
  
  // Close modal when clicking the close button
  closeBtn.addEventListener('click', function() {
    closeModal();
  });
  
  // Close modal when clicking outside the modal content
  window.addEventListener('click', function(e) {
    if (e.target === modal) {
      closeModal();
    }
  });
  
  // Close modal when pressing ESC key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeModal();
    }
  });
  
  // Function to close the modal
  function closeModal() {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // Restore scrolling
  }
  
  // Quantity selector functionality
  setupQuantitySelector();
  
  // Add to cart functionality for modal button
  document.getElementById('modalAddToCartBtn').addEventListener('click', function() {
    const productId = this.getAttribute('data-product-id');
    const productName = this.getAttribute('data-name');
    const productPrice = this.getAttribute('data-price');
    const productImage = this.getAttribute('data-image');
    
    // Get quantity
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    // Call the addToCart function from cart.js with quantity
    // If the original addToCart function doesn't support quantity, we'll need to modify it
    try {
      // Try to use the quantity parameter if the function supports it
      addToCart(productId, productName, productPrice, productImage, quantity);
    } catch (e) {
      // Fallback to the original function if it doesn't support quantity
      // Add the product multiple times based on quantity
      for (let i = 0; i < quantity; i++) {
        addToCart(productId, productName, productPrice, productImage);
      }
    }
    
    // Show success message
    showNotification(`${quantity} ${quantity > 1 ? 'items' : 'item'} added to cart!`);
  });
  
  // Setup image zoom functionality
  setupImageZoom();
  
  // Fix for modal favorite button to prevent errors in gallery.js
  const modalFavoriteBtn = document.querySelector('.modal-favorite-btn');
  if (modalFavoriteBtn) {
    // Override the default click behavior to prevent errors
    modalFavoriteBtn.addEventListener('click', function(e) {
      e.stopPropagation(); // Prevent event bubbling
      
      // Get product data from the modal
      const productId = document.getElementById('modalAddToCartBtn').getAttribute('data-product-id');
      const productName = document.getElementById('modalProductName').textContent;
      
      // Toggle wishlist status
      if (typeof toggleWishlistItem === 'function') {
        toggleWishlistItem(productId, productName);
        
        // Update button appearance
        const isInWishlist = window.wishlist && window.wishlist.some(item => item.id === productId);
        this.innerHTML = isInWishlist ? '❤' : '♡';
        this.style.color = isInWishlist ? '#e74c3c' : '#906e2b';
      } else {
        // Fallback if wishlist function doesn't exist
        this.innerHTML = '❤';
        this.style.color = '#e74c3c';
        showNotification('Added to favorites!');
      }
    });
  }
  
  // Helper function to format price
  function formatPrice(price) {
    return new Intl.NumberFormat('en-US').format(price);
  }
  
  // Function to set up quantity selector
  function setupQuantitySelector() {
    const minusBtn = document.querySelector('.minus-btn');
    const plusBtn = document.querySelector('.plus-btn');
    const quantityInput = document.getElementById('quantity');
    
    if (minusBtn && plusBtn && quantityInput) {
      // Decrease quantity
      minusBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        if (value > 1) {
          quantityInput.value = value - 1;
        }
      });
      
      // Increase quantity
      plusBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        quantityInput.value = value + 1;
      });
      
      // Ensure valid input
      quantityInput.addEventListener('change', function() {
        let value = parseInt(this.value);
        if (isNaN(value) || value < 1) {
          this.value = 1;
        }
      });
    }
  }
  
  // Function to set up image zoom
  function setupImageZoom() {
    const zoomContainer = document.querySelector('.image-zoom-container');
    const zoomImage = document.getElementById('modalProductImage');
    
    if (zoomContainer && zoomImage) {
      // Track mouse position for more advanced zoom effect
      zoomContainer.addEventListener('mousemove', function(e) {
        const { left, top, width, height } = zoomContainer.getBoundingClientRect();
        const x = (e.clientX - left) / width;
        const y = (e.clientY - top) / height;
        
        // Calculate zoom position
        const transformOriginX = Math.max(0, Math.min(100, x * 100));
        const transformOriginY = Math.max(0, Math.min(100, y * 100));
        
        // Apply transform origin for zoom effect
        zoomImage.style.transformOrigin = `${transformOriginX}% ${transformOriginY}%`;
      });
    }
  }
  
  // Function to show notification
  function showNotification(message) {
    // Check if notification container exists, if not create it
    let notificationContainer = document.getElementById('notification-container');
    if (!notificationContainer) {
      notificationContainer = document.createElement('div');
      notificationContainer.id = 'notification-container';
      notificationContainer.style.position = 'fixed';
      notificationContainer.style.bottom = '20px';
      notificationContainer.style.right = '20px';
      notificationContainer.style.zIndex = '1000';
      document.body.appendChild(notificationContainer);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.style.backgroundColor = '#906e2b';
    notification.style.color = 'white';
    notification.style.padding = '12px 20px';
    notification.style.borderRadius = '4px';
    notification.style.marginTop = '10px';
    notification.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    notification.style.animation = 'slideInRight 0.3s, fadeOut 0.5s 2.5s forwards';
    notification.textContent = message;
    
    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
      @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
      }
      @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
      }
    `;
    document.head.appendChild(style);
    
    // Add notification to container
    notificationContainer.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }
});

// Comment: Enhanced the product modal with image zoom functionality that follows the mouse cursor,
// added quantity selector with validation, and updated the add to cart functionality to support
// multiple quantities of the same product
