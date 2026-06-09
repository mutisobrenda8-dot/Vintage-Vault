<?php
$pageTitle = 'Checkout';
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /week1-brenda/week1b/login.php');
    exit;
}

// Fetch cart
$stmt = $pdo->prepare("
    SELECT c.*, p.name AS product_name, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header('Location: /week1-brenda/week1b/cart.php');
    exit;
}

$cartTotal = array_sum(
    array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems)
);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = trim($_POST['full_name']   ?? '');
    $address     = trim($_POST['address']     ?? '');
    $city        = trim($_POST['city']        ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $country     = trim($_POST['country']     ?? '');

    if (empty($full_name) || empty($address) ||
        empty($city) || empty($country)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders
            (user_id, total, status, full_name,
             address, city, postal_code, country)
            VALUES (?, ?, 'pending', ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'], $cartTotal,
            $full_name, $address, $city, $postal_code, $country
        ]);
        $orderId = $pdo->lastInsertId();

        // Add order items
        foreach ($cartItems as $item) {
            $pdo->prepare("
                INSERT INTO order_items
                (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ")->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);

            // Reduce stock
            $pdo->prepare("
                UPDATE products SET stock = stock - ?
                WHERE id = ?
            ")->execute([$item['quantity'], $item['product_id']]);
        }

        // Clear cart
        $pdo->prepare(
            "DELETE FROM cart WHERE user_id = ?"
        )->execute([$_SESSION['user_id']]);

        // Redirect to confirmation
        header("Location: /week1-brenda/week1b/order_confirm.php?id=$orderId");
        exit;
    }
}
?>

<div class="container py-5">
    <h1 style="font-family:var(--font-serif); font-style:italic;
               color:var(--vv-dark); margin-bottom:4px;">
        Checkout
    </h1>
    <p style="color:var(--vv-muted); font-size:0.82rem;
              margin-bottom:28px;">
        Complete your order
    </p>

    <?php if ($error): ?>
        <div class="vv-alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Delivery Form -->
        <div class="col-12 col-md-7">
            <div style="background:#faf6ef;
                        border:1px solid var(--vv-gold); padding:28px;">
                <h5 style="font-family:var(--font-serif);
                           font-style:italic; margin-bottom:20px;
                           color:var(--vv-dark);">
                    Delivery Details
                </h5>
                <form method="POST">
                    <div class="mb-3">
                        <label style="font-size:0.7rem; letter-spacing:2px;
                                      text-transform:uppercase;
                                      color:var(--vv-brown);
                                      font-weight:700; display:block;
                                      margin-bottom:6px;">
                            Full Name *
                        </label>
                        <input type="text" name="full_name"
                               class="form-control"
                               style="border:1px solid var(--vv-gold);
                                      background:var(--vv-cream);
                                      border-radius:0;"
                               value="<?= htmlspecialchars(
                                   $_POST['full_name'] ?? ''
                               ) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.7rem; letter-spacing:2px;
                                      text-transform:uppercase;
                                      color:var(--vv-brown);
                                      font-weight:700; display:block;
                                      margin-bottom:6px;">
                            Address *
                        </label>
                        <input type="text" name="address"
                               class="form-control"
                               style="border:1px solid var(--vv-gold);
                                      background:var(--vv-cream);
                                      border-radius:0;"
                               value="<?= htmlspecialchars(
                                   $_POST['address'] ?? ''
                               ) ?>" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label style="font-size:0.7rem; letter-spacing:2px;
                                          text-transform:uppercase;
                                          color:var(--vv-brown);
                                          font-weight:700; display:block;
                                          margin-bottom:6px;">
                                City *
                            </label>
                            <input type="text" name="city"
                                   class="form-control"
                                   style="border:1px solid var(--vv-gold);
                                          background:var(--vv-cream);
                                          border-radius:0;"
                                   value="<?= htmlspecialchars(
                                       $_POST['city'] ?? ''
                                   ) ?>" required>
                        </div>
                        <div class="col-6">
                            <label style="font-size:0.7rem; letter-spacing:2px;
                                          text-transform:uppercase;
                                          color:var(--vv-brown);
                                          font-weight:700; display:block;
                                          margin-bottom:6px;">
                                Postal Code
                            </label>
                            <input type="text" name="postal_code"
                                   class="form-control"
                                   style="border:1px solid var(--vv-gold);
                                          background:var(--vv-cream);
                                          border-radius:0;"
                                   value="<?= htmlspecialchars(
                                       $_POST['postal_code'] ?? ''
                                   ) ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label style="font-size:0.7rem; letter-spacing:2px;
                                      text-transform:uppercase;
                                      color:var(--vv-brown);
                                      font-weight:700; display:block;
                                      margin-bottom:6px;">
                            Country *
                        </label>
                        <input type="text" name="country"
                               class="form-control"
                               style="border:1px solid var(--vv-gold);
                                      background:var(--vv-cream);
                                      border-radius:0;"
                               value="<?= htmlspecialchars(
                                   $_POST['country'] ?? 'Kenya'
                               ) ?>" required>
                    </div>
                    <button type="submit"
                            class="btn btn-vv-primary w-100 py-2">
                        Place Order →
                    </button>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-12 col-md-5">
            <div style="background:#faf6ef;
                        border:1px solid var(--vv-gold); padding:24px;">
                <h5 style="font-family:var(--font-serif);
                           font-style:italic; margin-bottom:16px;
                           color:var(--vv-dark);">
                    Order Summary
                </h5>
                <?php foreach ($cartItems as $item): ?>
                <div style="display:flex; justify-content:space-between;
                            padding:8px 0;
                            border-bottom:1px solid var(--vv-parchment);
                            font-size:0.85rem;">
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
                <div style="display:flex; justify-content:space-between;
                            padding-top:16px; font-weight:700;
                            font-size:1.1rem;">
                    <span>Total</span>
                    <span style="color:var(--vv-brown);">
                        $<?= number_format($cartTotal, 2) ?>
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require 'includes/footer.php'; ?>