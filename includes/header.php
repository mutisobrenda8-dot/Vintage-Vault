<?php
// week1/includes/header.php
// This is included at the TOP of every page.
// It starts the session and prints the <head> + navigation.

session_start();  // Must be first — allows us to use $_SESSION variables
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vintage Vault — <?= $pageTitle ?? 'Curated Vintage Goods' ?></title>

    <!-- Bootstrap 5 for responsive grid and components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts: Playfair Display (headings) + Lato (body) -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- Our custom stylesheet -->
    <link rel="stylesheet" href="/week1/css/style.css">
</head>
<body>

<!-- ===== NAVIGATION BAR ===== -->
<nav class="navbar navbar-expand-lg vv-navbar">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand vv-logo" href="/week1/index.php">Vintage Vault</a>

        <!-- Mobile hamburger toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav links -->
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/week1/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/week1/shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="/week1/categories.php">Categories</a></li>
            </ul>

            <!-- Right side: Cart + Account -->
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="/week1/cart.php">
                        🛒 Cart
                        <?php if (!empty($_SESSION['cart'])): ?>
                            <span class="badge vv-badge"><?= count($_SESSION['cart']) ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Logged in: show username and logout -->
                    <li class="nav-item">
                        <span class="nav-link vv-username">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </li>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link vv-admin-link" href="/week1/admin/dashboard.php">Admin Panel</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/week1/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <!-- Not logged in: show Login and Register -->
                    <li class="nav-item"><a class="nav-link" href="/week1/login.php">Login</a></li>
                    <li class="nav-item"><a class="btn vv-btn-outline" href="/week1/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<!-- ===== END NAVIGATION ===== -->