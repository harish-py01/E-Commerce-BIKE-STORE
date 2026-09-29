/* Storefront Main JavaScript Utilities */

document.addEventListener('DOMContentLoaded', function () {

    // Auto dismiss bootstrap alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Product Detail Page - Image Gallery Switcher
    const mainImg = document.getElementById('mainProductImg');
    const thumbImgs = document.querySelectorAll('.thumb-img');

    if (mainImg && thumbImgs.length > 0) {
        thumbImgs.forEach(thumb => {
            thumb.addEventListener('click', function () {
                mainImg.src = this.getAttribute('data-src');
                thumbImgs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // Quantity Input Plus / Minus Controls
    const qtyBtnMinus = document.querySelectorAll('.qty-btn-minus');
    const qtyBtnPlus = document.querySelectorAll('.qty-btn-plus');

    qtyBtnMinus.forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.closest('.quantity-control').querySelector('.qty-input');
            let currentVal = parseInt(input.value) || 1;
            if (currentVal > 1) {
                input.value = currentVal - 1;
                triggerFormSubmitIfNeeded(input);
            }
        });
    });

    qtyBtnPlus.forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.closest('.quantity-control').querySelector('.qty-input');
            let currentVal = parseInt(input.value) || 1;
            let maxVal = parseInt(input.getAttribute('max')) || 999;
            if (currentVal < maxVal) {
                input.value = currentVal + 1;
                triggerFormSubmitIfNeeded(input);
            }
        });
    });

    function triggerFormSubmitIfNeeded(input) {
        const form = input.closest('form.auto-submit-qty');
        if (form) {
            form.submit();
        }
    }

    // SweetAlert Confirm for Cart Removals / Actions
    const confirmActions = document.querySelectorAll('.btn-confirm-delete');
    confirmActions.forEach(element => {
        element.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            const message = this.getAttribute('data-message') || 'Are you sure you want to delete this item?';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmColor: '#dc3545',
                    cancelColor: '#6c757d',
                    confirmButtonText: 'Yes, proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            } else {
                if (confirm(message)) {
                    window.location.href = href;
                }
            }
        });
    });

    // Add to Cart Fly Animation
    const addToCartForms = document.querySelectorAll('form[action*="add_to_cart.php"]');
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            btn.disabled = true;

            // Find product image
            const card = form.closest('.product-card') || form.closest('.card');
            let img = null;
            if (card) {
                img = card.querySelector('img');
            }
            const cartIcon = document.querySelector('.cart-badge');

            if (img && cartIcon) {
                // Create clone
                const clone = img.cloneNode();
                const rect = img.getBoundingClientRect();
                clone.style.position = 'fixed';
                clone.style.top = rect.top + 'px';
                clone.style.left = rect.left + 'px';
                clone.style.width = rect.width + 'px';
                clone.style.height = rect.height + 'px';
                clone.style.zIndex = '9999';
                clone.style.transition = 'all 0.8s cubic-bezier(0.25, 0.8, 0.25, 1)';
                document.body.appendChild(clone);

                // Start animation
                setTimeout(() => {
                    const cartRect = cartIcon.getBoundingClientRect();
                    clone.style.top = cartRect.top + 'px';
                    clone.style.left = cartRect.left + 'px';
                    clone.style.width = '20px';
                    clone.style.height = '20px';
                    clone.style.opacity = '0.5';
                    clone.style.transform = 'scale(0.1) rotate(360deg)';
                }, 10);

                setTimeout(() => {
                    clone.remove();
                    form.submit();
                }, 800);
            } else {
                form.submit();
            }
        });
    });
});
