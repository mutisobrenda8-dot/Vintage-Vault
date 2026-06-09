<?php
$pageTitle = 'Dashboard';
require '../db.php';
require 'includes/admin_header.php';

$success = $error = '';

// ── ADD PRODUCT ──
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
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $image = time().'_'.basename($_FILES['image']['name']);
                move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $upload_dir.$image
                );
            }
        }
        $pdo->prepare(
            "INSERT INTO products
             (category_id,name,description,price,stock,image)
             VALUES (?,?,?,?,?,?)"
        )->execute([
            $category_id,$name,$description,$price,$stock,$image
        ]);
        $success = 'Product added!';
    }
}

// ── EDIT PRODUCT ──
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
        $ext        = strtolower(pathinfo(
            $_FILES['image']['name'], PATHINFO_EXTENSION
        ));
        $upload_dir = '../images/products/';
        if (in_array($ext, $allowed)) {
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $image = time().'_'.basename($_FILES['image']['name']);
            move_uploaded_file(
                $_FILES['image']['tmp_name'], $upload_dir.$image
            );
        }
    }

    $pdo->prepare(
        "UPDATE products
         SET category_id=?,name=?,description=?,price=?,stock=?,image=?
         WHERE id=?"
    )->execute([
        $category_id,$name,$description,$price,$stock,$image,$id
    ]);
    $success = 'Product updated!';
}

// ── DELETE PRODUCT ──
if (isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $pdo->prepare("DELETE FROM products WHERE id=?")
        ->execute([$_POST['product_id']]);
    $success = 'Product deleted.';
}

// ── UPDATE ORDER STATUS ──
if (isset($_POST['action']) && $_POST['action'] === 'update_order') {
    $allowed = [
        'pending','processing','shipped','completed','cancelled'
    ];
    if (in_array($_POST['status'], $allowed)) {
        $pdo->prepare("UPDATE orders SET status=? WHERE id=?")
            ->execute([$_POST['status'], $_POST['order_id']]);
        $success = 'Order updated!';
    }
}

// ── DELETE REVIEW ──
if (isset($_POST['action']) && $_POST['action'] === 'delete_review') {
    $pdo->prepare("DELETE FROM reviews WHERE id=?")
        ->execute([$_POST['review_id']]);
    $success = 'Review deleted.';
}

// ── ADD CATEGORY ──
if (isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = trim($_POST['cat_name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    if (!empty($name)) {
        $pdo->prepare(
            "INSERT INTO categories (name,slug) VALUES (?,?)"
        )->execute([$name, $slug]);
        $success = 'Category added!';
    }
}

// ── DELETE CATEGORY ──
if (isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $pdo->prepare("DELETE FROM categories WHERE id=?")
        ->execute([$_POST['category_id']]);
    $success = 'Category deleted.';
}

// ── FETCH ALL DATA ──
$totalProducts = $pdo->query(
    "SELECT COUNT(*) FROM products"
)->fetchColumn();
$totalOrders   = $pdo->query(
    "SELECT COUNT(*) FROM orders"
)->fetchColumn();
$totalUsers    = $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role='customer'"
)->fetchColumn();
$totalRevenue  = $pdo->query(
    "SELECT COALESCE(SUM(total),0) FROM orders
     WHERE status != 'cancelled'"
)->fetchColumn();
$pendingOrders = $pdo->query(
    "SELECT COUNT(*) FROM orders WHERE status='pending'"
)->fetchColumn();

$products = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll();

$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id ORDER BY c.name
")->fetchAll();

