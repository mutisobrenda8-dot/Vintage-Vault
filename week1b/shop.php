<?php
$pageTitle = 'Shop';
require 'db.php';
require 'includes/header.php';

$search   = trim($_GET['search']   ?? '');
$category = $_GET['category']      ?? '';
$sort     = $_GET['sort']          ?? 'newest';

$sql    = "
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE 1=1
";
$params = [];

if ($search) {
    $sql     .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}
if ($category) {
    $sql     .= " AND p.category_id = ?";
    $params[] = $category;
}

switch ($sort) {
    case 'price_low':  $sql .= " ORDER BY p.price ASC";       break;
    case 'price_high': $sql .= " ORDER BY p.price DESC";      break;
    case 'name':       $sql .= " ORDER BY p.name ASC";        break;
    default:           $sql .= " ORDER BY p.created_at DESC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id ORDER BY c.name
")->fetchAll();

$currentCat = '';
foreach ($categories as $cat) {
    if ($cat['id'] == $category) {
        $currentCat = $cat['name'];
    }
}
?>

<div class="container py-5">
<div class="row g-4">

    <!-- SIDEBAR -->
    <div class="col-12 col-md-3">
        <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                    padding:20px;">

            <div class="vv-section-title" style="margin-bottom:14px;">
                Search
            </div>
            <form method="GET">
                <input type="text" name="search"
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search items..."
                       style="width:100%; padding:8px 12px;
                              border:1px solid var(--vv-gold);
                              background:var(--vv-cream);
                              font-family:var(--font-serif);
                              color:var(--vv-dark); margin-bottom:8px;">
                <?php if ($category): ?>
                    <input type="hidden" name="category"
                           value="<?= $category ?>">
                <?php endif; ?>
                <button type="submit"
                        class="btn btn-vv-primary w-100">
                    Search
                </button>
            </form>

            <div style="height:1px; background:var(--vv-parchment);
                        margin:20px 0;"></div>

            <div class="vv-section-title" style="margin-bottom:14px;">
                Categories
            </div>
            <nav style="display:flex; flex-direction:column; gap:4px;">
                <a href="/week1-brenda/week1b/shop.php"
                   style="padding:8px 12px; font-size:0.82rem;
                          text-decoration:none;
                          background:<?= !$category
                              ? 'var(--vv-dark)' : 'transparent' ?>;
                          color:<?= !$category
                              ? '#f0e4cc' : 'var(--vv-text)' ?>;">
                    All Categories
                </a>
                <?php foreach ($categories as $cat): ?>
                <a href="/week1-brenda/week1b/shop.php?category=<?= $cat['id'] ?>"
                   style="padding:8px 12px; font-size:0.82rem;
                          text-decoration:none;
                          background:<?= $category==$cat['id']
                              ? 'var(--vv-dark)' : 'transparent' ?>;
                          color:<?= $category==$cat['id']
                              ? '#f0e4cc' : 'var(--vv-text)' ?>;">
                    <?= htmlspecialchars($cat['name']) ?>
                    <span style="float:right; font-size:0.75rem;
                                 color:var(--vv-muted);">
                        <?= $cat['product_count'] ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </nav>

            <div style="height:1px; background:var(--vv-parchment);
                        margin:20px 0;"></div>

            <div class="vv-section-title" style="margin-bottom:14px;">
                Sort By
            </div>
            <form method="GET">
                <?php if ($search): ?>
                    <input type="hidden" name="search"
                           value="<?= htmlspecialchars($search) ?>">
                <?php endif; ?>
                <?php if ($category): ?>
                    <input type="hidden" name="category"
                           value="<?= $category ?>">
                <?php endif; ?>
                <select name="sort" onchange="this.form.submit()"
                        style="width:100%; padding:8px 12px;
                               border:1px solid var(--vv-gold);
                               background:var(--vv-cream);
                               color:var(--vv-dark); font-size:0.85rem;">
                    <option value="newest"
                        <?= $sort==='newest' ? 'selected' : '' ?>>
                        Newest First
                    </option>
                    <option value="price_low"
                        <?= $sort==='price_low' ? 'selected' : '' ?>>
                        Price: Low to High
                    </option>
                    <option value="price_high"
                        <?= $sort==='price_high' ? 'selected' : '' ?>>
                        Price: High to Low
                    </option>
                    <option value="name"
                        <?= $sort==='name' ? 'selected' : '' ?>>
                        Name A–Z
                    </option>
                </select>
            </form>
        </div>
    </div>

    <!-- PRODUCTS -->
    <div class="col-12 col-md-9">

        <div style="display:flex; justify-content:space-between;
                    align-items:center; margin-bottom:20px;">
            <div>
                <h2 style="font-family:var(--font-serif);
                           font-style:italic; font-size:1.5rem;
                           color:var(--vv-dark); margin:0;">
                    <?php if ($search): ?>
                        Results for "<?= htmlspecialchars($search) ?>"
                    <?php elseif ($currentCat): ?>
                        <?= htmlspecialchars($currentCat) ?>
                    <?php else: ?>
                        All Items
                    <?php endif; ?>
                </h2>
                <p style="font-size:0.78rem; color:var(--vv-muted); margin:0;">
                    <?= count($products) ?> item(s) found
                </p>
            </div>
            <?php if ($search || $category): ?>
                <a href="/week1-brenda/week1b/shop.php"
                   style="font-size:0.75rem; letter-spacing:1px;
                          text-transform:uppercase; color:var(--vv-brown);">
                    ✕ Clear
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($products)): ?>
            <div style="text-align:center; padding:60px 20px;
                        color:var(--vv-muted);">
                <p style="font-size:3rem;">🏺</p>
                <p style="font-family:var(--font-serif); font-size:1.2rem;">
                    No items found
                </p>
                <a href="/week1-brenda/week1b/shop.php" class="btn btn-vv-primary mt-3">
                    View all items
                </a>
            </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $p): ?>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card vv-product-card border-0 h-100">
                    <a href="/week1-brenda/week1b/product.php?id=<?= $p['id'] ?>">
                        <div class="vv-product-img">
                            <?php
                            $img = 'images/products/' . $p['image'];
                            if ($p['image'] !== 'placeholder.jpg'
                                && file_exists($img)):
                            ?>
                                <img src="/week1-brenda/week1b/<?= $img ?>"
                                     alt="<?= htmlspecialchars($p['name']) ?>">
                            <?php else: ?>
                                🏺
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="card-body d-flex flex-column p-3">
                        <div class="vv-product-category">
                            <?= htmlspecialchars($p['category_name']) ?>
                        </div>
                        <a href="/week1-brenda/week1b/product.php?id=<?= $p['id'] ?>"
                           style="text-decoration:none;">
                            <div class="vv-product-name">
                                <?= htmlspecialchars($p['name']) ?>
                            </div>
                        </a>
                        <?php if ($p['description']): ?>
                        <p style="font-size:0.8rem; color:var(--vv-muted);
                                  flex:1; margin-bottom:12px;">
                            <?= htmlspecialchars(
                                substr($p['description'], 0, 80)
                            ) ?>...
                        </p>
                        <?php endif; ?>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between
                                        align-items-center mb-2">
                                <span class="vv-product-price">
                                    $<?= number_format($p['price'], 2) ?>
                                </span>
                                <span style="font-size:0.7rem;
                                             text-transform:uppercase;
                                             color:<?= $p['stock']>0
                                                 ? '#2d7a4f':'#c0522a' ?>">
                                    <?= $p['stock']>0
                                        ? 'In Stock':'Sold Out' ?>
                                </span>
                            </div>
                            <?php if ($p['stock'] > 0): ?>
                                <button class="btn btn-vv-primary w-100
                                               add-to-cart-btn"
                                        data-id="<?= $p['id'] ?>"
                                        data-name="<?= htmlspecialchars($p['name']) ?>">
                                    Add to Cart
                                </button>
                            <?php else: ?>
                                <button class="btn w-100" disabled
                                        style="background:var(--vv-parchment);
                                               color:var(--vv-muted);
                                               font-size:0.75rem;">
                                    Sold Out
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>
</div>

<?php require 'includes/footer.php'; ?>