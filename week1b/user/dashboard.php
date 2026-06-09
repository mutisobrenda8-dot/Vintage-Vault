<?php
$pageTitle = 'My Account';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /week1-brenda/week1b/login.php');
    exit;
}
if ($_SESSION['user_role'] === 'admin') {
    header('Location: /week1-brenda/week1b/admin/dashboard.php');
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
$activeTab = $_POST['active_tab'] ?? $_GET['tab'] ?? 'orders';

// Order tracking steps
$trackingSteps = [
    'pending'    => 1,
    'processing' => 2,
    'shipped'    => 3,
    'completed'  => 4,
    'cancelled'  => 0
];
?>

<style>
/* ── USER DASHBOARD STYLES ── */
.ud-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 40px 20px;
}

/* Profile Header */
.ud-profile-header {
    background: linear-gradient(135deg, var(--vv-dark) 0%, #5c3d28 100%);
    border-radius: 8px;
    padding: 32px;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 32px;
}
.ud-avatar {
    width: 72px; height: 72px;
    background: var(--vv-gold);
    color: var(--vv-dark);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-serif);
    font-size: 2rem;
    font-weight: 700;
    flex-shrink: 0;
}
.ud-profile-info h2 {
    font-family: var(--font-serif);
    font-style: italic;
    color: #f0e4cc;
    font-size: 1.6rem;
    margin: 0 0 4px;
}
.ud-profile-info p {
    color: var(--vv-gold);
    font-size: 0.82rem;
    letter-spacing: 1px;
    margin: 0;
}

/* Stats Row */
.ud-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}
.ud-stat {
    background: #faf6ef;
    border: 1px solid var(--vv-parchment);
    border-radius: 6px;
    padding: 20px;
    text-align: center;
    transition: transform 0.2s;
}
.ud-stat:hover { transform: translateY(-2px); }
.ud-stat-number {
    font-family: var(--font-serif);
    font-size: 1.8rem;
    color: var(--vv-dark);
    margin-bottom: 4px;
}
.ud-stat-label {
    font-size: 0.7rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--vv-muted);
}

/* Tab Navigation */
.ud-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 2px solid var(--vv-parchment);
    margin-bottom: 24px;
}
.ud-tab-btn {
    padding: 10px 20px;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    font-family: var(--font-sans);
    font-size: 0.82rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--vv-muted);
    cursor: pointer;
    transition: all 0.2s;
}
.ud-tab-btn:hover { color: var(--vv-brown); }
.ud-tab-btn.active {
    color: var(--vv-dark);
    border-bottom-color: var(--vv-brown);
    font-weight: 700;
}

/* Cards */
.ud-card {
    background: #faf6ef;
    border: 1px solid var(--vv-parchment);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 16px;
}
.ud-card-header {
    padding: 16px 20px;
    background: var(--vv-parchment);
    border-bottom: 1px solid var(--vv-gold);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ud-card-title {
    font-family: var(--font-serif);
    font-style: italic;
    color: var(--vv-dark);
    font-size: 1rem;
    margin: 0;
}

/* Order Card */
.ud-order-card {
    background: #faf6ef;
    border: 1px solid var(--vv-parchment);
    border-radius: 6px;
    margin-bottom: 16px;
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.ud-order-card:hover {
    box-shadow: 0 4px 16px rgba(61,43,31,0.08);
}
.ud-order-header {
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--vv-parchment);
    cursor: pointer;
}
.ud-order-number {
    font-family: var(--font-serif);
    font-size: 1rem;
    color: var(--vv-dark);
}
.ud-order-meta {
    font-size: 0.78rem;
    color: var(--vv-muted);
    margin-top: 2px;
}
.ud-order-body {
    padding: 20px;
    display: none;
}
.ud-order-body.open { display: block; }

/* Order Tracking */
.ud-tracking {
    margin: 16px 0 24px;
}
.ud-tracking-title {
    font-size: 0.7rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--vv-muted);
    margin-bottom: 16px;
}
.ud-tracking-steps {
    display: flex;
    align-items: center;
    gap: 0;
}
.ud-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}
.ud-step-circle {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--vv-parchment);
    border: 2px solid var(--vv-gold);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    position: relative;
    z-index: 1;
    transition: all 0.3s;
}
.ud-step.completed .ud-step-circle {
    background: var(--vv-dark);
    border-color: var(--vv-dark);
}
.ud-step.current .ud-step-circle {
    background: var(--vv-brown);
    border-color: var(--vv-brown);
    box-shadow: 0 0 0 4px rgba(122,82,48,0.2);
}
.ud-step-label {
    font-size: 0.65rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--vv-muted);
    margin-top: 8px;
    text-align: center;
}
.ud-step.completed .ud-step-label,
.ud-step.current .ud-step-label {
    color: var(--vv-brown);
    font-weight: 700;
}
.ud-step-line {
    flex: 1;
    height: 2px;
    background: var(--vv-parchment);
    margin-top: -20px;
    transition: background 0.3s;
}
.ud-step-line.completed { background: var(--vv-dark); }

