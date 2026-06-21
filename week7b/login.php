<?php
// week7b/login.php
$pageTitle = 'Login';
require 'db.php';
require 'includes/header.php';

$error = '';

// Auto-login via Remember Me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$_COOKIE['remember_token']]);
    $cookieUser = $stmt->fetch();
    if ($cookieUser) {
        $_SESSION['user_id']    = $cookieUser['id'];
        $_SESSION['user_name']  = $cookieUser['name'];
        $_SESSION['user_role']  = $cookieUser['role'];
        $_SESSION['user_email'] = $cookieUser['email'];
        header('Location: /vintage-vault/week7b/' .
            ($cookieUser['role'] === 'admin'
                ? 'admin/dashboard.php' : 'user/dashboard.php'));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email']    ?? '');
    $password   = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_role']  = $user['role'];
            $_SESSION['user_email'] = $user['email'];

            // Remember Me — issue a long-lived random token
            if ($rememberMe) {
                $token = bin2hex(random_bytes(32));
                $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?")
                    ->execute([$token, $user['id']]);
                setcookie('remember_token', $token, [
                    'expires'  => time() + (30 * 24 * 60 * 60), // 30 days
                    'path'     => '/vintage-vault/week7b/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }

            header('Location: /vintage-vault/week7b/' .
                ($user['role'] === 'admin'
                    ? 'admin/dashboard.php' : 'user/dashboard.php'));
            exit;
        } else {
            $error = 'Incorrect email or password.';
        }
    }
}
?>

<div class="container">
    <div class="vv-form-card">
        <h2>Welcome back</h2>
        <p class="vv-form-subtitle">
            Sign in to your Vintage Vault account
        </p>

        <?php if ($error): ?>
            <div class="vv-alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>

            <div class="mb-4" style="display:flex; justify-content:space-between;
                                       align-items:center;">
                <label style="display:flex; align-items:center; gap:6px;
                              font-size:0.8rem; color:var(--vv-muted);
                              text-transform:none; letter-spacing:0;
                              font-weight:400; cursor:pointer;">
                    <input type="checkbox" name="remember_me" value="1"
                           style="width:14px; height:14px;">
                    Remember me
                </label>
                <a href="/vintage-vault/week7b/forgot_password.php"
                   style="font-size:0.8rem; color:var(--vv-brown);">
                    Forgot password?
                </a>
            </div>

            <button type="submit" class="btn btn-vv-primary w-100 py-2">
                Sign in
            </button>
        </form>

        <p class="text-center mt-3"
           style="font-size:0.8rem; color:var(--vv-muted)">
            No account?
            <a href="/vintage-vault/week7b/register.php">Register here</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>