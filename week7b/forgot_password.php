<?php
// week7b/forgot_password.php
$pageTitle = 'Forgot Password';
require 'db.php';
require 'includes/header.php';

$error = '';
$resetLinkHtml = '';
$plainSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

   $token = bin2hex(random_bytes(32));

$pdo->prepare("
    UPDATE users
    SET reset_token = ?, reset_token_expires = NOW() + INTERVAL 1 HOUR
    WHERE id = ?
")->execute([$token, $user['id']]);

        $resetLink = "/vintage-vault/week7b/reset_password.php?token=$token";
        $resetLinkHtml = "
            <div style='background:#d4f0e0; border-left:3px solid #2d7a4f;
                        color:#1a4d30; padding:16px; margin-bottom:16px;
                        font-size:0.85rem; border-radius:2px;'>
                A password reset link has been generated. In a real
                application this would be emailed to you. For this demo,
                click the link below:
                <br><br>
                <a href='$resetLink'
                   style='word-break:break-all; font-weight:600;'>
                    $resetLink
                </a>
            </div>
        ";
    } else {
        // Same message either way — don't reveal which emails exist
        $plainSuccess = 'If that email is registered, a password reset link has been generated.';
    }
?>

<div class="container">
    <div class="vv-form-card">
        <h2>Forgot Password</h2>
        <p class="vv-form-subtitle">
            Enter your email to receive a reset link
        </p>

        <?php if ($error): ?>
            <div class="vv-alert-error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($plainSuccess): ?>
            <div class="vv-alert-success"><?= $plainSuccess ?></div>
        <?php endif; ?>

        <?php if ($resetLinkHtml): ?>
            <?= $resetLinkHtml ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control"
                       placeholder="you@example.com" required>
            </div>
            <button type="submit" class="btn btn-vv-primary w-100 py-2">
                Send Reset Link
            </button>
        </form>

        <p class="text-center mt-3"
           style="font-size:0.8rem; color:var(--vv-muted)">
            <a href="/vintage-vault/week7b/login.php">← Back to login</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>