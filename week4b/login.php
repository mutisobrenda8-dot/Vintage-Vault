<?php
// Week4/login.php
// Task 2 & 3 — Login form with PHP authentication and sessions

$pageTitle = 'Login';
require 'db.php';
require 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Receive form data
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Check database
        $stmt = $pdo->prepare(
            "SELECT * FROM users WHERE email = ?"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Start session and store user data
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect to dashboard
            header('Location: /vintage-vault/week4b//dashboard.php');
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

        <form method="POST" action="" data-validate>

            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email"
                       class="form-control"
                       value="<?= htmlspecialchars(
                           $_POST['email'] ?? ''
                       ) ?>"
                       placeholder="you@example.com" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password"
                       class="form-control"
                       placeholder="••••••••" required>
            </div>

            <button type="submit"
                    class="btn btn-vv-primary w-100 py-2">
                Sign in
            </button>

        </form>

        <p class="text-center mt-3"
           style="font-size:0.8rem; color:var(--vv-muted)">
            No account?
            <a href="/week1-brenda/Week4/register.php">
                Register here
            </a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>