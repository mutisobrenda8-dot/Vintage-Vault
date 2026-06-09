<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vintage Vault — <?= $pageTitle ?? 'Week 4' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/week1-brenda/Week4b/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg vv-navbar">
    <div class="container">
        <a class="navbar-brand vv-logo"
           href="/week1-brenda/Week4/index.php">
            Vintage Vault
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <span class="nav-link vv-username">
                            👤 <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="/week1-brenda/Week4/dashboard.php">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="/week1-brenda/Week4b/logout.php">
                            Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="/week1-brenda/Week4b/login.php">
                            Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn vv-btn-outline"
                           href="/week1-brenda/Week4b/index.php">
                            Home
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>