<?php
// Week7/reset_password.php
$pageTitle = 'Reset Password';
require 'db.php';
require 'includes/header.php';

$error = $success = '';
$token = $_GET['token'] ?? $_POST['token'] ?? '';

// Validate token
$stmt = $pdo->prepare("
    SELECT * FROM users
    WHERE reset_token = ? AND reset_token_expires > NOW()
");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $error = 'This reset link is invalid or has expired. Please request a new one.';
}

if ($user && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("
            UPDATE users
            SET password = ?, reset_token = NULL, reset_token_expires = NULL
            WHERE id = ?
        ")->execute([$hashed, $user['id']]);

        $success = 'Your password has been reset successfully! 
            <a href="/vintage-vault/week7b/login.php">Log in now</a>.';
        $user = null; // hide form after success
    }
}
?>

<div class="container">
    <div class="vv-form-card">
        <h2>Reset Password</h2>
        <p class="vv-form-subtitle">
            Choose a new password for your account
        </p>

        <?php if ($error): ?>
            <div class="vv-alert-error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="vv-alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($user): ?>
        <form method="POST" action="">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" id="passwordInput"
                       class="form-control"
                       placeholder="At least 6 characters" required>
                <div style="margin-top:8px;">
                    <div style="height:4px; background:var(--vv-parchment);
                                border-radius:2px; overflow:hidden;">
                        <div id="strengthBar" style="height:100%; width:0%;
                             transition:all 0.3s ease; border-radius:2px;"></div>
                    </div>
                    <p id="strengthText" style="font-size:0.72rem; margin-top:4px;
                       letter-spacing:1px; color:var(--vv-muted);"></p>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm" class="form-control"
                       placeholder="Repeat new password" required>
            </div>

            <button type="submit" class="btn btn-vv-primary w-100 py-2">
                Reset Password
            </button>
        </form>

        <script>
        const passwordInput = document.getElementById('passwordInput');
        const strengthBar   = document.getElementById('strengthBar');
        const strengthText  = document.getElementById('strengthText');
        passwordInput.addEventListener('input', function () {
            const val = this.value;
            let score = 0;
            if (val.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
                return;
            }
            if (val.length >= 6)            score++;
            if (val.length >= 10)           score++;
            if (/[A-Z]/.test(val))         score++;
            if (/[0-9]/.test(val))         score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            let feedback, color, width;
            if (score <= 1)      { feedback='⚠️ Weak';   color='#c0522a'; width='25%'; }
            else if (score === 2){ feedback='🔶 Fair';   color='#e08c1a'; width='50%'; }
            else if (score === 3){ feedback='🔷 Good';   color='#4a7a9b'; width='75%'; }
            else                 { feedback='✅ Strong!'; color='#2d7a4f'; width='100%'; }
            strengthBar.style.width      = width;
            strengthBar.style.background = color;
            strengthText.style.color     = color;
            strengthText.textContent     = feedback;
        });
        </script>
        <?php endif; ?>

        <p class="text-center mt-3"
           style="font-size:0.8rem; color:var(--vv-muted)">
            <a href="/vintage-vault/week7b/login.php">← Back to login</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>