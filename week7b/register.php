<?php
$pageTitle = 'Register';
require 'db.php';
require 'includes/header.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'That email is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare(
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, 'customer')"
            )->execute([$name, $email, $hashed]);
            $success = 'Account created! <a href="/vintage-vault/week7b/login.php">Log in here</a>.';
        }
    }
}
?>

<div class="container">
    <div class="vv-form-card">
        <h2>Create account</h2>
        <p class="vv-form-subtitle">
            Join the Vintage Vault community
        </p>

        <?php if ($error): ?>
            <div class="vv-alert-error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="vv-alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="mb-3">
                <label class="form-label">Full name</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       placeholder="Jane Smith" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password"
                       id="passwordInput"
                       class="form-control"
                       placeholder="At least 6 characters" required>

                <!-- Password Strength Bar -->
                <div style="margin-top:8px;">
                    <div style="height:4px; background:var(--vv-parchment);
                                border-radius:2px; overflow:hidden;">
                        <div id="strengthBar"
                             style="height:100%; width:0%;
                                    transition:all 0.3s ease;
                                    border-radius:2px;">
                        </div>
                    </div>
                    <p id="strengthText"
                       style="font-size:0.72rem; margin-top:4px;
                              letter-spacing:1px; color:var(--vv-muted);">
                    </p>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm password</label>
                <input type="password" name="confirm"
                       class="form-control"
                       placeholder="Repeat your password" required>
            </div>

            <button type="submit"
                    class="btn btn-vv-primary w-100 py-2">
                Create account
            </button>

        </form>

        <p class="text-center mt-3"
           style="font-size:0.8rem; color:var(--vv-muted)">
            Already have an account?
            <a href="/vintage-vault/week7b/login.php">Sign in</a>
        </p>
    </div>
</div>

<!-- Password Strength Checker -->
<script>
const passwordInput = document.getElementById('passwordInput');
const strengthBar   = document.getElementById('strengthBar');
const strengthText  = document.getElementById('strengthText');

passwordInput.addEventListener('input', function () {
    const val = this.value;
    let score = 0;

    if (val.length === 0) {
        strengthBar.style.width  = '0%';
        strengthText.textContent = '';
        return;
    }

    if (val.length >= 6)               score++;
    if (val.length >= 10)              score++;
    if (/[A-Z]/.test(val))            score++;
    if (/[0-9]/.test(val))            score++;
    if (/[^A-Za-z0-9]/.test(val))    score++;

    let feedback, color, width;

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

<?php require 'includes/footer.php'; ?>