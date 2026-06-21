<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) ||
    $_SESSION['user_role'] !== 'admin') {
    header('Location: /vintage-vault/week7b/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — <?= $pageTitle ?? 'Dashboard' ?> | Vintage Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/vintage-vault/week7b/css/admin.css">
</head>
<body>

<header class="admin-topbar">
    <div style="display:flex; align-items:center; gap:16px;">
        <button class="sidebar-toggle" id="sidebarToggle">☰</button>
        <span class="logo">Vintage Vault</span>
        <span style="color:#5c4438; font-size:0.75rem;
                     letter-spacing:1px;">Admin Panel</span>
    </div>
    <div class="topbar-right">
        <span class="admin-name">
            👤 <?= htmlspecialchars($_SESSION['user_name']) ?>
        </span>
        <a href="/vintage-vault/week7b/index.php" class="topbar-btn" target="_blank">
            View Store
        </a>
        <a href="/vintage-vault/week7b/logout.php" class="topbar-btn">Logout</a>
    </div>
</header>

<div class="admin-wrapper">
<?php require 'admin_sidebar.php'; ?>
<main class="admin-main">