$orders = $pdo->query("
    SELECT o.*, u.name AS customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
")->fetchAll();

$users = $pdo->query("
    SELECT u.*, COUNT(o.id) AS order_count
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
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
?>

<?php if ($success): ?>
    <div class="vv-alert-success" id="alertMsg"><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="vv-alert-error" id="alertMsg"><?= $error ?></div>
<?php endif; ?>

<!-- TABS NAV -->
<div class="vv-tab-nav">
    <button class="vv-tab-btn" data-tab="overview">📊 Overview</button>
    <button class="vv-tab-btn" data-tab="products">🏺 Products</button>
    <button class="vv-tab-btn" data-tab="orders">🛒 Orders</button>
    <button class="vv-tab-btn" data-tab="users">👥 Users</button>
    <button class="vv-tab-btn" data-tab="reviews">⭐ Reviews</button>
    <button class="vv-tab-btn" data-tab="categories">🗂️ Categories</button>
</div>

<!-- ══ OVERVIEW ══ -->
<div class="vv-tab-content" id="tab-overview">
    <div class="admin-page-title">Dashboard</div>
    <div class="admin-page-subtitle">
        Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>.
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon">🏺</span>
            <div class="stat-number"
                 data-target="<?= $totalProducts ?>">0</div>
            <div class="stat-label">Products</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">🛒</span>
            <div class="stat-number"
                 data-target="<?= $totalOrders ?>">0</div>
            <div class="stat-label">Orders</div>
            <div class="stat-change">
                🕐 <?= $pendingOrders ?> pending
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">💰</span>
            <div class="stat-number"
                 data-target="<?= intval($totalRevenue) ?>"
                 data-prefix="$">$0</div>
            <div class="stat-label">Revenue</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">👥</span>
            <div class="stat-number"
                 data-target="<?= $totalUsers ?>">0</div>
            <div class="stat-label">Customers</div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">Recent Products</span>
            <button class="admin-card-action"
                    onclick="switchTab('products')">
                View all
            </button>
        </div>
        <table class="admin-table">
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
                    <td><?= $p['stock'] ?></td>
                    <td>
                        <span class="status-pill
                            <?= $p['stock']>0
                                ? 'status-active':'status-sold' ?>">
                            <?= $p['stock']>0 ? 'In Stock':'Sold Out' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">Recent Orders</span>
            <button class="admin-card-action"
                    onclick="switchTab('orders')">
                View all
            </button>
        </div>
        <?php if (empty($orders)): ?>
            <p style="padding:20px; color:var(--vv-muted);">
                No orders yet.
            </p>
        <?php else: ?>
        <table class="admin-table">
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
                        <span class="status-pill
                            status-<?= $o['status'] ?>">
                            <?= ucfirst($o['status']) ?>
                        </span>
                    </td>
                    <td>
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

<!-- ══ PRODUCTS ══ -->
<div class="vv-tab-content" id="tab-products" style="display:none;">
    <div class="admin-page-title">Products</div>
    <div class="admin-page-subtitle">Manage your inventory</div>

    <div style="display:grid; grid-template-columns:1fr 1fr;
                gap:24px; align-items:start;">

        <!-- Add Product Form -->
        <div class="admin-card">
            <div class="admin-card-header">
                <span class="admin-card-title">Add New Product</span>
            </div>
            <div style="padding:20px;">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action"
                           value="add_product">
                    <input type="hidden" name="active_tab"
                           value="products">

                    <div style="margin-bottom:14px;">
                        <label class="vv-form-label">Name *</label>
                        <input type="text" name="name"
                               class="vv-input" required>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="vv-form-label">Category *</label>
                        <select name="category_id"
                                class="vv-input" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:grid;
                                grid-template-columns:1fr 1fr;
                                gap:12px; margin-bottom:14px;">
                        <div>
                            <label class="vv-form-label">Price *</label>
                            <input type="number" name="price"
                                   class="vv-input"
                                   step="0.01" min="0" required>
                        </div>
                        <div>
                            <label class="vv-form-label">Stock</label>
                            <input type="number" name="stock"
                                   class="vv-input"
                                   min="0" value="1">
                        </div>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="vv-form-label">Description</label>
                        <textarea name="description" class="vv-input"
                                  rows="3"
                                  style="resize:vertical;"></textarea>
                    </div>
                    <div style="margin-bottom:20px;">
                        <label class="vv-form-label">Image</label>
                        <input type="file" name="image"
                               class="vv-input" accept="image/*">
                    </div>
                    <button type="submit" class="quick-action-btn">
                        ➕ Add Product
                    </button>
                </form>
            </div>
        </div>

        <!-- Products List -->
        <div class="admin-card">
            <div class="admin-card-header">
                <span class="admin-card-title">
                    All Products (<?= count($products) ?>)
                </span>
            </div>
            <div style="max-height:520px; overflow-y:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <div style="font-size:0.85rem;">
                                    <?= htmlspecialchars($p['name']) ?>
                                </div>
                                <div style="font-size:0.72rem;
                                            color:var(--vv-muted);">
                                    <?= htmlspecialchars(
                                        $p['category_name']
                                    ) ?>
                                </div>
                            </td>
                            <td>$<?= number_format($p['price'],2) ?></td>
                            <td>
                                <span class="status-pill
                                    <?= $p['stock']>0
                                        ? 'status-active':'status-sold' ?>">
                                    <?= $p['stock'] ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-action btn-edit"
                                        onclick="openEditModal(
                                            <?= $p['id'] ?>,
                                            '<?= addslashes($p['name']) ?>',
                                            '<?= addslashes($p['description']) ?>',
                                            <?= $p['price'] ?>,
                                            <?= $p['stock'] ?>,
                                            <?= $p['category_id'] ?>
                                        )">Edit</button>
                                <form method="POST"
                                      style="display:inline;">
                                    <input type="hidden" name="action"
                                           value="delete_product">
                                    <input type="hidden"
                                           name="active_tab"
                                           value="products">
                                    <input type="hidden"
                                           name="product_id"
                                           value="<?= $p['id'] ?>">
                                    <button type="submit"
                                            class="btn-action btn-delete"
                                            onclick="return confirm(
                                                'Delete this product?'
                                            )">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal"
         style="display:none; position:fixed; inset:0;
                background:rgba(0,0,0,0.6); z-index:9999;
                align-items:center; justify-content:center;">
        <div style="background:#faf6ef;
                    border:1px solid var(--vv-gold);
                    padding:28px; width:100%; max-width:500px;
                    max-height:90vh; overflow-y:auto;
                    position:relative;">
            <button onclick="closeEditModal()"
                    style="position:absolute; top:12px; right:16px;
                           background:none; border:none;
                           font-size:1.4rem; cursor:pointer;
                           color:var(--vv-muted);">✕</button>
            <h3 style="font-family:var(--font-serif);
                       font-style:italic; margin-bottom:20px;
                       color:var(--vv-dark);">Edit Product</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action"
                       value="edit_product">
                <input type="hidden" name="active_tab"
                       value="products">
                <input type="hidden" name="product_id"
                       id="edit_id">
                <div style="margin-bottom:14px;">
                    <label class="vv-form-label">Name *</label>
                    <input type="text" name="name" id="edit_name"
                           class="vv-input" required>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="vv-form-label">Category *</label>
                    <select name="category_id" id="edit_cat"
                            class="vv-input" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:grid;
                            grid-template-columns:1fr 1fr;
                            gap:12px; margin-bottom:14px;">
                    <div>
                        <label class="vv-form-label">Price *</label>
                        <input type="number" name="price"
                               id="edit_price" class="vv-input"
                               step="0.01" min="0" required>
                    </div>
                    <div>
                        <label class="vv-form-label">Stock</label>
                        <input type="number" name="stock"
                               id="edit_stock" class="vv-input"
                               min="0">
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="vv-form-label">Description</label>
                    <textarea name="description" id="edit_desc"
                              class="vv-input" rows="3"
                              style="resize:vertical;"></textarea>
                </div>
                <div style="margin-bottom:20px;">
                    <label class="vv-form-label">New Image</label>
                    <input type="file" name="image"
                           class="vv-input" accept="image/*">
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="quick-action-btn">
                        💾 Save
                    </button>
                    <button type="button"
                            onclick="closeEditModal()"
                            class="quick-action-btn"
                            style="background:var(--vv-parchment);
                                   color:var(--vv-brown);">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══ ORDERS ══ -->
