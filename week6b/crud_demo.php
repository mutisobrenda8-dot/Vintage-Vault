<?php
// Week6/crud_demo.php
// Demonstrates CREATE, READ, UPDATE, DELETE with labeled SQL for documentation/screenshots

$pageTitle = 'CRUD Demonstration';
require 'db.php';
require 'includes/header.php';

$message = '';

// ── CREATE ──
if (isset($_POST['action']) && $_POST['action'] === 'demo_create') {
    $name  = trim($_POST['demo_name'] ?? '');
    $price = trim($_POST['demo_price'] ?? '');
    if ($name && $price) {
        $pdo->prepare("
            INSERT INTO products (category_id, name, description, price, stock)
            VALUES (1, ?, 'Added via CRUD demo', ?, 1)
        ")->execute([$name, $price]);
        $message = "CREATE successful — inserted '$name' into the products table.";
    }
}

// ── UPDATE ──
if (isset($_POST['action']) && $_POST['action'] === 'demo_update') {
    $id    = $_POST['demo_id']    ?? '';
    $price = $_POST['demo_price'] ?? '';
    if ($id && $price) {
        $pdo->prepare("UPDATE products SET price = ? WHERE id = ?")
            ->execute([$price, $id]);
        $message = "UPDATE successful — product #$id price changed to \$$price.";
    }
}

// ── DELETE ──
if (isset($_POST['action']) && $_POST['action'] === 'demo_delete') {
    $id = $_POST['demo_id'] ?? '';
    if ($id) {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $message = "DELETE successful — product #$id removed from the database.";
    }
}

// ── READ ──
$products = $pdo->query("
    SELECT p.id, p.name, p.price, p.stock, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
    LIMIT 10
")->fetchAll();
?>

<div class="container py-5">

    <h1 style="font-family:var(--font-serif); font-style:italic;
               color:var(--vv-dark); margin-bottom:4px;">
        CRUD Operations Demonstration
    </h1>
    <p style="color:var(--vv-muted); font-size:0.82rem; margin-bottom:28px;">
        Week 6 — Database Integration & CRUD, applied to the Vintage Vault
        products table using PHP PDO and MySQL.
    </p>

    <?php if ($message): ?>
        <div class="vv-alert-success"><?= $message ?></div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- CREATE -->
        <div class="col-12 col-md-6">
            <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                        border-radius:6px; padding:24px;">
                <h5 style="font-family:var(--font-serif); font-style:italic;
                           margin-bottom:4px;">🟢 CREATE</h5>
                <div style="background:#2a1e14; border-radius:4px;
                            padding:10px 14px; margin-bottom:16px;
                            font-family:monospace; font-size:0.78rem;
                            color:#c9b49a;">
                    <span style="color:#7a9b4a;">INSERT INTO</span> products
                    (name, price) <span style="color:#7a9b4a;">VALUES</span> (?, ?);
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="demo_create">
                    <div class="mb-2">
                        <input type="text" name="demo_name" class="form-control"
                               style="border:1px solid var(--vv-gold); border-radius:0;"
                               placeholder="Product name" required>
                    </div>
                    <div class="mb-2">
                        <input type="number" step="0.01" name="demo_price"
                               class="form-control"
                               style="border:1px solid var(--vv-gold); border-radius:0;"
                               placeholder="Price" required>
                    </div>
                    <button type="submit" class="btn btn-vv-primary w-100">
                        Run INSERT
                    </button>
                </form>
            </div>
        </div>

        <!-- UPDATE -->
        <div class="col-12 col-md-6">
            <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                        border-radius:6px; padding:24px;">
                <h5 style="font-family:var(--font-serif); font-style:italic;
                           margin-bottom:4px;">🟡 UPDATE</h5>
                <div style="background:#2a1e14; border-radius:4px;
                            padding:10px 14px; margin-bottom:16px;
                            font-family:monospace; font-size:0.78rem;
                            color:#c9b49a;">
                    <span style="color:#7a9b4a;">UPDATE</span> products
                    <span style="color:#7a9b4a;">SET</span> price = ?
                    <span style="color:#7a9b4a;">WHERE</span> id = ?;
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="demo_update">
                    <div class="mb-2">
                        <input type="number" name="demo_id" class="form-control"
                               style="border:1px solid var(--vv-gold); border-radius:0;"
                               placeholder="Product ID" required>
                    </div>
                    <div class="mb-2">
                        <input type="number" step="0.01" name="demo_price"
                               class="form-control"
                               style="border:1px solid var(--vv-gold); border-radius:0;"
                               placeholder="New price" required>
                    </div>
                    <button type="submit" class="btn btn-vv-primary w-100">
                        Run UPDATE
                    </button>
                </form>
            </div>
        </div>

        <!-- DELETE -->
        <div class="col-12 col-md-6">
            <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                        border-radius:6px; padding:24px;">
                <h5 style="font-family:var(--font-serif); font-style:italic;
                           margin-bottom:4px;">🔴 DELETE</h5>
                <div style="background:#2a1e14; border-radius:4px;
                            padding:10px 14px; margin-bottom:16px;
                            font-family:monospace; font-size:0.78rem;
                            color:#c9b49a;">
                    <span style="color:#7a9b4a;">DELETE FROM</span> products
                    <span style="color:#7a9b4a;">WHERE</span> id = ?;
                </div>
                <form method="POST" onsubmit="return confirm('Delete this product?');">
                    <input type="hidden" name="action" value="demo_delete">
                    <div class="mb-2">
                        <input type="number" name="demo_id" class="form-control"
                               style="border:1px solid var(--vv-gold); border-radius:0;"
                               placeholder="Product ID" required>
                    </div>
                    <button type="submit" class="btn btn-vv-primary w-100"
                            style="background:#8b1a1a;">
                        Run DELETE
                    </button>
                </form>
            </div>
        </div>

        <!-- READ -->
        <div class="col-12 col-md-6">
            <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                        border-radius:6px; padding:24px;">
                <h5 style="font-family:var(--font-serif); font-style:italic;
                           margin-bottom:4px;">🔵 READ</h5>
                <div style="background:#2a1e14; border-radius:4px;
                            padding:10px 14px; margin-bottom:16px;
                            font-family:monospace; font-size:0.78rem;
                            color:#c9b49a;">
                    <span style="color:#7a9b4a;">SELECT</span> * <span style="color:#7a9b4a;">FROM</span> products
                    <span style="color:#7a9b4a;">ORDER BY</span> id <span style="color:#7a9b4a;">DESC</span>;
                </div>
                <p style="font-size:0.8rem; color:var(--vv-muted);">
                    Live results shown in the table below — refreshes after
                    every Create/Update/Delete.
                </p>
            </div>
        </div>

    </div>

    <!-- Live Results Table -->
    <div style="background:#faf6ef; border:1px solid var(--vv-parchment);
                border-radius:6px; margin-top:24px; overflow:hidden;">
        <div style="padding:14px 20px; background:var(--vv-parchment);
                    font-family:var(--font-serif); font-style:italic;">
            Live Products (most recent 10)
        </div>
        <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
            <thead>
                <tr style="background:var(--vv-parchment);">
                    <th style="padding:10px 16px; text-align:left; font-size:0.68rem;
                               letter-spacing:2px; text-transform:uppercase;
                               color:var(--vv-muted);">ID</th>
                    <th style="padding:10px 16px; text-align:left; font-size:0.68rem;
                               letter-spacing:2px; text-transform:uppercase;
                               color:var(--vv-muted);">Name</th>
                    <th style="padding:10px 16px; text-align:left; font-size:0.68rem;
                               letter-spacing:2px; text-transform:uppercase;
                               color:var(--vv-muted);">Category</th>
                    <th style="padding:10px 16px; text-align:left; font-size:0.68rem;
                               letter-spacing:2px; text-transform:uppercase;
                               color:var(--vv-muted);">Price</th>
                    <th style="padding:10px 16px; text-align:left; font-size:0.68rem;
                               letter-spacing:2px; text-transform:uppercase;
                               color:var(--vv-muted);">Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr style="border-bottom:1px solid #f0e8d8;">
                    <td style="padding:10px 16px;">#<?= $p['id'] ?></td>
                    <td style="padding:10px 16px;"><?= htmlspecialchars($p['name']) ?></td>
                    <td style="padding:10px 16px;"><?= htmlspecialchars($p['category_name']) ?></td>
                    <td style="padding:10px 16px; color:var(--vv-brown); font-weight:700;">
                        $<?= number_format($p['price'], 2) ?>
                    </td>
                    <td style="padding:10px 16px;"><?= $p['stock'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p style="font-size:0.78rem; color:var(--vv-muted); margin-top:16px;">
        Note: This page reuses the same <code>products</code> table that powers
        the live shop, admin dashboard, and cart — demonstrating that CRUD here
        is the same CRUD running the whole store (admin Products tab = same
        Create/Update/Delete; Shop page = same Read).
    </p>

</div>

<?php require 'includes/footer.php'; ?>