<?php
// week1/register.php
// Handles both showing the form (GET) and processing it (POST)

$pageTitle = 'Register';
require 'db.php';
require 'includes/header.php';

$error   = '';   // Will hold any error message
$success = '';   // Will hold success message

// Only run this block when the form is submitted (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Step 1: Read and clean the submitted data
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // Step 2: Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';

    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';

    } else {
        // Step 3: Check if email is already registered
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'That email address is already registered.';
        } else {
            // Step 4: Hash the password (NEVER store plain text passwords)
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Step 5: Insert the new user into the database
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (?, ?, ?, 'customer')
            ");
            $stmt->execute([$name, $email, $hashedPassword]);

            $success = 'Account created! You can now <a href="/week1/login.php">log in</a>.';
        }
    }
}
?>

<div class="container">
    <div class="vv-form-card">
        <h2>Create account</h2>
        <p class="vv-form-subtitle">Join the Vintage Vault community</p>

        <?php if ($error): ?>
            <div class="vv-alert-error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="vv-alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="mb-3">
                <label for="name" class="form-label">Full name</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       placeholder="Jane Smith" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="At least 6 characters" required>
            </div>

            <div class="mb-4">
                <label for="confirm" class="form-label">Confirm password</label>
                <input type="password" id="confirm" name="confirm" class="form-control"
                       placeholder="Repeat your password" required>
            </div>

            <button type="submit" class="btn btn-vv-primary w-100 py-2">Create account</button>

        </form>

        <p class="text-center mt-3" style="font-size:0.8rem; color:var(--vv-muted)">
            Already have an account? <a href="/week1/login.php">Sign in</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>