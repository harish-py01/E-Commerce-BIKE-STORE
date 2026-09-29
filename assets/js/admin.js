/* Admin Panel JavaScript Utilities */

document.addEventListener('DOMContentLoaded', function () {

    // Sidebar Toggle
    const menuToggle = document.getElementById('menu-toggle');
    const wrapper = document.getElementById('wrapper');

    if (menuToggle && wrapper) {
        menuToggle.addEventListener('click', function (e) {
            e.preventDefault();
            wrapper.classList.toggle('toggled');
        });
    }

    // Image Input Live Preview
    const imageInputs = document.querySelectorAll('.image-preview-input');
    imageInputs.forEach(input => {
        input.addEventListener('change', function () {
            const targetId = this.getAttribute('data-preview-target');
            const previewImage = document.getElementById(targetId);

            if (previewImage && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    previewImage.classList.remove('d-none');
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // Admin Table Instant Search Filter
    const searchInputs = document.querySelectorAll('.table-search-input');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function () {
            const targetTableId = this.getAttribute('data-table-target');
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#' + targetTableId + ' tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
            });
        });
    });

    // SweetAlert Deletion Confirmations in Admin
    const deleteBtns = document.querySelectorAll('.admin-delete-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            const itemName = this.getAttribute('data-name') || 'this item';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Confirmation',
                    text: 'Are you sure you want to delete "' + itemName + '"? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete "' + itemName + '"?')) {
                    window.location.href = href;
                }
            }
        });
    });
});
