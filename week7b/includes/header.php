<?php
// week1/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vintage Vault — <?= $pageTitle ?? 'Curated Vintage Goods' ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- Our CSS -->
    <link rel="stylesheet" href="/vintage-vault/week7b/css/style.css">
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg vv-navbar">
    <div class="container">

        <a class="navbar-brand vv-logo" href="/vintage-vault/week7b/index.php">Vintage Vault</a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">

            <!-- Left links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/vintage-vault/week7b/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/vintage-vault/week7b/shop.php">Shop</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/vintage-vault/week7b/shop.php">Categories</a>
                </li>
            </ul>

            <!-- Right links -->
            <ul class="navbar-nav ms-auto align-items-center gap-2">

                <!-- Cart -->
                <li class="nav-item">
                    <a class="nav-link" href="/vintage-vault/week7b/cart.php">
                        🛒 Cart
                        <?php
                        // Show cart count if logged in
                        if (isset($_SESSION['user_id'])) {
                            global $pdo;
                            if (isset($pdo)) {
                                $cartStmt = $pdo->prepare(
                                    "SELECT COALESCE(SUM(quantity),0)
                                     FROM cart WHERE user_id = ?"
                                );
                                $cartStmt->execute([$_SESSION['user_id']]);
                                $cartCount = (int) $cartStmt->fetchColumn();
                                if ($cartCount > 0) {
                                    echo '<span class="badge vv-badge">'
                                         . $cartCount . '</span>';
                                }
                            }
                        }
                        ?>
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Logged in -->
                    <li class="nav-item">
                        <span class="nav-link vv-username">
                            👤 <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </span>
                    </li>

                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" style="color:#f0c070 !important"
                               href="/vintage-vault/week7b/admin/dashboard.php">
                                Admin
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="/vintage-vault/week7b/user/dashboard.php">
                                My Account
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link"
                           href="/vintage-vault/week7b/logout.php">Logout</a>
                    </li>

                <?php else: ?>
                    <!-- Not logged in -->
                    <li class="nav-item">
                        <a class="nav-link" href="/vintage-vault/week7b/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn vv-btn-outline"
                           href="/vintage-vault/week7b/register.php">Register</a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>