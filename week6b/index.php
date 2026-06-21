<?php
// week1/index.php
// The homepage — first page users see

$pageTitle = 'Home';          // Used in the <title> tag inside header.php

require 'db.php';             // Connect to database
require 'includes/header.php'; // Print the <head> and <nav>

// Fetch 6 newest products from the database to display on the homepage
// We JOIN the categories table so we also get the category name
$stmt = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
    LIMIT 6
");
$products = $stmt->fetchAll();  // $products is now an array of rows
?>

<!-- ===== HERO ===== -->
<section class="vv-hero">
    <div class="container">
        <p class="vv-section-title justify-content-center" style="color:#c9b49a">Curated since 2024</p>
        <h1>Treasures from another era</h1>
        <p class="lead">Vintage books · Ceramics · Vinyl · Jewellery · Cameras</p>
        <a href="/vintage-vault/week6b/shop.php" class="vv-hero-btn">Explore the collection</a>
    </div>
</section>

<!-- ===== FEATURED PRODUCTS ===== -->
<section class="container py-5">
    <div class="vv-section-title">Featured items</div>

    <div class="row g-4">
        <?php foreach ($products as $product): ?>
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card vv-product-card border-0 h-100">

                <!-- Product image (or placeholder emoji if no image yet) -->
                <div class="vv-product-img">
                    <?php if ($product['image'] && file_exists("images/" . $product['image'])): ?>
                        <img src="/vintage-vault/week6b/images/<?= htmlspecialchars($product['image']) ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="img-fluid w-100 h-100" style="object-fit:cover">
                    <?php else: ?>
                        🏺  <!-- Placeholder until you add real photos -->
                    <?php endif; ?>
                </div>

                <div class="card-body d-flex flex-column p-3">
                    <div class="vv-product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                    <div class="vv-product-name"><?= htmlspecialchars($product['name']) ?></div>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="vv-product-price">$<?= number_format($product['price'], 2) ?></span>
                        <a href="/vintage-vault/week6b/product.php?id=<?= $product['id'] ?>"
                           class="btn btn-vv-primary btn-sm">View</a>
                    </div>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require 'includes/footer.php'; ?>