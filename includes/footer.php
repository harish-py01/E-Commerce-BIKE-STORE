<?php
// includes/footer.php
?>
<footer class="mt-5 pt-5 pb-4 text-light border-top border-secondary" style="background-color: var(--dark-elevated);">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6">
                <img src="<?php echo base_url('assets/images/logo.svg'); ?>" alt="Bike Store" height="40" class="mb-3">
                <p class="text-muted small">
                    Your premier destination for high-performance bike spare parts, authentic OEM components, racing exhausts, and premium riding gear. Engineered for speed and durability.
                </p>
                <div class="d-flex gap-3 fs-5 mt-3 text-secondary">
                    <a href="#" class="text-light"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="text-white mb-3">Quick Links</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?php echo base_url('index.php'); ?>">Home</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('shop.php'); ?>">Shop Spare Parts</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('about.php'); ?>">About Us</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('contact.php'); ?>">Contact Support</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('cart.php'); ?>">Shopping Cart</a></li>
                </ul>
            </div>

            <!-- Top Categories -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-3">Top Categories</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?php echo base_url('shop.php?category=braking-systems'); ?>">Braking Systems</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('shop.php?category=engine-parts'); ?>">Engine Parts</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('shop.php?category=chains-sprockets'); ?>">Chains & Sprockets</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('shop.php?category=tires-wheels'); ?>">Tires & Wheels</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('shop.php?category=oils-lubricants'); ?>">Oils & Lubricants</a></li>
                </ul>
            </div>

            <!-- Contact & Newsletter -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-3">Newsletter</h5>
                <p class="text-muted small">Subscribe to get exclusive discount codes and new arrivals.</p>
                <form action="#" method="POST" onsubmit="event.preventDefault(); Swal.fire('Subscribed!', 'Thank you for subscribing to our newsletter.', 'success');">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control form-control-sm" placeholder="Your Email Address" required>
                        <button class="btn btn-danger btn-sm" type="submit"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
                <div class="small text-muted">
                    <p class="mb-1"><i class="fas fa-map-marker-alt text-danger me-2"></i> Bengaluru, Karnataka, India</p>
                    <p class="mb-0"><i class="fas fa-phone text-danger me-2"></i> +91 98765 43210</p>
                </div>
            </div>
        </div>

        <hr class="my-4 border-secondary">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted">
            <p class="mb-2 mb-md-0">&copy; <?php echo date('Y'); ?> Bike Spare Parts E-Commerce Store. All Rights Reserved. Designed by <strong>Grish A</strong>.</p>
            <div>
                <span class="me-3"><i class="fab fa-cc-visa fs-4 text-light"></i></span>
                <span class="me-3"><i class="fab fa-cc-mastercard fs-4 text-light"></i></span>
                <span><i class="fas fa-university fs-4 text-light"></i></span>
            </div>
        </div>
    </div>
</footer>

<!-- JS Scripts -->
<script src="<?php echo base_url('assets/js/vendor/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/vendor/sweetalert2.all.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/main.js'); ?>"></script>

</body>
</html>
