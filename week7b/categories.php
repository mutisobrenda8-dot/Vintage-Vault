<?php
$pageTitle = 'Categories';
require 'db.php';
require 'includes/header.php';

// Fetch all categories with product count
$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY c.name
")->fetchAll();

// Category icons
$icons = [
    'Books'        => '📖',
    'Vinyl'        => '🎵',
    'Cameras'      => '📷',
    'Jewellery'    => '💎',
    'Ceramics'     => '🏺',
    'Home Decor'   => '🕯️',
    'Toys'         => '🎲',
    'Furniture'    => '🪑',
    'Collectibles' => '🗿',
];
?>

<div class="container py-5">

    <h1 style="font-family:var(--font-serif); font-style:italic;
               color:var(--vv-dark); margin-bottom:4px;">
        Categories
    </h1>
    <p style="color:var(--vv-muted); font-size:0.82rem;
              margin-bottom:40px;">
        Browse our collection by category
    </p>

    <div class="row g-4">
        <?php foreach ($categories as $cat): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <a href="/vintage-vault/week7b/shop.php?category=<?= $cat['id'] ?>"
               style="text-decoration:none;">
                <div style="background:#faf6ef;
                            border:1px solid var(--vv-parchment);
                            padding:32px 20px; text-align:center;
                            transition:all 0.2s;"
                     onmouseover="this.style.background='var(--vv-dark)';
                                  this.style.borderColor='var(--vv-dark)';"
                     onmouseout="this.style.background='#faf6ef';
                                 this.style.borderColor='var(--vv-parchment)';">

                    <div style="font-size:2.5rem; margin-bottom:12px;">
                        <?= $icons[$cat['name']] ?? '🏺' ?>
                    </div>

                    <div style="font-family:var(--font-serif);
                                font-size:1.1rem; color:var(--vv-dark);
                                margin-bottom:6px;">
                        <?= htmlspecialchars($cat['name']) ?>
                    </div>

                    <div style="font-size:0.75rem; letter-spacing:2px;
                                text-transform:uppercase;
                                color:var(--vv-muted);">
                        <?= $cat['product_count'] ?> item(s)
                    </div>

                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require 'includes/footer.php'; ?>