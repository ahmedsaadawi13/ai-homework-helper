// FILE: /public/js/app.js
// AI Homework Helper - Main JavaScript

/**
 * Auto-hide alerts after 5 seconds
 */
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');

    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';

            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

/**
 * Confirm delete actions
 */
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

/**
 * Form validation helper
 */
function validateForm(formId) {
    const form = document.getElementById(formId);

    if (!form) {
        return true;
    }

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return isValid;
}

/**
 * Toggle elements
 */
function toggleElement(elementId) {
    const element = document.getElementById(elementId);

    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}

/**
 * Image preview for file uploads
 */
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            const preview = document.getElementById('image-preview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };

        reader.readAsDataURL(input.files[0]);
    }
}

// Attach image preview to file inputs
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');

    fileInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            previewImage(this);
        });
    });
});
