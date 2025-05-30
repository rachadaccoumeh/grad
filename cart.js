// RoomGenius Global Cart Functionality
document.addEventListener('DOMContentLoaded', function() {
  // Make cart functionality available globally
  window.cartManager = {
    // Initialize the cart
    init: function() {
      this.cartContainer = document.querySelector('.cart-container');
      this.body = document.querySelector('body');
      this.closeButton = document.querySelector('.cartTab .close');
      this.checkoutButton = document.querySelector('.checkOut');
      this.listCart = document.querySelector('.listCart');
      
      // Ensure cart is hidden when page loads
      if (this.body) {
        this.body.classList.remove('showCart');
      }
      
      // Load cart from localStorage
      this.loadCart();
      
      // Setup event listeners
      this.setupEventListeners();
      
      // Update the UI
      this.updateCartUI();
      this.updateCartCount();
    },
    
    // Setup all event listeners
    setupEventListeners: function() {
      // Toggle cart visibility
      if (this.cartContainer) {
        this.cartContainer.addEventListener('click', (e) => {
          e.preventDefault();
          this.body.classList.add('showCart');
        });
      }
      
      // Close cart
      if (this.closeButton) {
        this.closeButton.addEventListener('click', () => {
          this.body.classList.remove('showCart');
        });
      }
      
      // Checkout functionality
      if (this.checkoutButton) {
        this.checkoutButton.addEventListener('click', () => {
          if (this.cart.length > 0) {
            // Redirect to checkout page
            window.location.href = 'checkout.php';
          } else {
            alert('Your cart is empty. Please add items before checkout.');
          }
        });
      }
    },
    
    // Cart data array
    cart: [],
    
    // Load cart from localStorage
    loadCart: function() {
      const savedCart = localStorage.getItem('cart');
      if (savedCart) {
        try {
          this.cart = JSON.parse(savedCart);
        } catch (e) {
          console.error('Error parsing cart data:', e);
          this.cart = [];
          localStorage.setItem('cart', '[]');
        }
      }
    },
    
    // Save cart to localStorage
    saveCart: function() {
      try {
        localStorage.setItem('cart', JSON.stringify(this.cart));
      } catch (e) {
        console.error('Error saving cart data:', e);
      }
    },
    
    // Add to cart method accessible from gallery.php
    addToCart: function(button, requestedQuantity = 1) {
      if (!button) return;
      
      // Get product details from data attributes
      const productId = button.getAttribute('data-id');
      const productName = button.getAttribute('data-name');
      const productPrice = button.getAttribute('data-price');
      const productImage = button.getAttribute('data-image');
      const availableQuantity = parseInt(button.getAttribute('data-quantity') || '0');
      
      if (!productId || !productName || !productPrice) {
        console.error('Missing product data for adding to cart');
        return;
      }
      
      // Check if there's enough stock available
      if (availableQuantity < requestedQuantity) {
        // Show error notification
        this.showNotification(`Sorry, only ${availableQuantity} item(s) available in stock.`, true);
        return false;
      }
      
      // Check if product already exists in the cart
      const existingItemIndex = this.cart.findIndex(item => item.id === productId);
      
      if (existingItemIndex > -1) {
        // Check if adding the requested quantity would exceed available stock
        const currentQuantity = this.cart[existingItemIndex].quantity;
        const newQuantity = currentQuantity + requestedQuantity;
        
        if (newQuantity > availableQuantity) {
          this.showNotification(`Cannot add ${requestedQuantity} more. Only ${availableQuantity - currentQuantity} more available.`, true);
          return false;
        }
        
        // Increment quantity if product already exists
        this.cart[existingItemIndex].quantity = newQuantity;
      } else {
        // Add new product to cart
        this.cart.push({
          id: productId,
          name: productName,
          price: parseFloat(productPrice.replace(/[^0-9.-]+/g, '')), // Ensure price is a number
          image: productImage,
          quantity: requestedQuantity,
          stockQuantity: availableQuantity // Store stock quantity for future reference
        });
      }
      
      // Save cart to localStorage
      this.saveCart();
      
      // Update cart UI
      this.updateCartUI();
      this.updateCartCount();
      
      // Visual feedback
      button.textContent = "Added!";
      setTimeout(() => {
        button.textContent = "Add to cart";
      }, 1500);
      
      // Show notification
      this.showNotification(`${productName} added to cart!`);
      
      return true;
    },
    
    // Update cart count badge
    updateCartCount: function() {
      const cartCount = document.getElementById('cartCount');
      if (cartCount) {
        const totalItems = this.cart.reduce((total, item) => total + item.quantity, 0);
        cartCount.textContent = totalItems;
      }
    },
    
    // Update cart interface
    updateCartUI: function() {
      if (!this.listCart) return;
      
      // Clear current cart display
      this.listCart.innerHTML = '';
      
      if (this.cart.length === 0) {
        // Show empty cart message
        const emptyCart = document.createElement('div');
        emptyCart.className = 'empty-cart';
        emptyCart.textContent = 'Your cart is empty';
        this.listCart.appendChild(emptyCart);
        return;
      }
      
      // Calculate cart totals
      const subtotal = this.cart.reduce((total, item) => {
        const price = parseFloat(item.price) || 0;
        return total + (price * item.quantity);
      }, 0);
      
      // Add each item to the cart
      this.cart.forEach((item, index) => {
        const cartItem = document.createElement('div');
        cartItem.className = 'item';
        cartItem.dataset.id = item.id;
        
        // Handle different image property names
        const imgSrc = item.image || '';
        
        cartItem.innerHTML = `
          <div class="image">
            <img src="${imgSrc}" alt="${item.name}">
          </div>
          <div class="name">
            ${item.name}
          </div>
          <div class="totalPrice">
            $${(parseFloat(item.price) * item.quantity).toFixed(2)}
          </div>
          <div class="quantity">
            <span class="minus" data-index="${index}">-</span>
            <span>${item.quantity}</span>
            <span class="plus" data-index="${index}">+</span>
          </div>
        `;
        
        this.listCart.appendChild(cartItem);
      });

      // Add cart summary section (subtotal)
      const cartSummary = document.createElement('div');
      cartSummary.className = 'cart-summary';
      cartSummary.innerHTML = `
        <div class="subtotal">
          <strong>Subtotal:</strong> $${subtotal.toFixed(2)}
        </div>
      `;

      // Style the cart summary
      cartSummary.style.marginTop = '15px';
      cartSummary.style.paddingTop = '15px';
      cartSummary.style.borderTop = '1px solid rgba(255, 248, 227, 0.2)';
      cartSummary.style.textAlign = 'right';
      cartSummary.style.fontSize = '16px';

      this.listCart.appendChild(cartSummary);
      
      // Add event listeners to quantity buttons
      const self = this;
      this.listCart.querySelectorAll('.minus').forEach(button => {
        button.addEventListener('click', function() {
          const index = parseInt(this.getAttribute('data-index'));
          if (self.cart[index].quantity > 1) {
            self.cart[index].quantity--;
          } else {
            // Remove item if quantity becomes 0
            self.cart.splice(index, 1);
          }
          self.saveCart();
          self.updateCartUI();
          self.updateCartCount();
        });
      });
      
      this.listCart.querySelectorAll('.plus').forEach(button => {
        button.addEventListener('click', function() {
          const index = parseInt(this.getAttribute('data-index'));
          const cartItem = self.cart[index];
          const productId = cartItem.id;
          
          // If we already have the stock quantity stored, use it
          if (cartItem.stockQuantity !== undefined) {
            if (cartItem.quantity < cartItem.stockQuantity) {
              // Only increase if there's enough stock
              cartItem.quantity++;
              self.saveCart();
              self.updateCartUI();
              self.updateCartCount();
            } else {
              // Show error notification if at max stock
              self.showNotification(`Sorry, only ${cartItem.stockQuantity} item(s) available in stock.`, true);
            }
          } else {
            // Get the stock quantity from the database via AJAX if not stored
            self.checkStockQuantity(productId, (availableQuantity) => {
              // Store the stock quantity for future reference
              cartItem.stockQuantity = availableQuantity;
              self.saveCart();
              
              if (cartItem.quantity < availableQuantity) {
                // Only increase if there's enough stock
                cartItem.quantity++;
                self.saveCart();
                self.updateCartUI();
                self.updateCartCount();
              } else {
                // Show error notification if at max stock
                self.showNotification(`Sorry, only ${availableQuantity} item(s) available in stock.`, true);
              }
            });
          }
        });
      });
    },
    
    // Show notification when adding to cart
    showNotification: function(message, isError = false) {
      const notification = document.createElement('div');
      notification.textContent = message;
      notification.style.position = 'fixed';
      notification.style.bottom = '20px';
      notification.style.right = '20px';
      notification.style.backgroundColor = isError ? '#e74c3c' : '#24424c';
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
    },
    
    // Check stock quantity from the database
    checkStockQuantity: function(productId, callback) {
      // Create AJAX request to check stock quantity
      const xhr = new XMLHttpRequest();
      xhr.open('GET', `check_stock.php?product_id=${productId}`, true);
      
      xhr.onload = function() {
        if (this.status === 200) {
          try {
            const response = JSON.parse(this.responseText);
            if (response.success) {
              // Call the callback with the available quantity
              callback(parseInt(response.quantity) || 0);
            } else {
              console.error('Error checking stock:', response.message);
              // Default to 0 if there's an error
              callback(0);
            }
          } catch (e) {
            console.error('Error parsing stock check response:', e);
            callback(0);
          }
        } else {
          console.error('Error checking stock. Status:', this.status);
          callback(0);
        }
      };
      
      xhr.onerror = function() {
        console.error('Request error while checking stock');
        callback(0);
      };
      
      xhr.send();
    }
  };
  
  // Make cart functions globally available
  window.updateCartUI = function() {
    window.cartManager.updateCartUI();
    window.cartManager.updateCartCount();
  };
  
  window.addToCart = function(button, quantity = 1) {
    return window.cartManager.addToCart(button, quantity);
  };
  
  // Initialize the cart
  window.cartManager.init();
  
  // Hook into existing add to cart buttons
  document.querySelectorAll('.add-to-cart').forEach(button => {
    // Remove existing click listeners to avoid duplicates
    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);
    
    // Add our click handler
    newButton.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      window.cartManager.addToCart(this);
    });
  });
});