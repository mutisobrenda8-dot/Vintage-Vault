<?php
$pageTitle = 'Dashboard';
require '../db.php';
require 'includes/admin_header.php';

$success = $error = '';

// ADD PRODUCT
if (isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = trim($_POST['price']);
    $stock       = trim($_POST['stock']);
    $category_id = $_POST['category_id'];
    $image       = 'placeholder.jpg';

    if (empty($name) || empty($price) || empty($category_id)) {
        $error = 'Name, price and category are required.';
    } else {
        if (!empty($_FILES['image']['name'])) {
            $allowed    = ['jpg','jpeg','png','webp'];
            $ext        = strtolower(pathinfo(
                $_FILES['image']['name'], PATHINFO_EXTENSION
            ));
            $upload_dir = '../images/products/';
            if (in_array($ext, $allowed)) {
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $image = time().'_'.basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir.$image);
            }
        }
        $pdo->prepare(
            "INSERT INTO products (category_id,name,description,price,stock,image)
             VALUES (?,?,?,?,?,?)"
        )->execute([$category_id,$name,$description,$price,$stock,$image]);
        $success = 'Product added successfully!';
    }
}

// EDIT PRODUCT
if (isset($_POST['action']) && $_POST['action'] === 'edit_product') {
    $id          = $_POST['product_id'];
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = trim($_POST['price']);
    $stock       = trim($_POST['stock']);
    $category_id = $_POST['category_id'];

    $s = $pdo->prepare("SELECT image FROM products WHERE id=?");
    $s->execute([$id]);
    $image = $s->fetchColumn();

    if (!empty($_FILES['image']['name'])) {
        $allowed    = ['jpg','jpeg','png','webp'];
        $ext        = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $upload_dir = '../images/products/';
        if (in_array($ext, $allowed)) {
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $image = time().'_'.basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir.$image);
        }
    }

    $pdo->prepare(
        "UPDATE products SET category_id=?,name=?,description=?,price=?,stock=?,image=? WHERE id=?"
    )->execute([$category_id,$name,$description,$price,$stock,$image,$id]);
    $success = 'Product updated!';
}

// DELETE PRODUCT
if (isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$_POST['product_id']]);
    $success = 'Product deleted.';
}

// UPDATE ORDER STATUS
if (isset($_POST['action']) && $_POST['action'] === 'update_order') {
    $allowed = ['pending','processing','shipped','completed','cancelled'];
    if (in_array($_POST['status'], $allowed)) {
        $pdo->prepare("UPDATE orders SET status=? WHERE id=?")
            ->execute([$_POST['status'], $_POST['order_id']]);
        $success = 'Order status updated!';
    }
}

// DELETE REVIEW
if (isset($_POST['action']) && $_POST['action'] === 'delete_review') {
    $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$_POST['review_id']]);
    $success = 'Review deleted.';
}

// ADD CATEGORY
if (isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = trim($_POST['cat_name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    if (!empty($name)) {
        $pdo->prepare("INSERT INTO categories (name,slug) VALUES (?,?)")->execute([$name, $slug]);
        $success = 'Category added!';
    }
}

// DELETE CATEGORY
if (isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$_POST['category_id']]);
    $success = 'Category deleted.';
}

// FETCH ALL DATA
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$lowStock      = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 2 AND stock > 0")->fetchColumn();

$products = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll();

$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id ORDER BY c.name
")->fetchAll();

$orders = $pdo->query("
    SELECT o.*, u.name AS customer_name, u.email AS customer_email
    FROM orders o JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
")->fetchAll();

$users = $pdo->query("
    SELECT u.*, COUNT(o.id) AS order_count,
           COALESCE(SUM(o.total),0) AS total_spent
    FROM users u LEFT JOIN orders o ON u.id = o.user_id
    GROUP BY u.id ORDER BY u.created_at DESC
")->fetchAll();

$reviews = $pdo->query("
    SELECT r.*, u.name AS user_name, p.name AS product_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN products p ON r.product_id = p.id
    ORDER BY r.created_at DESC
")->fetchAll();

$activeTab = $_POST['active_tab'] ?? $_GET['tab'] ?? 'overview';

// Monthly revenue for chart (last 6 months)
$monthlyRevenue = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%b') AS month,
           COALESCE(SUM(total), 0) AS revenue
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    AND status != 'cancelled'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY created_at ASC
")->fetchAll();
?>

<style>
/* ── ADMIN DASHBOARD STYLES ── */
.ad-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
.ad-stat {
    background: #faf6ef;
    border: 1px solid var(--vv-parchment);
    border-radius: 6px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.ad-stat:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(61,43,31,0.1);
}
.ad-stat::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--vv-brown);
    transform: scaleX(0);
    transition: transform 0.3s;
}
.ad-stat:hover::after { transform: scaleX(1); }
.ad-stat-icon { font-size: 1.8rem; margin-bottom: 8px; display: block; }
.ad-stat-number {
    font-family: var(--font-serif);
    font-size: 2rem;
    color: var(--vv-dark);
    line-height: 1;
    margin-bottom: 4px;
}
.ad-stat-label {
    font-size: 0.68rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--vv-muted);
}
.ad-stat-sub {
    font-size: 0.72rem;
    margin-top: 6px;
    color: var(--vv-brown);
}

