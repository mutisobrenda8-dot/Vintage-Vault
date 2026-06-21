<?php
// Week5/edit.php
// CRUD — UPDATE operation

$pageTitle = 'Edit Product';
require 'db.php';
require 'includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: /vintage-vault/week1b//index.php');
    exit;
}

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /vintage-vault/week1b//index.php');
    exit;
}

// Fetch categories
$categories = $pdo->query(
    "SELECT * FROM categories ORDER BY name"
)->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price']       ?? '');
    $stock       = trim($_POST['stock']       ?? '');
    $category_id = $_POST['category_id']      ?? '';

    if (empty($name) || empty($price) || empty($category_id)) {
        $error = 'Name, price and category are required.';
    } else {
        // UPDATE — Update record in database
        $pdo->prepare("
            UPDATE products
            SET category_id=?, name=?, description=?,
                price=?, stock=?
            WHERE id=?
        ")->execute([
            $category_id, $name, $description, $price, $stock, $id
        ]);

        header('Location: /vintage-vault/week1b//index.php?updated=1');
        exit;
    }
}
?>

<div class="container py-5">
    <div style="max-width:600px; margin:0 auto;">

        <div style="margin-bottom:24px;">
            <a href="/vintage-vault/week1b//index.php"
               style="font-size:0.78rem; letter-spacing:1px;
                      text-transform:uppercase; color:var(--vv-muted);">
                ← Back to Products
            </a>
        </div>

        <h1 style="font-family:var(--font-serif); font-style:italic;
                   color:var(--vv-dark); margin-bottom:4px;">
            Edit Product
        </h1>
        <p style="color:var(--vv-muted); font-size:0.82rem;
                  margin-bottom:28px;">
            Week 5 — UPDATE Operation (UPDATE products SET ... WHERE id=?)
        </p>

        <!-- SQL Preview -->
        <div style="background:#2a1e14; border-radius:6px;
                    padding:16px; margin-bottom:24px;
                    font-family:monospace; font-size:0.82rem;
                    color:#c9b49a;">
            <span style="color:#7a9b4a;">UPDATE</span> products
            <span style="color:#7a9b4a;">SET</span>
            name=?, price=?, stock=?
            <span style="color:#7a9b4a;">WHERE</span>
            id = <?= $id ?>;
        </div>

        <?php if ($error): ?>
            <div class="vv-alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                    border-radius:6px; padding:28px;">
            <form method="POST">

                <div class="mb-3">
                    <label style="font-size:0.7rem; letter-spacing:2px;
                                  text-transform:uppercase;
                                  color:var(--vv-brown); font-weight:700;
                                  display:block; margin-bottom:6px;">
                        Product Name *
                    </label>
                    <input type="text" name="name"
                           class="form-control"
                           style="border:1px solid var(--vv-gold);
                                  background:var(--vv-cream);
                                  border-radius:0;"
                           value="<?= htmlspecialchars(
                               $_POST['name'] ?? $product['name']
                           ) ?>" required>
                </div>

                <div class="mb-3">
                    <label style="font-size:0.7rem; letter-spacing:2px;
                                  text-transform:uppercase;
                                  color:var(--vv-brown); font-weight:700;
                                  display:block; margin-bottom:6px;">
                        Category *
                    </label>
                    <select name="category_id"
                            class="form-control"
                            style="border:1px solid var(--vv-gold);
                                   background:var(--vv-cream);
                                   border-radius:0;" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= ($product['category_id'] == $cat['id'])
                                    ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label style="font-size:0.7rem; letter-spacing:2px;
                                      text-transform:uppercase;
                                      color:var(--vv-brown); font-weight:700;
                                      display:block; margin-bottom:6px;">
                            Price ($) *
                        </label>
                        <input type="number" name="price"
                               class="form-control"
                               style="border:1px solid var(--vv-gold);
                                      background:var(--vv-cream);
                                      border-radius:0;"
                               step="0.01" min="0"
                               value="<?= htmlspecialchars(
                                   $_POST['price'] ?? $product['price']
                               ) ?>" required>
                    </div>
                    <div class="col-6">
                        <label style="font-size:0.7rem; letter-spacing:2px;
                                      text-transform:uppercase;
                                      color:var(--vv-brown); font-weight:700;
                                      display:block; margin-bottom:6px;">
                            Stock
                        </label>
                        <input type="number" name="stock"
                               class="form-control"
                               style="border:1px solid var(--vv-gold);
                                      background:var(--vv-cream);
                                      border-radius:0;"
                               min="0"
                               value="<?= htmlspecialchars(
                                   $_POST['stock'] ?? $product['stock']
                               ) ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label style="font-size:0.7rem; letter-spacing:2px;
                                  text-transform:uppercase;
                                  color:var(--vv-brown); font-weight:700;
                                  display:block; margin-bottom:6px;">
                        Description
                    </label>
                    <textarea name="description" rows="4"
                              class="form-control"
                              style="border:1px solid var(--vv-gold);
                                     background:var(--vv-cream);
                                     border-radius:0; resize:vertical;">
                        <?= htmlspecialchars(
                            $_POST['description'] ?? $product['description']
                        ) ?>
                    </textarea>
                </div>

                <div style="display:flex; gap:12px;">
                    <button type="submit"
                            class="btn btn-vv-primary px-4 py-2">
                        💾 Save Changes
                    </button>
                    <a href="/vintage-vault/week1b//index.php"
                       class="btn btn-vv-primary px-4 py-2"
                       style="background:var(--vv-parchment);
                              color:var(--vv-brown);">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>