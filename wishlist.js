// Wishlist functionality
let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];

// Function to toggle wishlist item
function toggleWishlist(button) {
  const product = button.closest('.product-card');
  const productId = product.getAttribute('data-id');
  const productTitle = product.querySelector('h4').textContent;
  const productPrice = product.querySelector('.price').textContent;
  const productDescription = product.querySelector('.gallery-description').textContent;
  const productImage = product.querySelector('img').src;
  
  // Check if item is already in wishlist
  const existingIndex = wishlist.findIndex(item => item.id === productId);
  
  if (existingIndex !== -1) {
    // Remove from wishlist
    wishlist.splice(existingIndex, 1);
    button.textContent = '♡';
    button.style.color = '#24424c';
    
    // Show notification
    showNotification(`${productTitle} removed from wishlist!`);
  } else {
    // Add to wishlist
    wishlist.push({
      id: productId,
      title: productTitle,
      price: productPrice,
      description: productDescription,
      image: productImage
    });
    button.textContent = '❤';
    button.style.color = 'red';
    
    // Show notification
    showNotification(`${productTitle} added to wishlist!`);
  }
  
  // Save to localStorage
  localStorage.setItem('wishlist', JSON.stringify(wishlist));
  
  // Update wishlist count if it exists
  const wishlistCount = document.getElementById('wishlistCount');
  if (wishlistCount) {
    wishlistCount.textContent = wishlist.length;
  }
}

// Function to show notification
function showNotification(message) {
  const notification = document.createElement('div');
  notification.textContent = message;
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

// Update favorite buttons when page loads
function initWishlistButtons() {
  const favoriteButtons = document.querySelectorAll('.favorite-btn');
  
  favoriteButtons.forEach(button => {
    const product = button.closest('.product-card');
    const productId = product.getAttribute('data-id');
    
    // Check if this product is in the wishlist
    const isInWishlist = wishlist.some(item => item.id === productId);
    
    // Update button appearance based on wishlist status
    if (isInWishlist) {
      button.textContent = '❤';
      button.style.color = 'red';
    } else {
      button.textContent = '♡';
      button.style.color = '#24424c';
    }
    
    // Add click event listener
    button.addEventListener('click', function(e) {
      e.preventDefault();
      toggleWishlist(this);
    });
  });
}

// Toggle cart function
function toggleCart() {
  document.body.classList.toggle('showCart');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Initialize wishlist buttons
  initWishlistButtons();
  
  // Add wishlist icon to header if it doesn't exist
  const headerIcons = document.querySelector('.icons');
  if (headerIcons && !document.querySelector('.wishlist-container')) {
    const wishlistContainer = document.createElement('span');
    wishlistContainer.className = 'wishlist-container';
    wishlistContainer.setAttribute('onclick', "window.location.href='wishlist.html'");
    wishlistContainer.innerHTML = `
      <i class="fas fa-heart" style="color: red;"></i>
      <span class="wishlist-count" id="wishlistCount">${wishlist.length}</span>
    `;
    
    // Insert after cart container
    const cartContainer = document.querySelector('.cart-container');
    if (cartContainer) {
      headerIcons.insertBefore(wishlistContainer, cartContainer.nextSibling);
    } else {
      headerIcons.appendChild(wishlistContainer);
    }
    
    // Add styling for wishlist count
    const style = document.createElement('style');
    style.textContent = `
      .wishlist-container {
        position: relative;
        display: inline-block;
        cursor: pointer;
      }
      .wishlist-container i {
        font-size: 18px;
      }
      .wishlist-count {
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
    `;
    document.head.appendChild(style);
  }
});