/* Quick Actions */
.ad-quick-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 28px;
}
.ad-action-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: var(--vv-dark);
    color: var(--vv-gold);
    border: none;
    font-size: 0.78rem;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    border-radius: 4px;
}
.ad-action-btn:hover {
    background: var(--vv-brown);
    color: #fff;
    transform: translateY(-1px);
}
.ad-action-btn.secondary {
    background: var(--vv-parchment);
    color: var(--vv-brown);
    border: 1px solid var(--vv-gold);
}
.ad-action-btn.secondary:hover {
    background: var(--vv-brown);
    color: #fff;
}

/* Cards */
.ad-card {
    background: #faf6ef;
    border: 1px solid var(--vv-parchment);
    border-radius: 6px;
    margin-bottom: 24px;
    overflow: hidden;
}
.ad-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: var(--vv-parchment);
    border-bottom: 1px solid var(--vv-gold);
}
.ad-card-title {
    font-family: var(--font-serif);
    font-style: italic;
    color: var(--vv-dark);
    font-size: 1rem;
    margin: 0;
}

/* Tables */
.ad-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.ad-table thead th {
    padding: 12px 16px;
    text-align: left;
    font-size: 0.68rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--vv-muted);
    border-bottom: 1px solid var(--vv-parchment);
    font-weight: 700;
    white-space: nowrap;
}
.ad-table tbody tr {
    border-bottom: 1px solid #f0e8d8;
    transition: background 0.15s;
}
.ad-table tbody tr:hover { background: var(--vv-parchment); }
.ad-table tbody td {
    padding: 12px 16px;
    color: var(--vv-text);
    vertical-align: middle;
}

