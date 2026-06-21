<script>
const passwordInput = document.getElementById('passwordInput');
const strengthBar   = document.getElementById('strengthBar');
const strengthText  = document.getElementById('strengthText');

passwordInput.addEventListener('input', function () {
    const val    = this.value;
    let score    = 0;
    let feedback = '';
    let color    = '';
    let width    = '';

    if (val.length === 0) {
        strengthBar.style.width = '0%';
        strengthText.textContent = '';
        return;
    }

    // Check criteria
    if (val.length >= 6)                    score++;
    if (val.length >= 10)                   score++;
    if (/[A-Z]/.test(val))                  score++;
    if (/[0-9]/.test(val))                  score++;
    if (/[^A-Za-z0-9]/.test(val))          score++;

    if (score <= 1) {
        feedback = '⚠️ Weak password';
        color    = '#c0522a';
        width    = '25%';
    } else if (score === 2) {
        feedback = '🔶 Fair password';
        color    = '#e08c1a';
        width    = '50%';
    } else if (score === 3) {
        feedback = '🔷 Good password';
        color    = '#4a7a9b';
        width    = '75%';
    } else {
        feedback = '✅ Strong password!';
        color    = '#2d7a4f';
        width    = '100%';
    }

    strengthBar.style.width      = width;
    strengthBar.style.background = color;
    strengthText.style.color     = color;
    strengthText.textContent     = feedback;
});
</script>
<?php
// week1/includes/footer.php
?>
<!-- FOOTER -->
<footer class="vv-footer mt-5">
    <div class="container text-center">
        <p class="vv-footer-logo">Vintage Vault</p>
        <p class="vv-footer-sub">Curating history, one piece at a time.</p>
        <p class="vv-footer-copy">
            &copy; <?= date('Y') ?> Vintage Vault. All rights reserved.
        </p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Main JS — only load if file exists -->
<?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vintage-vault/week6b/js/main.js')): ?>
    <script src="/vintage-vault/week6b/js/main.js"></script>
<?php endif; ?>

</body>
</html>