<div class="vv-tab-content" id="tab-orders" style="display:none;">
    <div class="admin-page-title">Orders</div>
    <div class="admin-page-subtitle">Manage customer orders</div>

    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">
                All Orders (<?= count($orders) ?>)
            </span>
        </div>
        <?php if (empty($orders)): ?>
            <p style="padding:24px; color:var(--vv-muted);">
                No orders yet.
            </p>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td>$<?= number_format($o['total'],2) ?></td>
                    <td>
                        <span class="status-pill
                            status-<?= $o['status'] ?>">
                            <?= ucfirst($o['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?= date('M d, Y',
                            strtotime($o['created_at'])) ?>
                    </td>
                    <td>
                        <form method="POST"
                              style="display:flex; gap:6px;">
                            <input type="hidden" name="action"
                                   value="update_order">
                            <input type="hidden" name="active_tab"
                                   value="orders">
                            <input type="hidden" name="order_id"
                                   value="<?= $o['id'] ?>">
                            <select name="status" class="vv-input"
                                    style="padding:4px 8px;
                                           font-size:0.8rem;">
                                <?php foreach ([
                                    'pending','processing',
                                    'shipped','completed','cancelled'
                                ] as $s): ?>
                                    <option value="<?= $s ?>"
                                        <?= $o['status']===$s
                                            ? 'selected':'' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit"
                                    class="btn-action btn-edit">
                                Update
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

<!-- ══ USERS ══ -->
<div class="vv-tab-content" id="tab-users" style="display:none;">
    <div class="admin-page-title">Users</div>
    <div class="admin-page-subtitle">All registered accounts</div>

    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">
                All Users (<?= count($users) ?>)
            </span>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Orders</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>#<?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="status-pill
                            <?= $u['role']==='admin'
                                ? 'status-shipped':'status-active' ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td><?= $u['order_count'] ?></td>
                    <td>
                        <?= date('M d, Y',
                            strtotime($u['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ══ REVIEWS ══ -->
<div class="vv-tab-content" id="tab-reviews" style="display:none;">
    <div class="admin-page-title">Reviews</div>
    <div class="admin-page-subtitle">Customer product reviews</div>

    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">
                All Reviews (<?= count($reviews) ?>)
            </span>
        </div>
        <?php if (empty($reviews)): ?>
            <p style="padding:24px; color:var(--vv-muted);">
                No reviews yet.
            </p>
        <?php else: ?>
        <table class="admin-table">
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
                    <td style="font-size:0.82rem;">
                        <?= htmlspecialchars($r['product_name']) ?>
                    </td>
                    <td>
                        <?php for ($i=1; $i<=5; $i++): ?>
                            <?= $i<=$r['rating'] ? '⭐':'☆' ?>
                        <?php endfor; ?>
                    </td>
                    <td style="max-width:180px; font-size:0.82rem;">
                        <?= htmlspecialchars($r['comment']) ?>
                    </td>
                    <td>
                        <?= date('M d, Y',
                            strtotime($r['created_at'])) ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action"
                                   value="delete_review">
                            <input type="hidden" name="active_tab"
                                   value="reviews">
                            <input type="hidden" name="review_id"
                                   value="<?= $r['id'] ?>">
                            <button type="submit"
                                    class="btn-action btn-delete"
                                    onclick="return confirm(
                                        'Delete this review?'
                                    )">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ══ CATEGORIES ══ -->
<div class="vv-tab-content" id="tab-categories" style="display:none;">
    <div class="admin-page-title">Categories</div>
    <div class="admin-page-subtitle">Manage product categories</div>

    <div style="display:grid; grid-template-columns:1fr 1fr;
                gap:24px; align-items:start;">

        <div class="admin-card">
            <div class="admin-card-header">
                <span class="admin-card-title">Add Category</span>
            </div>
            <div style="padding:20px;">
                <form method="POST">
                    <input type="hidden" name="action"
                           value="add_category">
                    <input type="hidden" name="active_tab"
                           value="categories">
                    <div style="margin-bottom:16px;">
                        <label class="vv-form-label">Name *</label>
                        <input type="text" name="cat_name"
                               class="vv-input" required>
                    </div>
                    <button type="submit" class="quick-action-btn">
                        ➕ Add
                    </button>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <span class="admin-card-title">
                    All Categories (<?= count($categories) ?>)
                </span>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Products</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['name']) ?></td>
                        <td><?= $cat['product_count'] ?></td>
                        <td>
                            <?php if ($cat['product_count'] == 0): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action"
                                       value="delete_category">
                                <input type="hidden"
                                       name="active_tab"
                                       value="categories">
                                <input type="hidden"
                                       name="category_id"
                                       value="<?= $cat['id'] ?>">
                                <button type="submit"
                                        class="btn-action btn-delete"
                                        onclick="return confirm(
                                            'Delete this category?'
                                        )">Delete</button>
                            </form>
                            <?php else: ?>
                                <span style="font-size:0.75rem;
                                             color:var(--vv-muted);">
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

<?php echo '</main></div>'; ?>

<script>
// Tab switching
const tabs     = document.querySelectorAll('.vv-tab-btn');
const contents = document.querySelectorAll('.vv-tab-content');

function switchTab(name) {
    tabs.forEach(t => t.classList.remove('active'));
    contents.forEach(c => c.style.display = 'none');
    const btn = document.querySelector(`[data-tab="${name}"]`);
    const content = document.getElementById(`tab-${name}`);
    if (btn) btn.classList.add('active');
    if (content) content.style.display = 'block';
    history.replaceState(null, '', `?tab=${name}`);
}

tabs.forEach(btn => {
    btn.addEventListener('click', () => switchTab(btn.dataset.tab));
});

const urlTab = new URLSearchParams(window.location.search).get('tab');
switchTab(urlTab || '<?= $activeTab ?>');

// Edit modal
function openEditModal(id, name, desc, price, stock, catId) {
    document.getElementById('edit_id').value    = id;
    document.getElementById('edit_name').value  = name;
    document.getElementById('edit_desc').value  = desc;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_stock').value = stock;
    document.getElementById('edit_cat').value   = catId;
    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.body.style.overflow = '';
}

document.getElementById('editModal').addEventListener('click',
    function(e) {
        if (e.target === this) closeEditModal();
    }
);

// Stat counters
function animateCounter(el, target, duration = 1200) {
    let start = 0;
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

document.querySelectorAll('.stat-number[data-target]').forEach(el => {
    animateCounter(el, parseInt(el.dataset.target));
});

// Sidebar toggle
const toggle  = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('adminSidebar');
if (toggle && sidebar) {
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });
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

<?php echo '</body></html>'; ?>