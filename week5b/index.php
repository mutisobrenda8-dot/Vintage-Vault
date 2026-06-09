<?php
// Week5/index.php
// CRUD — READ operation
// Fetches and displays all products from database

$pageTitle = 'Products — CRUD Demo';
require 'db.php';
require 'includes/header.php';

// READ — Fetch all products
$products = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll();

// Handle delete success message
$deleted = $_GET['deleted'] ?? false;
$added   = $_GET['added']   ?? false;
$updated = $_GET['updated'] ?? false;
?>

<div class="container py-5">

    <div style="display:flex; justify-content:space-between;
                align-items:center; margin-bottom:28px;">
        <div>
            <h1 style="font-family:var(--font-serif);
                       font-style:italic; color:var(--vv-dark);
                       margin-bottom:4px;">
                Products
            </h1>
            <p style="color:var(--vv-muted); font-size:0.82rem;">
                Week 5 — CRUD Operations Demo
            </p>
        </div>
        <a href="/week1-brenda/Week5b/add.php"
           class="btn btn-vv-primary px-4">
            ➕ Add Product
        </a>
    </div>

    <?php if ($deleted): ?>
        <div class="vv-alert-success">
            ✅ Product deleted successfully!
        </div>
    <?php endif; ?>

    <?php if ($added): ?>
        <div class="vv-alert-success">
            ✅ Product added successfully!
        </div>
    <?php endif; ?>

    <?php if ($updated): ?>
        <div class="vv-alert-success">
            ✅ Product updated successfully!
        </div>
    <?php endif; ?>

    <!-- Week 5 Info Banner -->
    <div style="background:var(--vv-parchment);
                border:1px solid var(--vv-gold);
                padding:16px 20px; margin-bottom:28px;
                border-radius:4px;">
        <p style="font-size:0.82rem; color:var(--vv-text); margin:0;">
            <strong style="color:var(--vv-dark);">
                Week 5 — CRUD Operations:
            </strong>
            This page demonstrates
            <strong>Create</strong> (Add),
            <strong>Read</strong> (View),
            <strong>Update</strong> (Edit) and
            <strong>Delete</strong> operations
            connected to MySQL via PHP PDO.
        </p>
    </div>

    <!-- Products Table -->
    <?php if (empty($products)): ?>
        <div style="text-align:center; padding:60px;
                    color:var(--vv-muted);">
            <p style="font-size:3rem;">🏺</p>
            <p style="font-family:var(--font-serif);
                      font-size:1.2rem; margin-bottom:16px;">
                No products yet
            </p>
            <a href="/week1-brenda/Week5b/add.php"
               class="btn btn-vv-primary">
                Add First Product
            </a>
        </div>
    <?php else: ?>
    <div style="background:#faf6ef;
                border:1px solid var(--vv-parchment);
                border-radius:6px; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;
                      font-size:0.88rem;">
            <thead>
                <tr style="background:var(--vv-parchment);">
                    <th style="padding:12px 16px; text-align:left;
                               font-size:0.68rem; letter-spacing:2px;
                               text-transform:uppercase;
                               color:var(--vv-muted);">ID</th>
                    <th style="padding:12px 16px; text-align:left;
                               font-size:0.68rem; letter-spacing:2px;
                               text-transform:uppercase;
                               color:var(--vv-muted);">Product</th>
                    <th style="padding:12px 16px; text-align:left;
                               font-size:0.68rem; letter-spacing:2px;
                               text-transform:uppercase;
                               color:var(--vv-muted);">Category</th>
                    <th style="padding:12px 16px; text-align:left;
                               font-size:0.68rem; letter-spacing:2px;
                               text-transform:uppercase;
                               color:var(--vv-muted);">Price</th>
                    <th style="padding:12px 16px; text-align:left;
                               font-size:0.68rem; letter-spacing:2px;
                               text-transform:uppercase;
                               color:var(--vv-muted);">Stock</th>
                    <th style="padding:12px 16px; text-align:left;
                               font-size:0.68rem; letter-spacing:2px;
                               text-transform:uppercase;
                               color:var(--vv-muted);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr style="border-bottom:1px solid #f0e8d8;">
                    <td style="padding:12px 16px; color:var(--vv-muted);">
                        #<?= $p['id'] ?>
                    </td>
                    <td style="padding:12px 16px;">
                        <div style="font-family:var(--font-serif);
                                    color:var(--vv-dark);">
                            <?= htmlspecialchars($p['name']) ?>
                        </div>
                        <?php if ($p['description']): ?>
                        <div style="font-size:0.75rem;
                                    color:var(--vv-muted); margin-top:2px;">
                            <?= htmlspecialchars(
                                substr($p['description'], 0, 50)
                            ) ?>...
                        </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 16px;">
                        <?= htmlspecialchars($p['category_name']) ?>
                    </td>
                    <td style="padding:12px 16px; font-weight:700;
                               color:var(--vv-brown);">
                        $<?= number_format($p['price'], 2) ?>
                    </td>
                    <td style="padding:12px 16px;">
                        <span style="padding:3px 10px;
                                     border-radius:12px;
                                     font-size:0.68rem;
                                     background:<?= $p['stock']>0
                                         ? '#d4f0e0':'#fde8d8' ?>;
                                     color:<?= $p['stock']>0
                                         ? '#1a5c36':'#8b3a1a' ?>;">
                            <?= $p['stock'] ?>
                        </span>
                    </td>
                    <td style="padding:12px 16px; white-space:nowrap;">
                        <!-- READ -->
                        <a href="/week1-brenda/Week5b/view.php?id=<?= $p['id'] ?>"
                           style="padding:4px 10px; font-size:0.72rem;
                                  letter-spacing:1px;
                                  text-transform:uppercase;
                                  background:#d4e8f0; color:#1a4a5c;
                                  border:1px solid #a8d0e0;
                                  text-decoration:none;
                                  border-radius:3px; margin-right:4px;">
                            👁️ View
                        </a>
                        <!-- UPDATE -->
                        <a href="/week1-brenda/Week5b/edit.php?id=<?= $p['id'] ?>"
                           style="padding:4px 10px; font-size:0.72rem;
                                  letter-spacing:1px;
                                  text-transform:uppercase;
                                  background:var(--vv-parchment);
                                  color:var(--vv-brown);
                                  border:1px solid var(--vv-gold);
                                  text-decoration:none;
                                  border-radius:3px; margin-right:4px;">
                            ✏️ Edit
                        </a>
                        <!-- DELETE -->
                        <a href="/week1-brenda/Week5b/delete.php?id=<?= $p['id'] ?>"
                           class="btn-confirm-delete"
                           style="padding:4px 10px; font-size:0.72rem;
                                  letter-spacing:1px;
                                  text-transform:uppercase;
                                  background:#fde8e8; color:#8b1a1a;
                                  border:1px solid #f0b8b8;
                                  text-decoration:none;
                                  border-radius:3px;">
                            🗑️ Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="font-size:0.78rem; color:var(--vv-muted);
              margin-top:12px; text-align:right;">
        <?= count($products) ?> product(s) found
    </p>
    <?php endif; ?>

</div>

<?php require 'includes/footer.php'; ?>