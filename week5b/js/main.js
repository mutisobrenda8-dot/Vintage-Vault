// Week5/js/main.js

// Smooth page load
document.body.style.opacity    = '0';
document.body.style.transition = 'opacity 0.3s ease';
window.addEventListener('load', () => {
    document.body.style.opacity = '1';
});

// Auto hide alerts
document.querySelectorAll('.vv-alert-success, .vv-alert-error')
    .forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });

// Confirm delete
document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to delete this product?')) {
            e.preventDefault();
        }
    });
});