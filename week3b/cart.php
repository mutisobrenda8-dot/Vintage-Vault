<?php
$pageTitle = 'My Cart';
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /week1-brenda/week3b/login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.*, p.name AS product_name,
           p.price, p.stock, p.image
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

$cartTotal = array_sum(
    array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems)
);
?>

<div class="container py-5">
    <h1 style="font-family:var(--font-serif); font-style:italic;
               color:var(--vv-dark); margin-bottom:4px;">
        My Cart
    </h1>
    <p style="color:var(--vv-muted); font-size:0.82rem;
              margin-bottom:28px;">
        <?= count($cartItems) ?> item(s) in your cart
    </p>

    <?php if (empty($cartItems)): ?>
        <div style="text-align:center; padding:60px 20px;
                    color:var(--vv-muted);">
            <p style="font-size:3rem;">🛒</p>
            <p style="font-family:var(--font-serif); font-size:1.2rem;
                      margin-bottom:16px;">
                Your cart is empty
            </p>
            <a href="/week1-brenda/week3b/shop.php" class="btn btn-vv-primary px-4">
                Browse the Shop
            </a>
        </div>
    <?php else: ?>
    <div class="row g-4">

        <!-- Cart Items -->
        <div class="col-12 col-md-8">
            <div style="background:#faf6ef;
                        border:1px solid var(--vv-parchment);">
                <?php foreach ($cartItems as $item): ?>
                <div id="cart-row-<?= $item['product_id'] ?>"
                     style="display:flex; align-items:center;
                            gap:16px; padding:16px 20px;
                            border-bottom:1px solid var(--vv-parchment);">

                    <div style="width:72px; height:72px; flex-shrink:0;
                                background:var(--vv-parchment);
                                display:flex; align-items:center;
                                justify-content:center;
                                font-size:1.8rem;">
                        🏺
                    </div>

                    <div style="flex:1;">
                        <div style="font-family:var(--font-serif);
                                    color:var(--vv-dark);
                                    font-size:0.95rem; margin-bottom:4px;">
                            <?= htmlspecialchars($item['product_name']) ?>
                        </div>
                        <div style="font-size:0.8rem; color:var(--vv-brown);">
                            $<?= number_format($item['price'], 2) ?> each
                        </div>
                    </div>

                    <!-- Qty Controls -->
                    <div style="display:flex; align-items:center; gap:8px;">
                        <button class="qty-btn"
                                data-id="<?= $item['product_id'] ?>"
                                data-action="decrease"
                                style="width:28px; height:28px;
                                       background:var(--vv-parchment);
                                       border:1px solid var(--vv-gold);
                                       cursor:pointer; font-size:1rem;">
                            −
                        </button>
                        <span id="qty-<?= $item['product_id'] ?>"
                              style="font-size:0.95rem; min-width:20px;
                                     text-align:center;">
                            <?= $item['quantity'] ?>
                        </span>
                        <button class="qty-btn"
                                data-id="<?= $item['product_id'] ?>"
                                data-action="increase"
                                style="width:28px; height:28px;
                                       background:var(--vv-parchment);
                                       border:1px solid var(--vv-gold);
                                       cursor:pointer; font-size:1rem;">
                            +
                        </button>
                    </div>

                    <div id="subtotal-<?= $item['product_id'] ?>"
                         style="font-weight:700; color:var(--vv-brown);
                                min-width:70px; text-align:right;">
                        $<?= number_format(
                            $item['price'] * $item['quantity'], 2
                        ) ?>
                    </div>

                    <button class="remove-item-btn"
                            data-id="<?= $item['product_id'] ?>"
                            style="background:none; border:none;
                                   color:#c0522a; cursor:pointer;
                                   font-size:1.2rem;" title="Remove">
                        ✕
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-12 col-md-4">
            <div style="background:#faf6ef;
                        border:1px solid var(--vv-gold); padding:24px;">
                <h5 style="font-family:var(--font-serif);
                           font-style:italic; margin-bottom:20px;
                           color:var(--vv-dark);">
                    Order Summary
                </h5>
                <div style="display:flex; justify-content:space-between;
                            margin-bottom:12px; font-size:0.88rem;">
                    <span>Subtotal</span>
                    <span id="cartTotal">
                        $<?= number_format($cartTotal, 2) ?>
                    </span>
                </div>
                <div style="display:flex; justify-content:space-between;
                            margin-bottom:20px; font-size:0.88rem;
                            color:var(--vv-muted);">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <div style="border-top:1px solid var(--vv-parchment);
                            padding-top:16px; display:flex;
                            justify-content:space-between;
                            font-weight:700; margin-bottom:20px;">
                    <span>Total</span>
                    <span style="color:var(--vv-brown);">
                        $<?= number_format($cartTotal, 2) ?>
                    </span>
                </div>
                <a href="/week1-brenda/week3b/checkout.php"
                   class="btn btn-vv-primary w-100 py-2"
                   style="text-align:center; display:block;">
                    Proceed to Checkout →
                </a>
                <a href="/week1-brenda/week3b/shop.php"
                   style="display:block; text-align:center;
                          margin-top:12px; font-size:0.8rem;
                          color:var(--vv-muted);">
                    ← Continue Shopping
                </a>
            </div>
        </div>

    </div>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>