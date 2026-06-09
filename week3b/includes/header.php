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
    <title>Vintage Vault — <?= $pageTitle ?? 'Curated Vintage Goods' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/week1-brenda/week1b/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg vv-navbar">
    <div class="container">
        <a class="navbar-brand vv-logo" href="//week1-brenda/week1b//index.php">
            Vintage Vault
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="//week1-brenda/week1b//index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="//week1-brenda/week1b//shop.php">Shop</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center gap-2">

                <!-- Cart -->
                <li class="nav-item">
                    <a class="nav-link" href="//week1-brenda/week1b//cart.php">
                        🛒 Cart
                        <?php
                        if (isset($_SESSION['user_id']) && isset($pdo)) {
                            $s = $pdo->prepare(
                                "SELECT COALESCE(SUM(quantity),0)
                                 FROM cart WHERE user_id=?"
                            );
                            $s->execute([$_SESSION['user_id']]);
                            $c = (int)$s->fetchColumn();
                            if ($c > 0) {
                                echo '<span class="badge vv-badge">'
                                     .$c.'</span>';
                            }
                        }
                        ?>
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <span class="nav-link vv-username">
                            👤 <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </span>
                    </li>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link"
                               style="color:#f0c070 !important"
                               href="//week1-brenda/week1b//admin/dashboard.php">
                               Admin
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="//week1-brenda/week1b//user/dashboard.php">
                               My Account
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="//week1-brenda/week1b//logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="//week1-brenda/week1b//login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn vv-btn-outline"
                           href="//week1-brenda/week1b//register.php">Register</a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>