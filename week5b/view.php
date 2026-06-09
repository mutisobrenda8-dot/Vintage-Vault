<?php
// Week5/view.php
// CRUD — READ single product

$pageTitle = 'View Product';
require 'db.php';
require 'includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: /week1-brenda/Week5b/index.php');
    exit;
}

// READ — Fetch single product
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /week1-brenda/Week5b/index.php');
    exit;
}
?>

<div class="container py-5">
    <div style="max-width:700px; margin:0 auto;">

        <div style="margin-bottom:24px;">
            <a href="/week1-brenda/Week5b/index.php"
               style="font-size:0.78rem; letter-spacing:1px;
                      text-transform:uppercase; color:var(--vv-muted);">
                ← Back to Products
            </a>
        </div>

        <h1 style="font-family:var(--font-serif); font-style:italic;
                   color:var(--vv-dark); margin-bottom:4px;">
            View Product
        </h1>
        <p style="color:var(--vv-muted); font-size:0.82rem;
                  margin-bottom:28px;">
            Week 5 — READ Operation (SELECT WHERE id = ?)
        </p>

        <!-- SQL Preview -->
        <div style="background:#2a1e14; border-radius:6px;
                    padding:16px; margin-bottom:24px;
                    font-family:monospace; font-size:0.82rem;
                    color:#c9b49a;">
            <span style="color:#7a9b4a;">SELECT</span> * 
            <span style="color:#7a9b4a;">FROM</span> products 
            <span style="color:#7a9b4a;">WHERE</span>
            id = <?= $id ?>;
        </div>

        <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                    border-radius:6px; overflow:hidden;">

            <div style="padding:24px; background:var(--vv-parchment);
                        border-bottom:1px solid var(--vv-gold);
                        display:flex; justify-content:space-between;
                        align-items:center;">
                <h2 style="font-family:var(--font-serif);
                           font-style:italic; color:var(--vv-dark);
                           margin:0; font-size:1.4rem;">
                    <?= htmlspecialchars($product['name']) ?>
                </h2>
                <span style="padding:3px 10px; border-radius:12px;
                             font-size:0.68rem; font-weight:700;
                             background:<?= $product['stock']>0
                                 ? '#d4f0e0':'#fde8d8' ?>;
                             color:<?= $product['stock']>0
                                 ? '#1a5c36':'#8b3a1a' ?>;">
                    <?= $product['stock']>0 ? 'In Stock':'Sold Out' ?>
                </span>
            </div>

            <div style="padding:24px;">
                <table style="width:100%; font-size:0.88rem;
                              border-collapse:collapse;">
                    <?php
                    $fields = [
                        'ID'          => '#' . $product['id'],
                        'Category'    => $product['category_name'],
                        'Price'       => '$' . number_format($product['price'],2),
                        'Stock'       => $product['stock'],
                        'Description' => $product['description'] ?: '—',
                        'Created'     => date('M d, Y H:i',
                            strtotime($product['created_at'])),
                    ];
                    foreach ($fields as $label => $value):
                    ?>
                    <tr style="border-bottom:1px solid var(--vv-parchment);">
                        <td style="padding:12px 0; width:140px;
                                   font-size:0.7rem; letter-spacing:2px;
                                   text-transform:uppercase;
                                   color:var(--vv-muted);">
                            <?= $label ?>
                        </td>
                        <td style="padding:12px 0; color:var(--vv-dark);">
                            <?= htmlspecialchars((string)$value) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <div style="display:flex; gap:12px; margin-top:24px;">
                    <a href="/week1-brenda/Week5b/edit.php?id=<?= $product['id'] ?>"
                       class="btn btn-vv-primary px-4">
                        ✏️ Edit Product
                    </a>
                    <a href="/week1-brenda/Week5b/delete.php?id=<?= $product['id'] ?>"
                       class="btn-confirm-delete"
                       style="padding:8px 20px;
                              background:#fde8e8; color:#8b1a1a;
                              border:1px solid #f0b8b8;
                              font-size:0.75rem; letter-spacing:1.5px;
                              text-transform:uppercase;
                              text-decoration:none; border-radius:0;">
                        🗑️ Delete
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>