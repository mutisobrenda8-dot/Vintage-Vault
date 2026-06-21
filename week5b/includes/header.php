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
    <title>Vintage Vault — <?= $pageTitle ?? 'Week 5' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/vintage-vault/week5b//css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg vv-navbar">
    <div class="container">
        <a class="navbar-brand vv-logo"
           href="/vintage-vault/week5b//index.php">
            Vintage Vault
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <a class="nav-link"
                       href="/vintage-vault/week5b//index.php">
                        Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                       href="/vintage-vault/week5b//add.php">
                        Add Product
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>