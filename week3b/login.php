<?php
$pageTitle = 'Login';
require 'db.php';
require 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

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

            if ($user['role'] === 'admin') {
                header('Location: /week1-brenda/week3b/admin/dashboard.php');
            } else {
                header('Location: /week1-brenda/week3b/user/dashboard.php');
            }
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
                <label for="email" class="form-label">Email address</label>
                <input type="email" id="email" name="email"
                       class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password"
                       class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-vv-primary w-100 py-2">
                Sign in
            </button>
        </form>

        <p class="text-center mt-3"
           style="font-size:0.8rem; color:var(--vv-muted)">
            No account?
            <a href="/week1-brenda/week3b/register.php">Register here</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>