// Wishlist functionality
let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
let cart = JSON.parse(localStorage.getItem('cart')) || [];

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
  
  // Update wishlist count
  updateWishlistCount();
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

// Update wishlist count in header
function updateWishlistCount() {
  const wishlistCount = document.getElementById('wishlistCount');
  if (wishlistCount) {
    wishlistCount.textContent = wishlist.length;
  }
}

// Initialize wishlist buttons when page loads
function initWishlistButtons() {
  const favoriteButtons = document.querySelectorAll('.favorite-btn');
  
  favoriteButtons.forEach(button => {
    // Check if button is inside a product card (could be in modal)
    const product = button.closest('.product-card');
    
    // Skip buttons that aren't in product cards (like those in the modal)
    if (!product) return;
    
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
    
    // Remove existing event listeners to prevent duplicates
    button.removeEventListener('click', favoriteButtonClickHandler);
    
    // Add click event listener
    button.addEventListener('click', favoriteButtonClickHandler);
  });
}

// Handler function for favorite button clicks
function favoriteButtonClickHandler(e) {
  e.preventDefault();
  toggleWishlist(this);
}

// Add to cart functionality
function addToCart(button, event) {
  // Prevent default to avoid any default button behavior
  if (event) event.preventDefault();
  
  // Prevent multiple clicks
  if (button.getAttribute('data-adding') === 'true') return;
  button.setAttribute('data-adding', 'true');
  
  console.log('Adding to cart:', button);
  
  const product = button.closest('.product-card');
  const productId = button.getAttribute('data-id') || product.getAttribute('data-id');
  const productTitle = button.getAttribute('data-name') || product.getAttribute('data-name');
  const priceString = button.getAttribute('data-price') || product.getAttribute('data-price');
  const productPrice = parseFloat(priceString.replace(/[^0-9.-]+/g,""));
  const productImage = button.getAttribute('data-image') || product.querySelector('img')?.src;
  
  // Check if item already exists in cart
  const existingItemIndex = cart.findIndex(item => item.id === productId);
  
  if (existingItemIndex > -1) {
    // Update quantity if item exists
    cart[existingItemIndex].quantity = (parseInt(cart[existingItemIndex].quantity) || 1) + 1;
  } else {
    // Add new item to cart
    cart.push({
      id: productId,
      name: productTitle,
      price: productPrice,
      image: productImage,
      quantity: 1
    });
  }
  
  console.log('Updated cart:', cart);
  
  // Save to localStorage
  localStorage.setItem('cart', JSON.stringify(cart));
  
  // Update cart count
  const cartCount = document.getElementById('cartCount');
  if (cartCount) {
    const totalItems = cart.reduce((total, item) => total + (parseInt(item.quantity) || 1), 0);
    cartCount.textContent = totalItems;
    console.log('Updated cart count:', totalItems);
  }
  
  // Visual feedback for adding to cart
  const originalText = button.textContent;
  button.textContent = "Added!";
  button.disabled = true;
  
  setTimeout(() => {
    button.textContent = originalText;
    button.disabled = false;
    button.removeAttribute('data-adding');
  }, 1500);
  
  // Show notification
  showNotification(`${productTitle} added to cart!`);
  
  // Prevent any further execution
  return false;
}

// Function to toggle cart visibility
function toggleCart() {
  document.body.classList.toggle('showCart');
}

// Function to filter products based on selected criteria
function filterProducts() {
  const style = document.getElementById('style').value;
  const priceRange = document.getElementById('priceRange').value;
  const sortOption = document.getElementById('sortOptions').value;
  const productCards = document.querySelectorAll('.product-card');
  
  let visibleCount = 0;
  
  productCards.forEach(card => {
    const cardStyle = card.getAttribute('data-style');
    const cardPrice = parseInt(card.getAttribute('data-price'));
    
    // Check if card matches all selected filters
    let styleMatch = style === '' || cardStyle === style;
    let priceMatch = true;
    
    // Price range logic
    if (priceRange !== '') {
      if (priceRange === 'Under $500' && cardPrice >= 500) {
        priceMatch = false;
      } else if (priceRange === '$500 - $1000' && (cardPrice < 500 || cardPrice > 1000)) {
        priceMatch = false;
      } else if (priceRange === '$1000 - $2000' && (cardPrice < 1000 || cardPrice > 2000)) {
        priceMatch = false;
      } else if (priceRange === '$2000+' && cardPrice < 2000) {
        priceMatch = false;
      }
    }
    
    // Show or hide based on filter match
    if (styleMatch && priceMatch) {
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
  toggleClearFiltersButton(style, priceRange, sortOption);
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
      // For now, using random ordering for popularity
      return 0.5 - Math.random();
    }
    return 0;
  });
  
  // Re-append sorted products
  products.forEach(product => {
    productGrid.appendChild(product);
  });
}

// Search functionality
function searchProducts() {
  const searchInput = document.getElementById('searchInput').value.toLowerCase();
  const productCards = document.querySelectorAll('.product-card');
  let foundResults = false;
  
  productCards.forEach(card => {
    const title = card.querySelector('h4').textContent.toLowerCase();
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
  
  // Show clear filters button if search is active
  toggleClearFiltersButton(false, false, false, searchInput !== '');
}

// Function to toggle the clear filters button
function toggleClearFiltersButton(style, priceRange, sortOption, searchActive = false) {
  const clearFiltersBtn = document.getElementById('clearFilters');
  
  if (style || priceRange || sortOption || searchActive) {
    clearFiltersBtn.style.display = 'block';
  } else {
    clearFiltersBtn.style.display = 'none';
  }
}

// Function to clear all filters
function clearAllFilters() {
  document.getElementById('style').value = '';
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
  
  // Hide clear filters button
  document.getElementById('clearFilters').style.display = 'none';
}

// Function to navigate to selected category
function navigateToPage() {
  var select = document.getElementById("categorySelect");
  var selectedValue = select.value;
  if (selectedValue) {
    window.location.href = selectedValue;
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Initialize wishlist buttons
  initWishlistButtons();
  
  // Update wishlist count
  updateWishlistCount();
  
  // Update cart count
  const cartCount = document.getElementById('cartCount');
  if (cartCount) {
    cartCount.textContent = cart.length;
  }
  
  // Add event listener to cart toggle
  const cartContainer = document.querySelector('.cart-container');
  if (cartContainer) {
    cartContainer.addEventListener('click', toggleCart);
  }
  
  // Add event listener to cart close button
  const closeCartBtn = document.querySelector('.close');
  if (closeCartBtn) {
    closeCartBtn.addEventListener('click', toggleCart);
  }
  
  // Hide clear filters button on page load
  const clearFiltersBtn = document.getElementById('clearFilters');
  if (clearFiltersBtn) {
    clearFiltersBtn.style.display = 'none';
  }
  
  // Add search input enter key listener
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        searchProducts();
      }
    });
  }
  
  // Initialize category selector
  const categorySelect = document.getElementById('categorySelect');
  if (categorySelect) {
    categorySelect.addEventListener('change', navigateToPage);
  }
});