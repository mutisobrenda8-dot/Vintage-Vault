// Week4/js/main.js

// 1. Smooth page load
document.body.style.opacity    = '0';
document.body.style.transition = 'opacity 0.3s ease';
window.addEventListener('load', () => {
    document.body.style.opacity = '1';
});

// 2. Auto hide alerts
document.querySelectorAll('.vv-alert-success, .vv-alert-error')
    .forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });

// 3. Form validation
const forms = document.querySelectorAll('form[data-validate]');
forms.forEach(form => {
    form.addEventListener('submit', function(e) {
        let valid = true;
        this.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#c0522a';
                valid = false;
            } else {
                field.style.borderColor = '';
            }
        });
        if (!valid) e.preventDefault();
    });
});

// 4. Password strength checker
const pwInput = document.getElementById('passwordInput');
const pwBar   = document.getElementById('strengthBar');
const pwText  = document.getElementById('strengthText');

if (pwInput && pwBar && pwText) {
    pwInput.addEventListener('input', function () {
        const val = this.value;
        let score = 0;

        if (val.length === 0) {
            pwBar.style.width  = '0%';
            pwText.textContent = '';
            return;
        }

        if (val.length >= 6)            score++;
        if (val.length >= 10)           score++;
        if (/[A-Z]/.test(val))         score++;
        if (/[0-9]/.test(val))         score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        let feedback, color, width;

        if (score <= 1) {
            feedback = '⚠️ Weak';
            color    = '#c0522a';
            width    = '25%';
        } else if (score === 2) {
            feedback = '🔶 Fair';
            color    = '#e08c1a';
            width    = '50%';
        } else if (score === 3) {
            feedback = '🔷 Good';
            color    = '#4a7a9b';
            width    = '75%';
        } else {
            feedback = '✅ Strong!';
            color    = '#2d7a4f';
            width    = '100%';
        }

        pwBar.style.width      = width;
        pwBar.style.background = color;
        pwText.style.color     = color;
        pwText.textContent     = feedback;
    });
}