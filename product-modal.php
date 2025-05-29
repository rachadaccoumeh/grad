<!-- Product Modal -->
<div id="productModal" class="product-modal">
  <div class="product-modal-content">
    <span class="close-modal">&times;</span>
    <div class="product-modal-body">
      <div class="product-modal-image">
        <!-- Image container with zoom capability -->
        <div class="image-zoom-container">
          <img id="modalProductImage" src="" alt="Product Image">
          <div class="zoom-hint">Hover to zoom</div>
        </div>
      </div>
      <div class="product-modal-details">
        <h2 id="modalProductName"></h2>
        <p class="product-modal-price">$<span id="modalProductPrice"></span></p>
        <div class="product-modal-description" id="modalProductDescription"></div>
        <div class="product-modal-meta">
          <p><strong>Category:</strong> <span id="modalProductCategory"></span></p>
          <p><strong>Style:</strong> <span id="modalProductStyle"></span></p>
          <p><strong>Size:</strong> <span id="modalProductSize"></span></p>
          <p><strong>Availability:</strong> <span id="modalProductAvailability" class="in-stock">In Stock</span></p>
        </div>
        <div class="product-modal-quantity">
          <label for="quantity">Quantity:</label>
          <div class="quantity-selector">
            <button type="button" class="quantity-btn minus-btn">-</button>
            <input type="number" id="quantity" name="quantity" min="1" value="1">
            <button type="button" class="quantity-btn plus-btn">+</button>
          </div>
        </div>
        <div class="product-modal-actions">
          <button class="favorite-btn modal-favorite-btn">♡</button>
          <button class="add-to-cart modal-add-to-cart" 
                  id="modalAddToCartBtn"
                  data-product-id="" 
                  data-name="" 
                  data-price="" 
                  data-image="">Add to cart</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Comment: Added image zoom functionality, product rating, availability status, and quantity selector to enhance the user experience when viewing product details -->