/* Status Pills */
.ud-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.7rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 700;
}
.ud-status-pending    { background:#fef3cd; color:#856404; }
.ud-status-processing { background:#d4e8f0; color:#1a4a5c; }
.ud-status-shipped    { background:#e8d4f0; color:#4a1a5c; }
.ud-status-completed  { background:#d4f0e0; color:#1a5c36; }
.ud-status-cancelled  { background:#fde8e8; color:#8b1a1a; }

/* Form Inputs */
.ud-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--vv-gold);
    background: var(--vv-cream);
    color: var(--vv-dark);
    font-family: var(--font-serif);
    font-size: 0.9rem;
    border-radius: 4px;
    outline: none;
    transition: border-color 0.2s;
}
.ud-input:focus {
    border-color: var(--vv-brown);
    box-shadow: 0 0 0 3px rgba(122,82,48,0.1);
}
.ud-label {
    display: block;
    font-size: 0.7rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--vv-brown);
    margin-bottom: 6px;
    font-weight: 700;
}

/* Empty State */
.ud-empty {
    padding: 48px 20px;
    text-align: center;
    color: var(--vv-muted);
}
.ud-empty-icon { font-size: 3rem; margin-bottom: 12px; }
.ud-empty-text {
    font-family: var(--font-serif);
    font-style: italic;
    font-size: 1.1rem;
    margin-bottom: 16px;
}

@media (max-width: 768px) {
    .ud-stats { grid-template-columns: 1fr 1fr; }
    .ud-profile-header { flex-direction: column; text-align: center; }
    .ud-step-label { font-size: 0.55rem; }
}
</style>

<div class="ud-wrapper">

    <?php if ($success): ?>
        <div class="vv-alert-success" id="alertMsg"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="vv-alert-error" id="alertMsg"><?= $error ?></div>
    <?php endif; ?>

    <!-- Profile Header -->
    <div class="ud-profile-header">
        <div class="ud-avatar">
            <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>
        <div class="ud-profile-info">
            <h2><?= htmlspecialchars($user['name']) ?></h2>
            <p>
                <?= htmlspecialchars($user['email']) ?> &nbsp;·&nbsp;
                Member since <?= date('F Y', strtotime($user['created_at'])) ?>
            </p>
        </div>
    </div>

    <!-- Stats -->
    <div class="ud-stats">
        <div class="ud-stat">
            <div class="ud-stat-number"><?= count($orders) ?></div>
            <div class="ud-stat-label">Total Orders</div>
        </div>
        <div class="ud-stat">
            <div class="ud-stat-number"><?= $pendingCount ?></div>
            <div class="ud-stat-label">Pending</div>
        </div>
        <div class="ud-stat">
            <div class="ud-stat-number">
                $<?= number_format($totalSpent, 2) ?>
            </div>
            <div class="ud-stat-label">Total Spent</div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="ud-tabs">
        <button class="ud-tab-btn" data-tab="orders"
                id="btn-orders">
            📦 My Orders
            <?php if ($pendingCount > 0): ?>
                <span style="background:var(--vv-brown); color:#f0e4cc;
                             font-size:0.65rem; padding:2px 7px;
                             border-radius:10px; margin-left:6px;">
                    <?= $pendingCount ?>
                </span>
            <?php endif; ?>
        </button>
        <button class="ud-tab-btn" data-tab="cart"
                id="btn-cart">
            🛒 My Cart
            <?php if (!empty($cartItems)): ?>
                <span style="background:var(--vv-brown); color:#f0e4cc;
                             font-size:0.65rem; padding:2px 7px;
                             border-radius:10px; margin-left:6px;">
                    <?= count($cartItems) ?>
                </span>
            <?php endif; ?>
        </button>
        <button class="ud-tab-btn" data-tab="profile"
                id="btn-profile">
            👤 Edit Profile
        </button>
        <a href="/week1-brenda/week1b/shop.php"
           style="padding:10px 20px; font-size:0.82rem;
                  letter-spacing:1px; text-transform:uppercase;
                  color:var(--vv-muted); text-decoration:none;">
            🏺 Browse Shop
        </a>
        <a href="/week1-brenda/week1b/logout.php"
           style="padding:10px 20px; font-size:0.82rem;
                  letter-spacing:1px; text-transform:uppercase;
                  color:#c0522a; text-decoration:none;
                  margin-left:auto;">
            🚪 Logout
        </a>
    </div>

    <!-- ══ TAB: ORDERS ══ -->
    <div class="ud-tab-content" id="utab-orders">

        <?php if (empty($orders)): ?>
            <div class="ud-empty">
                <div class="ud-empty-icon">🏺</div>
                <div class="ud-empty-text">
                    No orders yet
                </div>
                <a href="/week1-brenda/week1b/shop.php"
                   class="btn btn-vv-primary px-4">
                    Browse the Shop
                </a>
            </div>
        <?php else: ?>

            <?php foreach ($orders as $o): ?>
            <?php
            $step       = $trackingSteps[$o['status']] ?? 0;
            $isCancelled = $o['status'] === 'cancelled';
            ?>
            <div class="ud-order-card">

                <!-- Order Header — click to expand -->
                <div class="ud-order-header"
                     onclick="toggleOrder(<?= $o['id'] ?>)">
                    <div>
                        <div class="ud-order-number">
                            Order #<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?>
                        </div>
                        <div class="ud-order-meta">
                            <?= $o['item_count'] ?> item(s) &nbsp;·&nbsp;
                            $<?= number_format($o['total'], 2) ?> &nbsp;·&nbsp;
                            <?= date('M d, Y', strtotime($o['created_at'])) ?>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span class="ud-status ud-status-<?= $o['status'] ?>">
                            <?= ucfirst($o['status']) ?>
                        </span>
                        <span id="arrow-<?= $o['id'] ?>"
                              style="color:var(--vv-muted);
                                     transition:transform 0.2s;">▼</span>
                    </div>
                </div>

                <!-- Order Body — tracking + details -->
                <div class="ud-order-body" id="order-body-<?= $o['id'] ?>">

                    <?php if (!$isCancelled): ?>
                    <!-- Order Tracking -->
                    <div class="ud-tracking">
                        <div class="ud-tracking-title">
                            Order Tracking
                        </div>
                        <div class="ud-tracking-steps">

                            <!-- Step 1: Pending -->
                            <div class="ud-step <?= $step >= 1 ? ($step == 1 ? 'current' : 'completed') : '' ?>">
                                <div class="ud-step-circle">
                                    <?= $step > 1 ? '✓' : '📋' ?>
                                </div>
                                <div class="ud-step-label">Order<br>Placed</div>
                            </div>

                            <div class="ud-step-line <?= $step > 1 ? 'completed' : '' ?>"></div>

                            <!-- Step 2: Processing -->
                            <div class="ud-step <?= $step >= 2 ? ($step == 2 ? 'current' : 'completed') : '' ?>">
                                <div class="ud-step-circle">
                                    <?= $step > 2 ? '✓' : '⚙️' ?>
                                </div>
                                <div class="ud-step-label">Processing</div>
                            </div>

                            <div class="ud-step-line <?= $step > 2 ? 'completed' : '' ?>"></div>

                            <!-- Step 3: Shipped -->
                            <div class="ud-step <?= $step >= 3 ? ($step == 3 ? 'current' : 'completed') : '' ?>">
                                <div class="ud-step-circle">
                                    <?= $step > 3 ? '✓' : '🚚' ?>
                                </div>
                                <div class="ud-step-label">Shipped</div>
                            </div>

                            <div class="ud-step-line <?= $step > 3 ? 'completed' : '' ?>"></div>

                            <!-- Step 4: Delivered -->
                            <div class="ud-step <?= $step >= 4 ? 'completed' : '' ?>">
                                <div class="ud-step-circle">
                                    <?= $step >= 4 ? '✓' : '🏠' ?>
                                </div>
                                <div class="ud-step-label">Delivered</div>
                            </div>

                        </div>
                    </div>
                    <?php else: ?>
                    <div style="background:#fde8e8; border-left:3px solid #c0522a;
                                padding:12px 16px; margin-bottom:16px;
                                font-size:0.85rem; color:#8b1a1a;">
                        This order was cancelled.
                    </div>
                    <?php endif; ?>

                    <!-- Order Items -->
                    <?php
                    $items = $pdo->prepare("
                        SELECT oi.*, p.name AS product_name
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ?
                    ");
                    $items->execute([$o['id']]);
                    $orderItems = $items->fetchAll();
                    ?>

                    <?php if (!empty($orderItems)): ?>
                    <div style="border-top:1px solid var(--vv-parchment);
                                padding-top:16px;">
                        <div style="font-size:0.7rem; letter-spacing:2px;
                                    text-transform:uppercase;
                                    color:var(--vv-muted); margin-bottom:12px;">
                            Items Ordered
                        </div>
                        <?php foreach ($orderItems as $item): ?>
                        <div style="display:flex; justify-content:space-between;
                                    align-items:center; padding:10px 0;
                                    border-bottom:1px solid #f5f0e8;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:40px; height:40px;
                                            background:var(--vv-parchment);
                                            border-radius:4px; display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            font-size:1.2rem;">
                                    🏺
                                </div>
                                <div>
                                    <div style="font-family:var(--font-serif);
                                                color:var(--vv-dark);
                                                font-size:0.9rem;">
                                        <?= htmlspecialchars($item['product_name']) ?>
                                    </div>
                                    <div style="font-size:0.75rem;
                                                color:var(--vv-muted);">
                                        Qty: <?= $item['quantity'] ?>
                                    </div>
                                </div>
                            </div>
                            <div style="font-weight:700; color:var(--vv-brown);">
                                $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Order Total -->
                        <div style="display:flex; justify-content:space-between;
                                    padding-top:12px; font-weight:700;
                                    font-size:1rem;">
                            <span>Total</span>
                            <span style="color:var(--vv-brown);">
                                $<?= number_format($o['total'], 2) ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

    <!-- ══ TAB: CART ══ -->
    <div class="ud-tab-content" id="utab-cart" style="display:none;">
        <div class="ud-card">
            <div class="ud-card-header">
                <h5 class="ud-card-title">My Cart</h5>
                <?php if (!empty($cartItems)): ?>
                    <a href="/week1-brenda/week1b/checkout.php"
                       class="btn btn-vv-primary">
                        Checkout →
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($cartItems)): ?>
                <div class="ud-empty">
                    <div class="ud-empty-icon">🛒</div>
                    <div class="ud-empty-text">Your cart is empty</div>
                    <a href="/week1-brenda/week1b/shop.php"
                       class="btn btn-vv-primary">
                        Start Shopping
                    </a>
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
                        <div style="font-size:0.78rem; color:var(--vv-muted);">
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
                    <a href="/week1-brenda/week1b/checkout.php"
                       class="btn btn-vv-primary w-100 py-2">
                        Proceed to Checkout →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ TAB: PROFILE ══ -->
    <div class="ud-tab-content" id="utab-profile" style="display:none;">
        <div class="ud-card">
            <div class="ud-card-header">
                <h5 class="ud-card-title">Edit Profile</h5>
            </div>
            <div style="padding:28px;">
                <form method="POST">
                    <input type="hidden" name="action"
                           value="update_profile">
                    <input type="hidden" name="active_tab"
                           value="profile">

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="ud-label">Full Name</label>
                            <input type="text" name="name"
                                   class="ud-input"
                                   value="<?= htmlspecialchars($user['name']) ?>"
                                   required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ud-label">Email Address</label>
                            <input type="email" name="email"
                                   class="ud-input"
                                   value="<?= htmlspecialchars($user['email']) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ud-label">New Password</label>
                            <input type="password" name="password"
                                   class="ud-input"
                                   placeholder="Leave blank to keep current">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ud-label">
                                Confirm Password
                            </label>
                            <input type="password" name="confirm"
                                   class="ud-input"
                                   placeholder="Repeat new password">
                        </div>
                    </div>

                    <button type="submit"
                            class="btn btn-vv-primary px-5 py-2">
                        💾 Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
// Tab switching
const allTabs = document.querySelectorAll('.ud-tab-content');
const allBtns = document.querySelectorAll('.ud-tab-btn');

function switchTab(name) {
    allTabs.forEach(t => t.style.display = 'none');
    allBtns.forEach(b => b.classList.remove('active'));
    const target = document.getElementById('utab-' + name);
    const btn    = document.getElementById('btn-' + name);
    if (target) target.style.display = 'block';
    if (btn)    btn.classList.add('active');
    history.replaceState(null, '', `?tab=${name}`);
}

allBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        switchTab(btn.dataset.tab);
    });
});

const urlTab = new URLSearchParams(
    window.location.search
).get('tab');
switchTab(urlTab || '<?= $activeTab ?>');

// Toggle order details
function toggleOrder(id) {
    const body  = document.getElementById('order-body-' + id);
    const arrow = document.getElementById('arrow-' + id);
    const isOpen = body.classList.contains('open');
    body.classList.toggle('open');
    arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}

// Auto hide alerts
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