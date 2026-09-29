<?php
// contact.php - Contact Us Page
$page_title = "Contact Support - Bike Store";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row g-4">
        <!-- Contact Info Column -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-dark text-white h-100">
                <h3 class="fw-bold mb-4">Get In Touch</h3>
                <p class="text-light opacity-75 mb-4">Have questions regarding spare part fitment or order status? Send us a message and our technical team will assist you.</p>

                <div class="d-flex align-items-center mb-4">
                    <div class="bg-danger p-3 rounded-circle me-3"><i class="fas fa-map-marker-alt fs-4"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0 text-white">Store Address</h6>
                        <small class="text-light opacity-75">123 MG Road, Indiranagar, Bengaluru, KA 560038</small>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <div class="bg-danger p-3 rounded-circle me-3"><i class="fas fa-phone-alt fs-4"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0 text-white">Customer Support</h6>
                        <small class="text-light opacity-75">+91 98765 43210 (Mon - Sat: 9 AM - 8 PM)</small>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <div class="bg-danger p-3 rounded-circle me-3"><i class="fas fa-envelope fs-4"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0 text-white">Email Address</h6>
                        <small class="text-light opacity-75">support@bikestore.com</small>
                    </div>
                </div>

                <hr class="border-secondary my-4">

                <h6 class="fw-bold mb-3">Follow Us</h6>
                <div class="d-flex gap-3 fs-5">
                    <a href="#" class="text-light"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>

        <!-- Contact Form Column -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h3 class="fw-bold text-dark mb-4"><i class="fas fa-paper-plane text-danger me-2"></i> Send Us A Message</h3>

                <form action="#" method="POST" onsubmit="event.preventDefault(); Swal.fire('Message Sent!', 'Thank you for contacting us. Our team will get back to you shortly.', 'success'); this.reset();">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Your Name *</label>
                            <input type="text" class="form-control" required placeholder="John Doe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Your Email *</label>
                            <input type="email" class="form-control" required placeholder="name@example.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Subject</label>
                        <input type="text" class="form-control" placeholder="Inquiry about Brake Pad fitment">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary">Message *</label>
                        <textarea class="form-control" rows="5" required placeholder="Describe your inquiry..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold">
                        <i class="fas fa-paper-plane me-2"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
