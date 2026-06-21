<?php
$pageTitle = 'Home';
require 'db.php';
require 'includes/header.php';

// Fetch categories
$categories = $pdo->query(
    "SELECT * FROM categories ORDER BY name"
)->fetchAll();

// Fetch featured products
$products = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
    LIMIT 6
")->fetchAll();
?>

<!-- HERO -->
<section class="vv-hero">
    <div class="container" style="position:relative;">
        <p style="font-family:sans-serif; font-size:0.7rem;
                  letter-spacing:3px; color:#c9b49a;
                  text-transform:uppercase; margin-bottom:10px;">
            Curated since 2024
        </p>
        <h1>Treasures from another era</h1>
        <p class="lead">
            Vintage books · Ceramics · Vinyl · Jewellery · Cameras
        </p>

        <!-- Search Bar -->
        <form action="/vintage-vault/week3b//shop.php" method="GET"
              style="display:flex; max-width:480px;
                     margin:20px auto 0; position:relative;">
            <input type="text" name="search" id="heroSearch"
                   placeholder="Search vintage items..."
                   autocomplete="off"
                   style="flex:1; padding:12px 18px;
                          border:1.5px solid var(--vv-gold);
                          background:rgba(255,255,255,0.08);
                          color:#f0e4cc;
                          font-family:var(--font-serif);
                          font-size:0.95rem; outline:none;
                          border-right:none;">
            <button type="submit"
                    style="padding:12px 20px;
                           background:var(--vv-gold);
                           border:none; color:var(--vv-dark);
                           font-size:0.8rem; letter-spacing:1px;
                           text-transform:uppercase; cursor:pointer;">
                Search
            </button>
        </form>

        <!-- Suggestions Dropdown -->
        <div id="searchSuggestions"
             style="display:none; position:absolute; z-index:100;
                    width:100%; max-width:480px;
                    left:50%; transform:translateX(-50%);">
        </div>

        <a href="/vintage-vault/week3b//shop.php" class="vv-hero-btn"
           style="display:inline-block; margin-top:20px;">
            Explore the collection
        </a>
    </div>
</section>

<!-- CATEGORY PILLS -->
<section style="background:var(--vv-parchment);
                border-bottom:1px solid var(--vv-gold);
                padding:14px 0; overflow-x:auto;">
    <div class="container">
        <div style="display:flex; gap:8px;
                    flex-wrap:wrap; align-items:center;">
            <a href="/vintage-vault/week3b//shop.php" class="vv-cat-pill active">
                All Items
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="/vintage-vault/week3b//shop.php?category=<?= $cat['id'] ?>"
                   class="vv-cat-pill">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="container py-5">
    <div class="vv-section-title">Featured items</div>

    <div class="row g-4">
        <?php foreach ($products as $p): ?>
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card vv-product-card border-0 h-100">

                <a href="/vintage-vault/week3b//product.php?id=<?= $p['id'] ?>">
                    <div class="vv-product-img">
                        <?php
                        $img = 'images/products/' . $p['image'];
                        if ($p['image'] !== 'placeholder.jpg'
                            && file_exists($img)):
                        ?>
                            <img src="/vintage-vault/week3b//<?= $img ?>"
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
                    <a href="/vintage-vault/week3b//product.php?id=<?= $p['id'] ?>"
                       style="text-decoration:none;">
                        <div class="vv-product-name">
                            <?= htmlspecialchars($p['name']) ?>
                        </div>
                    </a>
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between
                                    align-items-center mb-2">
                            <span class="vv-product-price">
                                $<?= number_format($p['price'], 2) ?>
                            </span>
                            <span style="font-size:0.7rem;
                                         letter-spacing:1px;
                                         text-transform:uppercase;
                                         color:<?= $p['stock']>0
                                             ? '#2d7a4f' : '#c0522a' ?>">
                                <?= $p['stock'] > 0
                                    ? 'In Stock' : 'Sold Out' ?>
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

    <div class="text-center mt-5">
        <a href="/vintage-vault/week3b//shop.php" class="vv-hero-btn"
           style="color:var(--vv-dark);
                  border-color:var(--vv-brown);">
            View all items →
        </a>
    </div>
</section>

<?php require 'includes/footer.php'; ?>