<?php
$pageTitle = 'My Account';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /week1-brenda/week3b/login.php');
    exit;
}
if ($_SESSION['user_role'] === 'admin') {
    header('Location: /week1-brenda/week3b/admin/dashboard.php');
    exit;
}

require '../db.php';
require '../includes/header.php';

$success = $error = '';

// UPDATE PROFILE
if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif ($password && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password && $password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare(
                "UPDATE users SET name=?, email=?, password=?
                 WHERE id=?"
            )->execute([
                $name, $email, $hashed, $_SESSION['user_id']
            ]);
        } else {
            $pdo->prepare(
                "UPDATE users SET name=?, email=? WHERE id=?"
            )->execute([$name, $email, $_SESSION['user_id']]);
        }
        $_SESSION['user_name'] = $name;
        $success = 'Profile updated successfully!';
    }
}

// FETCH DATA
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT c.*, p.name AS product_name, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

$cartTotal    = array_sum(
    array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems)
);
$totalSpent   = array_sum(array_column($orders, 'total'));
$pendingCount = count(array_filter(
    $orders, fn($o) => $o['status'] === 'pending'
));
$activeTab    = $_POST['active_tab'] ?? $_GET['tab'] ?? 'orders';
?>

<div class="container py-5">
<div class="row g-4">

    <!-- SIDEBAR -->
    <div class="col-12 col-md-3">
        <div class="vv-user-sidebar">
            <div class="vv-user-avatar">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div class="vv-user-name">
                <?= htmlspecialchars($user['name']) ?>
            </div>
            <div class="vv-user-email">
                <?= htmlspecialchars($user['email']) ?>
            </div>
            <div class="vv-user-since">
                Member since
                <?= date('M Y', strtotime($user['created_at'])) ?>
            </div>
            <div class="vv-sidebar-divider"></div>
            <nav class="vv-user-nav">
                <button class="vv-user-nav-link w-100 text-start
                               border-0 bg-transparent"
                        onclick="switchTab('orders')"
                        id="btn-orders">
                    📦 My Orders
                    <?php if ($pendingCount > 0): ?>
                        <span style="float:right; background:var(--vv-brown);
                                     color:#f0e4cc; font-size:0.65rem;
                                     padding:2px 7px; border-radius:10px;">
                            <?= $pendingCount ?>
                        </span>
                    <?php endif; ?>
                </button>
                <button class="vv-user-nav-link w-100 text-start
                               border-0 bg-transparent"
                        onclick="switchTab('cart')"
                        id="btn-cart">
                    🛒 My Cart
                    <?php if (!empty($cartItems)): ?>
                        <span style="float:right; background:var(--vv-brown);
                                     color:#f0e4cc; font-size:0.65rem;
                                     padding:2px 7px; border-radius:10px;">
                            <?= count($cartItems) ?>
                        </span>
                    <?php endif; ?>
                </button>
                <button class="vv-user-nav-link w-100 text-start
                               border-0 bg-transparent"
                        onclick="switchTab('profile')"
                        id="btn-profile">
                    👤 Edit Profile
                </button>
                <a href="/week1-brenda/week3b/shop.php" class="vv-user-nav-link">
                    🏺 Browse Shop
                </a>
                <a href="/week1-brenda/week3b/logout.php"
                   class="vv-user-nav-link vv-logout-link">
                    🚪 Logout
                </a>
            </nav>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="col-12 col-md-9">

        <?php if ($success): ?>
            <div class="vv-alert-success" id="alertMsg">
                <?= $success ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="vv-alert-error" id="alertMsg">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="vv-user-stat">
                    <div class="vv-user-stat-number">
                        <?= count($orders) ?>
                    </div>
                    <div class="vv-user-stat-label">Orders</div>
                </div>
            </div>
            <div class="col-4">
                <div class="vv-user-stat">
                    <div class="vv-user-stat-number">
                        <?= $pendingCount ?>
                    </div>
                    <div class="vv-user-stat-label">Pending</div>
                </div>
            </div>
            <div class="col-4">
                <div class="vv-user-stat">
                    <div class="vv-user-stat-number">
                        $<?= number_format($totalSpent, 2) ?>
                    </div>
                    <div class="vv-user-stat-label">Spent</div>
                </div>
            </div>
        </div>

        <!-- TAB: ORDERS -->
        <div class="vv-tab-user" id="utab-orders">
            <div class="vv-user-card">
                <div class="vv-user-card-header">My Orders</div>
                <?php if (empty($orders)): ?>
                    <div style="padding:40px; text-align:center;
                                color:var(--vv-muted);">
                        <p style="font-size:3rem; margin-bottom:12px;">🏺</p>
                        <p style="margin-bottom:16px;">No orders yet.</p>
                        <a href="/week1-brenda/week3b/shop.php"
                           class="btn btn-vv-primary">Browse Shop</a>
                    </div>
                <?php else: ?>
                <table style="width:100%; border-collapse:collapse;
                              font-size:0.85rem;">
                    <thead>
                        <tr style="background:var(--vv-parchment);">
                            <th style="padding:10px 16px; text-align:left;
                                       font-size:0.68rem; letter-spacing:2px;
                                       text-transform:uppercase;
                                       color:var(--vv-muted);">Order</th>
                            <th style="padding:10px 16px; text-align:left;
                                       font-size:0.68rem; letter-spacing:2px;
                                       text-transform:uppercase;
                                       color:var(--vv-muted);">Items</th>
                            <th style="padding:10px 16px; text-align:left;
                                       font-size:0.68rem; letter-spacing:2px;
                                       text-transform:uppercase;
                                       color:var(--vv-muted);">Total</th>
                            <th style="padding:10px 16px; text-align:left;
                                       font-size:0.68rem; letter-spacing:2px;
                                       text-transform:uppercase;
                                       color:var(--vv-muted);">Status</th>
                            <th style="padding:10px 16px; text-align:left;
                                       font-size:0.68rem; letter-spacing:2px;
                                       text-transform:uppercase;
                                       color:var(--vv-muted);">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr style="border-bottom:1px solid #f0e8d8;">
                            <td style="padding:12px 16px;">
                                #<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?>
                            </td>
                            <td style="padding:12px 16px;">
                                <?= $o['item_count'] ?> item(s)
                            </td>
                            <td style="padding:12px 16px;">
                                $<?= number_format($o['total'], 2) ?>
                            </td>
                            <td style="padding:12px 16px;">
                                <span style="padding:3px 10px;
                                             border-radius:12px;
                                             font-size:0.68rem;
                                             letter-spacing:1px;
                                             text-transform:uppercase;
                                             background:<?= $o['status']==='completed'
                                                 ? '#d4f0e0' : ($o['status']==='pending'
                                                 ? '#fef3cd' : '#d4e8f0') ?>;
                                             color:<?= $o['status']==='completed'
                                                 ? '#1a5c36' : ($o['status']==='pending'
                                                 ? '#856404' : '#1a4a5c') ?>;">
                                    <?= ucfirst($o['status']) ?>
                                </span>
                            </td>
                            <td style="padding:12px 16px;">
                                <?= date('M d, Y',
                                    strtotime($o['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- TAB: CART -->
        <div class="vv-tab-user" id="utab-cart" style="display:none;">
            <div class="vv-user-card">
                <div class="vv-user-card-header">My Cart</div>
                <?php if (empty($cartItems)): ?>
                    <div style="padding:40px; text-align:center;
                                color:var(--vv-muted);">
                        <p style="font-size:3rem; margin-bottom:12px;">🛒</p>
                        <p style="margin-bottom:16px;">Your cart is empty.</p>
                        <a href="/week1-brenda/week3b/shop.php"
                           class="btn btn-vv-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                    <div style="display:flex; align-items:center; gap:16px;
                                padding:16px 20px;
                                border-bottom:1px solid var(--vv-parchment);">
                        <div style="font-size:2rem; width:48px;
                                    text-align:center;">🏺</div>
                        <div style="flex:1;">
                            <div style="font-family:var(--font-serif);
                                        color:var(--vv-dark);">
                                <?= htmlspecialchars($item['product_name']) ?>
                            </div>
                            <div style="font-size:0.78rem;
                                        color:var(--vv-muted);">
                                Qty: <?= $item['quantity'] ?> ×
                                $<?= number_format($item['price'], 2) ?>
                            </div>
                        </div>
                        <div style="font-weight:700; color:var(--vv-brown);">
                            $<?= number_format(
                                $item['price'] * $item['quantity'], 2
                            ) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div style="display:flex; justify-content:space-between;
                                padding:16px 20px; background:var(--vv-dark);">
                        <span style="font-size:0.8rem; letter-spacing:2px;
                                     text-transform:uppercase;
                                     color:var(--vv-gold);">Total</span>
                        <span style="font-size:1.4rem; color:#f0e4cc;
                                     font-family:var(--font-serif);">
                            $<?= number_format($cartTotal, 2) ?>
                        </span>
                    </div>
                    <div style="padding:16px 20px;">
                        <a href="/week1-brenda/week3b/checkout.php"
                           class="btn btn-vv-primary w-100 py-2">
                            Proceed to Checkout →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TAB: PROFILE -->
        <div class="vv-tab-user" id="utab-profile" style="display:none;">
            <div class="vv-user-card">
                <div class="vv-user-card-header">Edit Profile</div>
                <div style="padding:24px;">
                    <form method="POST">
                        <input type="hidden" name="action"
                               value="update_profile">
                        <input type="hidden" name="active_tab"
                               value="profile">
                        <div class="mb-3">
                            <label class="form-label"
                                   style="font-size:0.7rem; letter-spacing:2px;
                                          text-transform:uppercase;
                                          color:var(--vv-brown);
                                          font-weight:700;">
                                Full Name
                            </label>
                            <input type="text" name="name"
                                   class="form-control"
                                   style="border:1px solid var(--vv-gold);
                                          background:var(--vv-cream);
                                          border-radius:0;"
                                   value="<?= htmlspecialchars($user['name']) ?>"
                                   required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"
                                   style="font-size:0.7rem; letter-spacing:2px;
                                          text-transform:uppercase;
                                          color:var(--vv-brown);
                                          font-weight:700;">
                                Email Address
                            </label>
                            <input type="email" name="email"
                                   class="form-control"
                                   style="border:1px solid var(--vv-gold);
                                          background:var(--vv-cream);
                                          border-radius:0;"
                                   value="<?= htmlspecialchars($user['email']) ?>"
                                   required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"
                                   style="font-size:0.7rem; letter-spacing:2px;
                                          text-transform:uppercase;
                                          color:var(--vv-brown);
                                          font-weight:700;">
                                New Password
                            </label>
                            <input type="password" name="password"
                                   class="form-control"
                                   style="border:1px solid var(--vv-gold);
                                          background:var(--vv-cream);
                                          border-radius:0;"
                                   placeholder="Leave blank to keep current">
                        </div>
                        <div class="mb-4">
                            <label class="form-label"
                                   style="font-size:0.7rem; letter-spacing:2px;
                                          text-transform:uppercase;
                                          color:var(--vv-brown);
                                          font-weight:700;">
                                Confirm Password
                            </label>
                            <input type="password" name="confirm"
                                   class="form-control"
                                   style="border:1px solid var(--vv-gold);
                                          background:var(--vv-cream);
                                          border-radius:0;"
                                   placeholder="Repeat new password">
                        </div>
                        <button type="submit"
                                class="btn btn-vv-primary px-4 py-2">
                            💾 Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

<script>
const allTabs = document.querySelectorAll('.vv-tab-user');

function switchTab(name) {
    allTabs.forEach(t => t.style.display = 'none');
    const target = document.getElementById('utab-' + name);
    if (target) target.style.display = 'block';
    ['orders','cart','profile'].forEach(n => {
        const btn = document.getElementById('btn-' + n);
        if (btn) btn.classList.toggle('active', n === name);
    });
    history.replaceState(null, '', `?tab=${name}`);
}

const urlTab = new URLSearchParams(window.location.search).get('tab');
switchTab(urlTab || '<?= $activeTab ?>');

const alertMsg = document.getElementById('alertMsg');
if (alertMsg) {
    setTimeout(() => {
        alertMsg.style.transition = 'opacity 0.5s';
        alertMsg.style.opacity    = '0';
        setTimeout(() => alertMsg.remove(), 500);
    }, 4000);
}
</script>

<?php require '../includes/footer.php'; ?>