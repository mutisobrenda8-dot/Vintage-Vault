<?php
$pageTitle = 'Order Confirmed';
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /week1-brenda/week1b/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: /week1-brenda/week1b/index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.*, u.name AS customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: /week1-brenda/week1b/index.php');
    exit;
}

$items = $pdo->prepare("
    SELECT oi.*, p.name AS product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$items->execute([$id]);
$orderItems = $items->fetchAll();
?>

<div class="container py-5">
    <div style="max-width:600px; margin:0 auto; text-align:center;">

        <div style="font-size:4rem; margin-bottom:16px;">✅</div>

        <h1 style="font-family:var(--font-serif); font-style:italic;
                   color:var(--vv-dark); margin-bottom:8px;">
            Order Confirmed!
        </h1>
        <p style="color:var(--vv-muted); margin-bottom:32px;">
            Thank you, <?= htmlspecialchars($order['customer_name']) ?>!
            Your order has been placed successfully.
        </p>

        <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                    padding:24px; text-align:left; margin-bottom:24px;">

            <div style="display:flex; justify-content:space-between;
                        margin-bottom:16px;">
                <span style="font-size:0.75rem; letter-spacing:2px;
                             text-transform:uppercase;
                             color:var(--vv-muted);">
                    Order Number
                </span>
                <span style="font-family:var(--font-serif);
                             color:var(--vv-dark); font-weight:700;">
                    #<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?>
                </span>
            </div>

            <div style="display:flex; justify-content:space-between;
                        margin-bottom:16px;">
                <span style="font-size:0.75rem; letter-spacing:2px;
                             text-transform:uppercase;
                             color:var(--vv-muted);">
                    Status
                </span>
                <span style="background:#fef3cd; color:#856404;
                             padding:2px 10px; font-size:0.75rem;
                             letter-spacing:1px; text-transform:uppercase;">
                    Pending
                </span>
            </div>

            <div style="border-top:1px solid var(--vv-parchment);
                        padding-top:16px; margin-top:8px;">
                <?php foreach ($orderItems as $item): ?>
                <div style="display:flex; justify-content:space-between;
                            padding:6px 0; font-size:0.85rem;">
                    <span>
                        <?= htmlspecialchars($item['product_name']) ?>
                        <span style="color:var(--vv-muted);">
                            × <?= $item['quantity'] ?>
                        </span>
                    </span>
                    <span style="color:var(--vv-brown); font-weight:700;">
                        $<?= number_format(
                            $item['price'] * $item['quantity'], 2
                        ) ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <div style="border-top:1px solid var(--vv-parchment);
                            padding-top:12px; margin-top:8px;
                            display:flex; justify-content:space-between;
                            font-weight:700;">
                    <span>Total</span>
                    <span style="color:var(--vv-brown);">
                        $<?= number_format($order['total'], 2) ?>
                    </span>
                </div>
            </div>

        </div>

        <div style="display:flex; gap:12px; justify-content:center;">
            <a href="/week1-brenda/week1b/user/dashboard.php"
               class="btn btn-vv-primary px-4">
                View My Orders
            </a>
            <a href="/week1-brenda/week1b/shop.php"
               style="padding:8px 20px; border:1px solid var(--vv-gold);
                      color:var(--vv-brown); font-size:0.75rem;
                      letter-spacing:1.5px; text-transform:uppercase;
                      text-decoration:none; transition:all 0.2s;">
                Continue Shopping
            </a>
        </div>

    </div>
</div>

<?php require 'includes/footer.php'; ?>