<?php
/**
 * Footer component for RoomGenius website
 * This file contains the footer HTML structure that can be reused across multiple pages
 */
?>

<footer class="site-footer">
  <div class="footer-container">
    <!-- Footer top section with columns -->
    <div class="footer-top">
      <!-- Company info column -->
      <div class="footer-column" data-aos="fade-up" data-aos-delay="50">
        <div class="footer-logo">
          <i class="fas fa-brain"></i>
          <i class="fas fa-couch"></i>
          <span>RoomGenius</span>
        </div>
        <p class="footer-description">
          Transforming spaces with intelligent design solutions. We help you create the perfect living environment with our innovative furniture and design services.
        </p>
        <div class="social-icons">
          <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-pinterest"></i></a>
        </div>
      </div>
      
      <!-- Quick links column -->
      <div class="footer-column" data-aos="fade-up" data-aos-delay="100">
        <h3>Quick Links</h3>
        <ul class="footer-links">
          <li><a href="home.php">Home</a></li>
          <li><a href="gallery.php">Gallery</a></li>
          <li><a href="about.php">About Us</a></li>
          <li><a href="contact.php">Contact</a></li>
          <li><a href="ai-page.php">AI Room Designer</a></li>
        </ul>
      </div>
      
      <!-- Categories column -->
      <div class="footer-column" data-aos="fade-up" data-aos-delay="150">
        <h3>Categories</h3>
        <ul class="footer-links">
          <!-- Updated links to use the new category.php page with appropriate parameters -->
          <li><a href="category.php?category=livingroom">Living Room</a></li>
          <li><a href="category.php?category=bedroom">Bedroom</a></li>
          <li><a href="category.php?category=kitchen">Kitchen</a></li>
          <li><a href="category.php?category=office">Office</a></li>
          <li><a href="category.php?category=diningroom">Dining Room</a></li>
        </ul>
      </div>
      
      <!-- Contact info column -->
      <div class="footer-column" data-aos="fade-up" data-aos-delay="200">
        <h3>Contact Us</h3>
        <ul class="contact-info">
          <li><i class="fas fa-map-marker-alt"></i> 123 Design Street, Creative City</li>
          <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
          <li><i class="fas fa-envelope"></i> info@roomgenius.com</li>
          <li><i class="fas fa-clock"></i> Mon-Fri: 9AM - 6PM</li>
        </ul>
      </div>
    </div>
    
    <!-- Newsletter subscription -->
    <div class="newsletter" data-aos="fade-up" data-aos-delay="250">
      <h3>Subscribe to Our Newsletter</h3>
      <p>Stay updated with our latest designs and promotions</p>
      <form class="newsletter-form">
        <input type="email" placeholder="Your Email Address" required>
        <button type="submit" class="subscribe-btn">Subscribe</button>
      </form>
    </div>
    
    <!-- Footer bottom with copyright -->
    <div class="footer-bottom">
      <div class="copyright">
        <p>&copy; <?php echo date('Y'); ?> RoomGenius. All Rights Reserved.</p>
      </div>
      <div class="footer-bottom-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">FAQ</a>
      </div>
    </div>
  </div>
</footer>

<!-- Back to top button -->
<button id="back-to-top" title="Back to Top"><i class="fas fa-arrow-up"></i></button>

<script>
// Back to top button functionality
document.addEventListener('DOMContentLoaded', function() {
  // Get the button
  const backToTopButton = document.getElementById('back-to-top');
  
  // Show the button when user scrolls down 300px
  window.onscroll = function() {
    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
      backToTopButton.classList.add('show');
    } else {
      backToTopButton.classList.remove('show');
    }
  };
  
  // Scroll to top when button is clicked
  backToTopButton.addEventListener('click', function() {
    // For Safari
    document.body.scrollTop = 0;
    // For Chrome, Firefox, IE and Opera
    document.documentElement.scrollTop = 0;
  });
});
</script>