/* Status Pills */
.ad-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.68rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 700;
}
.ad-pill-active    { background:#d4f0e0; color:#1a5c36; }
.ad-pill-pending   { background:#fef3cd; color:#856404; }
.ad-pill-processing{ background:#d4e8f0; color:#1a4a5c; }
.ad-pill-shipped   { background:#e8d4f0; color:#4a1a5c; }
.ad-pill-completed { background:#d4f0e0; color:#1a5c36; }
.ad-pill-cancelled { background:#fde8e8; color:#8b1a1a; }
.ad-pill-sold      { background:#fde8d8; color:#8b3a1a; }
.ad-pill-admin     { background:#d4e8f0; color:#1a4a5c; }
.ad-pill-low       { background:#fef3cd; color:#856404; }

/* Action Buttons */
.ad-btn {
    padding: 4px 10px;
    font-size: 0.72rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
    border-radius: 3px;
}
.ad-btn-edit   { background:var(--vv-parchment); color:var(--vv-brown); border:1px solid var(--vv-gold); }
.ad-btn-delete { background:#fde8e8; color:#8b1a1a; border:1px solid #f0b8b8; }
.ad-btn-edit:hover   { background:var(--vv-brown); color:#fff; }
.ad-btn-delete:hover { background:#c0522a; color:#fff; }

/* Form Inputs */
.ad-input {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--vv-gold);
    background: var(--vv-cream);
    color: var(--vv-dark);
    font-family: var(--font-serif);
    font-size: 0.88rem;
    border-radius: 4px;
    outline: none;
    transition: border-color 0.2s;
}
.ad-input:focus {
    border-color: var(--vv-brown);
    box-shadow: 0 0 0 3px rgba(122,82,48,0.1);
}
.ad-label {
    display: block;
    font-size: 0.68rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--vv-brown);
    margin-bottom: 5px;
    font-weight: 700;
}

/* Order Expand */
.ad-order-row { cursor: pointer; }
.ad-order-detail {
    display: none;
    background: #f5f0e8;
}
.ad-order-detail.open { display: table-row; }

/* Low Stock Alert */
.ad-alert-low {
    background: #fef3cd;
    border-left: 3px solid #e08c1a;
    color: #856404;
    padding: 12px 16px;
    font-size: 0.85rem;
    border-radius: 4px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Revenue Chart */
.ad-chart-bar {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    height: 120px;
    padding: 16px 20px;
}
.ad-bar-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    height: 100%;
    justify-content: flex-end;
}
.ad-bar {
    width: 100%;
    background: var(--vv-brown);
    border-radius: 3px 3px 0 0;
    transition: height 0.8s ease;
    min-height: 4px;
}
.ad-bar-label {
    font-size: 0.68rem;
    color: var(--vv-muted);
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* Modal */
.ad-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.ad-modal.open { display: flex; }
.ad-modal-content {
    background: #faf6ef;
    border: 1px solid var(--vv-gold);
    padding: 28px;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 6px;
    position: relative;
}
.ad-modal-close {
    position: absolute;
    top: 12px; right: 16px;
    background: none;
    border: none;
    font-size: 1.4rem;
    cursor: pointer;
    color: var(--vv-muted);
}

@media (max-width: 900px) {
    .ad-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 500px) {
    .ad-stats { grid-template-columns: 1fr 1fr; }
}
</style>

<?php if ($success): ?>
    <div class="vv-alert-success" id="alertMsg"><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="vv-alert-error" id="alertMsg"><?= $error ?></div>
<?php endif; ?>

<!-- TAB NAV -->
<div class="vv-tab-nav">
    <button class="vv-tab-btn" data-tab="overview">📊 Overview</button>
    <button class="vv-tab-btn" data-tab="products">🏺 Products</button>
    <button class="vv-tab-btn" data-tab="orders">🛒 Orders</button>
    <button class="vv-tab-btn" data-tab="users">👥 Users</button>
    <button class="vv-tab-btn" data-tab="reviews">⭐ Reviews</button>
    <button class="vv-tab-btn" data-tab="categories">🗂️ Categories</button>
</div>

<!-- ══════════════════════════════════════
     OVERVIEW TAB
══════════════════════════════════════ -->
<div class="vv-tab-content" id="tab-overview">

    <div class="admin-page-title">Dashboard</div>
    <div class="admin-page-subtitle">
        Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>.
        Here's your store at a glance.
    </div>

    <?php if ($lowStock > 0): ?>
    <div class="ad-alert-low">
        ⚠️ <strong><?= $lowStock ?> product(s)</strong>
        are running low on stock. Check the Products tab.
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="ad-stats">
        <div class="ad-stat">
            <span class="ad-stat-icon">🏺</span>
            <div class="ad-stat-number"
                 data-target="<?= $totalProducts ?>">0</div>
            <div class="ad-stat-label">Products</div>
            <?php if ($lowStock > 0): ?>
                <div class="ad-stat-sub">⚠️ <?= $lowStock ?> low stock</div>
            <?php endif; ?>
        </div>
        <div class="ad-stat">
            <span class="ad-stat-icon">🛒</span>
            <div class="ad-stat-number"
                 data-target="<?= $totalOrders ?>">0</div>
            <div class="ad-stat-label">Total Orders</div>
            <div class="ad-stat-sub">
                🕐 <?= $pendingOrders ?> pending
            </div>
        </div>
        <div class="ad-stat">
            <span class="ad-stat-icon">💰</span>
            <div class="ad-stat-number"
                 data-target="<?= intval($totalRevenue) ?>"
                 data-prefix="$">$0</div>
            <div class="ad-stat-label">Revenue</div>
            <div class="ad-stat-sub">Excluding cancelled</div>
        </div>
        <div class="ad-stat">
            <span class="ad-stat-icon">👥</span>
            <div class="ad-stat-number"
                 data-target="<?= $totalUsers ?>">0</div>
            <div class="ad-stat-label">Customers</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="ad-quick-actions">
        <button class="ad-action-btn"
                onclick="switchTab('products')">
            ➕ Add Product
        </button>
        <button class="ad-action-btn"
                onclick="switchTab('orders')">
            🛒 View Orders
        </button>
        <button class="ad-action-btn secondary"
                onclick="switchTab('users')">
            👥 View Users
        </button>
        <a href="/vintage-vault/week6b/index.php"
           class="ad-action-btn secondary" target="_blank">
            🌐 View Store
        </a>
    </div>

    <!-- Revenue Chart -->
    <?php if (!empty($monthlyRevenue)): ?>
    <div class="ad-card" style="margin-bottom:24px;">
        <div class="ad-card-header">
            <h5 class="ad-card-title">Revenue — Last 6 Months</h5>
        </div>
        <?php
        $maxRev = max(array_column($monthlyRevenue, 'revenue'));
        $maxRev = $maxRev > 0 ? $maxRev : 1;
        ?>
        <div class="ad-chart-bar">
            <?php foreach ($monthlyRevenue as $m): ?>
            <div class="ad-bar-wrap">
                <div class="ad-bar"
                     style="height:<?= round(($m['revenue']/$maxRev)*100) ?>%"
                     title="$<?= number_format($m['revenue'],2) ?>">
                </div>
                <div class="ad-bar-label"><?= $m['month'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Orders -->
    <div class="ad-card">
        <div class="ad-card-header">
            <h5 class="ad-card-title">Recent Orders</h5>
            <button class="ad-action-btn"
                    onclick="switchTab('orders')"
                    style="padding:5px 12px; font-size:0.72rem;">
                View all
            </button>
        </div>
        <?php if (empty($orders)): ?>
            <p style="padding:20px; color:var(--vv-muted);">
                No orders yet.
            </p>
        <?php else: ?>
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($orders,0,5) as $o): ?>
                <tr>
                    <td>#<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td>$<?= number_format($o['total'],2) ?></td>
                    <td>
                        <span class="ad-pill ad-pill-<?= $o['status'] ?>">
                            <?= ucfirst($o['status']) ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Recent Products -->
    <div class="ad-card">
        <div class="ad-card-header">
            <h5 class="ad-card-title">Recent Products</h5>
            <button class="ad-action-btn"
                    onclick="switchTab('products')"
                    style="padding:5px 12px; font-size:0.72rem;">
                View all
            </button>
        </div>
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($products,0,5) as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                    <td>$<?= number_format($p['price'],2) ?></td>
                    <td>
                        <?php if ($p['stock'] <= 2 && $p['stock'] > 0): ?>
                            <span class="ad-pill ad-pill-low">
                                <?= $p['stock'] ?> left
                            </span>
                        <?php else: ?>
                            <?= $p['stock'] ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="ad-pill <?= $p['stock']>0 ? 'ad-pill-active':'ad-pill-sold' ?>">
                            <?= $p['stock']>0 ? 'In Stock':'Sold Out' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- ══════════════════════════════════════
     PRODUCTS TAB
══════════════════════════════════════ -->
<div class="vv-tab-content" id="tab-products" style="display:none;">

    <div class="admin-page-title">Products</div>
    <div class="admin-page-subtitle">Manage your vintage inventory</div>

    <div class="ad-quick-actions">
        <button class="ad-action-btn" onclick="openModal('addProductModal')">
            ➕ Add New Product
        </button>
    </div>

    <?php if ($lowStock > 0): ?>
    <div class="ad-alert-low">
        ⚠️ <?= $lowStock ?> product(s) have 2 or fewer items left in stock!
    </div>
    <?php endif; ?>

    <div class="ad-card">
        <div class="ad-card-header">
            <h5 class="ad-card-title">
                All Products (<?= count($products) ?>)
            </h5>
        </div>
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <div style="font-weight:500;">
                            <?= htmlspecialchars($p['name']) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                    <td>$<?= number_format($p['price'],2) ?></td>
                    <td>
                        <?php if ($p['stock'] == 0): ?>
                            <span class="ad-pill ad-pill-sold">0</span>
                        <?php elseif ($p['stock'] <= 2): ?>
                            <span class="ad-pill ad-pill-low">
                                <?= $p['stock'] ?> ⚠️
                            </span>
                        <?php else: ?>
                            <span style="color:#2d7a4f; font-weight:600;">
                                <?= $p['stock'] ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="ad-pill <?= $p['stock']>0 ? 'ad-pill-active':'ad-pill-sold' ?>">
                            <?= $p['stock']>0 ? 'In Stock':'Sold Out' ?>
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <button class="ad-btn ad-btn-edit"
                                onclick="openEditProduct(
                                    <?= $p['id'] ?>,
                                    '<?= addslashes($p['name']) ?>',
                                    '<?= addslashes($p['description']) ?>',
                                    <?= $p['price'] ?>,
                                    <?= $p['stock'] ?>,
                                    <?= $p['category_id'] ?>
                                )">✏️ Edit</button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="active_tab" value="products">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="ad-btn ad-btn-delete"
                                    onclick="return confirm('Delete this product?')">
                                🗑️ Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ADD PRODUCT MODAL -->
    <div class="ad-modal" id="addProductModal">
        <div class="ad-modal-content">
            <button class="ad-modal-close"
                    onclick="closeModal('addProductModal')">✕</button>
            <h3 style="font-family:var(--font-serif); font-style:italic;
                       margin-bottom:20px; color:var(--vv-dark);">
                Add New Product
            </h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_product">
                <input type="hidden" name="active_tab" value="products">

                <div class="mb-3">
                    <label class="ad-label">Product Name *</label>
                    <input type="text" name="name" class="ad-input"
                           placeholder="e.g. Olympus OM-1, 1973" required>
                </div>
                <div class="mb-3">
                    <label class="ad-label">Category *</label>
                    <select name="category_id" class="ad-input" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="ad-label">Price ($) *</label>
                        <input type="number" name="price" class="ad-input"
                               step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <div class="col-6">
                        <label class="ad-label">Stock *</label>
                        <input type="number" name="stock" class="ad-input"
                               min="0" value="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="ad-label">Description</label>
                    <textarea name="description" class="ad-input" rows="3"
                              style="resize:vertical;"
                              placeholder="Describe the item..."></textarea>
                </div>
                <div class="mb-4">
                    <label class="ad-label">Product Image</label>
                    <input type="file" name="image" class="ad-input"
                           accept="image/*">
                    <p style="font-size:0.72rem; color:var(--vv-muted); margin-top:4px;">
                        JPG, PNG or WEBP. Leave empty for placeholder.
                    </p>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="ad-action-btn">
                        ➕ Add Product
                    </button>
                    <button type="button"
                            class="ad-action-btn secondary"
                            onclick="closeModal('addProductModal')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT PRODUCT MODAL -->
    <div class="ad-modal" id="editProductModal">
        <div class="ad-modal-content">
            <button class="ad-modal-close"
                    onclick="closeModal('editProductModal')">✕</button>
            <h3 style="font-family:var(--font-serif); font-style:italic;
                       margin-bottom:20px; color:var(--vv-dark);">
                Edit Product
            </h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="active_tab" value="products">
                <input type="hidden" name="product_id" id="edit_id">

                <div class="mb-3">
                    <label class="ad-label">Product Name *</label>
                    <input type="text" name="name" id="edit_name"
                           class="ad-input" required>
                </div>
                <div class="mb-3">
                    <label class="ad-label">Category *</label>
                    <select name="category_id" id="edit_cat"
                            class="ad-input" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="ad-label">Price ($) *</label>
                        <input type="number" name="price" id="edit_price"
                               class="ad-input" step="0.01" min="0" required>
                    </div>
                    <div class="col-6">
                        <label class="ad-label">Stock</label>
                        <input type="number" name="stock" id="edit_stock"
                               class="ad-input" min="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="ad-label">Description</label>
                    <textarea name="description" id="edit_desc"
                              class="ad-input" rows="3"
                              style="resize:vertical;"></textarea>
                </div>
                <div class="mb-4">
                    <label class="ad-label">New Image (optional)</label>
                    <input type="file" name="image" class="ad-input"
                           accept="image/*">
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="ad-action-btn">
                        💾 Save Changes
                    </button>
                    <button type="button"
                            class="ad-action-btn secondary"
                            onclick="closeModal('editProductModal')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════════
     ORDERS TAB
══════════════════════════════════════ -->
<div class="vv-tab-content" id="tab-orders" style="display:none;">

    <div class="admin-page-title">Orders</div>
    <div class="admin-page-subtitle">
        Manage and update customer orders
    </div>

    <!-- Status Filter Pills -->
    <div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
        <?php
        $statuses = ['all','pending','processing','shipped','completed','cancelled'];
        $filterStatus = $_GET['status'] ?? 'all';
        foreach ($statuses as $s):
        ?>
        <button onclick="filterOrders('<?= $s ?>')"
                id="filter-<?= $s ?>"
                style="padding:5px 14px; font-size:0.75rem;
                       letter-spacing:1px; text-transform:uppercase;
                       border:1px solid var(--vv-gold);
                       background:transparent; cursor:pointer;
                       color:var(--vv-brown); border-radius:12px;
                       transition:all 0.2s;">
            <?= ucfirst($s) ?>
            <?php
            if ($s !== 'all') {
                $count = count(array_filter($orders, fn($o) => $o['status'] === $s));
                if ($count > 0) echo "<span style='background:var(--vv-brown);color:#fff;
                    border-radius:10px;padding:1px 6px;font-size:0.65rem;
                    margin-left:4px;'>$count</span>";
            }
            ?>
        </button>
        <?php endforeach; ?>
    </div>

    <div class="ad-card">
        <div class="ad-card-header">
            <h5 class="ad-card-title">
                All Orders (<?= count($orders) ?>)
            </h5>
        </div>
        <?php if (empty($orders)): ?>
            <p style="padding:24px; color:var(--vv-muted);">
                No orders yet.
            </p>
        <?php else: ?>
        <table class="ad-table" id="ordersTable">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Update Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o):
                    $items = $pdo->prepare("
                        SELECT oi.*, p.name AS product_name
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ?
                    ");
                    $items->execute([$o['id']]);
                    $orderItems = $items->fetchAll();
                ?>
                <!-- Order Row -->
                <tr class="ad-order-row order-status-<?= $o['status'] ?>"
                    onclick="toggleAdminOrder(<?= $o['id'] ?>)">
                    <td>
                        <strong>
                            #<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?>
                        </strong>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($o['customer_name']) ?></div>
                        <div style="font-size:0.75rem; color:var(--vv-muted);">
                            <?= htmlspecialchars($o['customer_email']) ?>
                        </div>
                    </td>
                    <td><strong>$<?= number_format($o['total'],2) ?></strong></td>
                    <td>
                        <span class="ad-pill ad-pill-<?= $o['status'] ?>">
                            <?= ucfirst($o['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?= date('M d, Y', strtotime($o['created_at'])) ?>
                    </td>
                    <td onclick="event.stopPropagation()">
                        <form method="POST"
                              style="display:flex; gap:6px;"
                              onclick="event.stopPropagation()">
                            <input type="hidden" name="action" value="update_order">
                            <input type="hidden" name="active_tab" value="orders">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="ad-input"
                                    style="padding:4px 8px; font-size:0.8rem; width:auto;">
                                <?php foreach (['pending','processing','shipped','completed','cancelled'] as $s): ?>
                                    <option value="<?= $s ?>"
                                        <?= $o['status']===$s ? 'selected':'' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="ad-btn ad-btn-edit">
                                Update
                            </button>
                        </form>
                    </td>
                    <td>
                        <span id="order-arrow-<?= $o['id'] ?>"
                              style="color:var(--vv-muted);">▼</span>
                    </td>
                </tr>

                <!-- Order Detail Row -->
                <tr class="ad-order-detail order-status-<?= $o['status'] ?>"
                    id="admin-order-detail-<?= $o['id'] ?>">
                    <td colspan="7" style="padding:16px 24px;
                                           background:#f5f0e8;">
                        <div style="font-size:0.7rem; letter-spacing:2px;
                                    text-transform:uppercase;
                                    color:var(--vv-muted); margin-bottom:10px;">
                            Items in Order
                        </div>
                        <?php if (!empty($orderItems)): ?>
                            <?php foreach ($orderItems as $item): ?>
                            <div style="display:flex;
                                        justify-content:space-between;
                                        padding:6px 0;
                                        border-bottom:1px solid var(--vv-parchment);
                                        font-size:0.85rem;">
                                <span>
                                    🏺 <?= htmlspecialchars($item['product_name']) ?>
                                    <span style="color:var(--vv-muted);">
                                        × <?= $item['quantity'] ?>
                                    </span>
                                </span>
                                <span style="color:var(--vv-brown); font-weight:700;">
                                    $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                            <div style="display:flex; justify-content:space-between;
                                        padding-top:10px; font-weight:700;">
                                <span>Total</span>
                                <span style="color:var(--vv-brown);">
                                    $<?= number_format($o['total'], 2) ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <p style="color:var(--vv-muted); font-size:0.85rem;">
                                No items found.
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($o['full_name'])): ?>
                        <div style="margin-top:12px; padding-top:12px;
                                    border-top:1px solid var(--vv-parchment);
                                    font-size:0.82rem; color:var(--vv-muted);">
                            📦 Ship to: <?= htmlspecialchars($o['full_name']) ?>,
                            <?= htmlspecialchars($o['address']) ?>,
                            <?= htmlspecialchars($o['city']) ?>,
                            <?= htmlspecialchars($o['country']) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<!-- ══════════════════════════════════════
     USERS TAB
══════════════════════════════════════ -->
<div class="vv-tab-content" id="tab-users" style="display:none;">

    <div class="admin-page-title">Users</div>
    <div class="admin-page-subtitle">
        All registered customers and admins
    </div>

    <div class="ad-card">
        <div class="ad-card-header">
            <h5 class="ad-card-title">
                All Users (<?= count($users) ?>)
            </h5>
        </div>
        <table class="ad-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>#<?= $u['id'] ?></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px;
                                        background:var(--vv-dark);
                                        color:var(--vv-gold);
                                        border-radius:50%;
                                        display:flex; align-items:center;
                                        justify-content:center;
                                        font-family:var(--font-serif);
                                        font-size:0.9rem; flex-shrink:0;">
                                <?= strtoupper(substr($u['name'],0,1)) ?>
                            </div>
                            <?= htmlspecialchars($u['name']) ?>
                        </div>
                    </td>
                    <td style="font-size:0.82rem;">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>
                    <td>
                        <span class="ad-pill <?= $u['role']==='admin' ? 'ad-pill-admin':'ad-pill-active' ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td><?= $u['order_count'] ?></td>
                    <td style="color:var(--vv-brown); font-weight:600;">
                        $<?= number_format($u['total_spent'], 2) ?>
                    </td>
                    <td>
                        <?= date('M d, Y', strtotime($u['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- ══════════════════════════════════════
     REVIEWS TAB
══════════════════════════════════════ -->
<div class="vv-tab-content" id="tab-reviews" style="display:none;">

    <div class="admin-page-title">Reviews</div>
    <div class="admin-page-subtitle">Customer product reviews</div>

    <div class="ad-card">
        <div class="ad-card-header">
            <h5 class="ad-card-title">
                All Reviews (<?= count($reviews) ?>)
            </h5>
        </div>
        <?php if (empty($reviews)): ?>
            <p style="padding:24px; color:var(--vv-muted);">
                No reviews yet.
            </p>
        <?php else: ?>
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['user_name']) ?></td>
                    <td style="font-size:0.82rem; max-width:160px;">
                        <?= htmlspecialchars($r['product_name']) ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <?php for ($i=1; $i<=5; $i++): ?>
                            <?= $i<=$r['rating'] ? '⭐':'☆' ?>
                        <?php endfor; ?>
                        <span style="font-size:0.75rem;
                                     color:var(--vv-muted);
                                     margin-left:4px;">
                            (<?= $r['rating'] ?>/5)
                        </span>
                    </td>
                    <td style="max-width:200px; font-size:0.82rem;">
                        <?= htmlspecialchars(substr($r['comment'],0,80)) ?>
                        <?= strlen($r['comment']) > 80 ? '...' : '' ?>
                    </td>
                    <td>
                        <?= date('M d, Y', strtotime($r['created_at'])) ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_review">
                            <input type="hidden" name="active_tab" value="reviews">
                            <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="ad-btn ad-btn-delete"
                                    onclick="return confirm('Delete this review?')">
                                🗑️ Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<!-- ══════════════════════════════════════
     CATEGORIES TAB
══════════════════════════════════════ -->
<div class="vv-tab-content" id="tab-categories" style="display:none;">

    <div class="admin-page-title">Categories</div>
    <div class="admin-page-subtitle">Manage product categories</div>

    <div class="row g-4">
        <!-- Add Category -->
        <div class="col-12 col-md-5">
            <div class="ad-card">
                <div class="ad-card-header">
                    <h5 class="ad-card-title">Add New Category</h5>
                </div>
                <div style="padding:20px;">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_category">
                        <input type="hidden" name="active_tab" value="categories">
                        <div class="mb-3">
                            <label class="ad-label">Category Name *</label>
                            <input type="text" name="cat_name"
                                   class="ad-input"
                                   placeholder="e.g. Vintage Cameras" required>
                        </div>
                        <button type="submit" class="ad-action-btn">
                            ➕ Add Category
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="col-12 col-md-7">
            <div class="ad-card">
                <div class="ad-card-header">
                    <h5 class="ad-card-title">
                        All Categories (<?= count($categories) ?>)
                    </h5>
                </div>
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= htmlspecialchars($cat['name']) ?></td>
                            <td style="font-size:0.78rem; color:var(--vv-muted);">
                                <?= $cat['slug'] ?>
                            </td>
                            <td><?= $cat['product_count'] ?></td>
                            <td>
                                <?php if ($cat['product_count'] == 0): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="active_tab" value="categories">
                                        <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                        <button type="submit"
                                                class="ad-btn ad-btn-delete"
                                                onclick="return confirm('Delete this category?')">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:0.75rem; color:var(--vv-muted);">
                                        Has products
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php echo '</main></div>'; ?>

<script>
// ── Tab Switching ──
const tabs     = document.querySelectorAll('.vv-tab-btn');
const contents = document.querySelectorAll('.vv-tab-content');

function switchTab(name) {
    tabs.forEach(t => t.classList.remove('active'));
    contents.forEach(c => c.style.display = 'none');
    const btn     = document.querySelector(`[data-tab="${name}"]`);
    const content = document.getElementById(`tab-${name}`);
    if (btn)     btn.classList.add('active');
    if (content) content.style.display = 'block';
    history.replaceState(null, '', `?tab=${name}`);
}

tabs.forEach(btn => {
    btn.addEventListener('click', () => switchTab(btn.dataset.tab));
});

const urlTab = new URLSearchParams(window.location.search).get('tab');
switchTab(urlTab || '<?= $activeTab ?>');

// ── Stat Counters ──
function animateCounter(el, target, duration = 1200) {
    let start     = 0;
    const isPrice = el.dataset.prefix === '$';
    const step    = target / (duration / 16);
    const timer   = setInterval(() => {
        start += step;
        if (start >= target) { start = target; clearInterval(timer); }
        el.textContent = isPrice
            ? '$' + Math.floor(start).toLocaleString()
            : Math.floor(start).toLocaleString();
    }, 16);
}

document.querySelectorAll('.ad-stat-number[data-target]').forEach(el => {
    animateCounter(el, parseInt(el.dataset.target));
});

// ── Modal ──
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

// Close modal on outside click
document.querySelectorAll('.ad-modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

// ── Edit Product Modal ──
function openEditProduct(id, name, desc, price, stock, catId) {
    document.getElementById('edit_id').value    = id;
    document.getElementById('edit_name').value  = name;
    document.getElementById('edit_desc').value  = desc;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_stock').value = stock;
    document.getElementById('edit_cat').value   = catId;
    openModal('editProductModal');
}

// ── Toggle Order Details ──
function toggleAdminOrder(id) {
    const detail = document.getElementById('admin-order-detail-' + id);
    const arrow  = document.getElementById('order-arrow-' + id);
    const isOpen = detail.classList.contains('open');
    detail.classList.toggle('open');
    arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}

// ── Filter Orders by Status ──
function filterOrders(status) {
    // Update button styles
    document.querySelectorAll('[id^="filter-"]').forEach(btn => {
        btn.style.background = 'transparent';
        btn.style.color      = 'var(--vv-brown)';
    });
    const activeBtn = document.getElementById('filter-' + status);
    if (activeBtn) {
        activeBtn.style.background = 'var(--vv-dark)';
        activeBtn.style.color      = 'var(--vv-gold)';
    }

    // Show/hide rows
    document.querySelectorAll('.ad-order-row, .ad-order-detail').forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else {
            row.style.display = row.classList.contains('order-status-' + status)
                ? '' : 'none';
        }
    });
}

// ── Sidebar Toggle ──
const toggle  = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('adminSidebar');
if (toggle && sidebar) {
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });
}

// ── Auto Hide Alerts ──
const alertMsg = document.getElementById('alertMsg');
if (alertMsg) {
    setTimeout(() => {
        alertMsg.style.transition = 'opacity 0.5s';
        alertMsg.style.opacity    = '0';
        setTimeout(() => alertMsg.remove(), 500);
    }, 4000);
}

// Set initial filter
filterOrders('all');
</script>

<?php echo '</body></html>'; ?>