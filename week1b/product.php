<?php
$pageTitle = 'Product';
require 'db.php';
require 'includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: /vintage-vault/week1b/shop.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /vintage-vault/week1b/shop.php');
    exit;
}

// Reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.name AS user_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$id]);
$reviews   = $stmt->fetchAll();
$avgRating = count($reviews)
    ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1)
    : 0;

// Submit review
$reviewError = $reviewSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $reviewError = 'Please log in to leave a review.';
    } else {
        $rating  = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);
        if ($rating < 1 || $rating > 5) {
            $reviewError = 'Please select a rating.';
        } else {
            $pdo->prepare("
                INSERT INTO reviews
                (user_id, product_id, rating, comment)
                VALUES (?, ?, ?, ?)
            ")->execute([
                $_SESSION['user_id'], $id, $rating, $comment
            ]);
            header("Location: /vintage-vault/week1b/product.php?id=$id");
            exit;
        }
    }
}

// Related products
$related = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.category_id = ? AND p.id != ?
    LIMIT 3
");
$related->execute([$product['category_id'], $id]);
$relatedProducts = $related->fetchAll();
?>

<div class="container py-5">

    <!-- Breadcrumb -->
    <nav style="font-size:0.78rem; color:var(--vv-muted);
                margin-bottom:24px;">
        <a href="/vintage-vault/week1b/index.php"
           style="color:var(--vv-brown);">Home</a>
        <span style="margin:0 8px;">›</span>
        <a href="/vintage-vault/week1b/shop.php"
           style="color:var(--vv-brown);">Shop</a>
        <span style="margin:0 8px;">›</span>
        <a href="/vintage-vault/week1b/shop.php?category=<?= $product['category_id'] ?>"
           style="color:var(--vv-brown);">
            <?= htmlspecialchars($product['category_name']) ?>
        </a>
        <span style="margin:0 8px;">›</span>
        <?= htmlspecialchars($product['name']) ?>
    </nav>

    <!-- Product -->
    <div class="row g-5 mb-5">

        <!-- Image -->
        <div class="col-12 col-md-5">
            <div style="background:var(--vv-parchment);
                        border:1px solid var(--vv-gold);
                        height:380px; display:flex;
                        align-items:center; justify-content:center;
                        font-size:6rem; overflow:hidden;">
                <?php
                $img = 'images/products/' . $product['image'];
                if ($product['image'] !== 'placeholder.jpg'
                    && file_exists($img)):
                ?>
                    <img src="/vintage-vault/week1b/<?= $img ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         style="width:100%; height:100%;
                                object-fit:cover;">
                <?php else: ?>
                    🏺
                <?php endif; ?>
            </div>
        </div>

        <!-- Info -->
        <div class="col-12 col-md-7">
            <div class="vv-product-category">
                <?= htmlspecialchars($product['category_name']) ?>
            </div>
            <h1 style="font-family:var(--font-serif); font-size:2rem;
                       color:var(--vv-dark); margin-bottom:12px;">
                <?= htmlspecialchars($product['name']) ?>
            </h1>

            <?php if ($avgRating > 0): ?>
            <div style="margin-bottom:12px;">
                <?php for ($i=1; $i<=5; $i++): ?>
                    <?= $i <= $avgRating ? '⭐' : '☆' ?>
                <?php endfor; ?>
                <span style="font-size:0.8rem; color:var(--vv-muted);
                             margin-left:6px;">
                    <?= $avgRating ?>/5
                    (<?= count($reviews) ?> reviews)
                </span>
            </div>
            <?php endif; ?>

            <div style="font-size:2rem; font-weight:700;
                        color:var(--vv-brown); margin-bottom:16px;">
                $<?= number_format($product['price'], 2) ?>
            </div>

            <?php if ($product['description']): ?>
            <p style="color:var(--vv-text); line-height:1.8;
                      margin-bottom:24px; font-size:0.95rem;">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>
            <?php endif; ?>

            <div style="margin-bottom:20px; font-size:0.82rem;">
                <?php if ($product['stock'] > 0): ?>
                    <span style="color:#2d7a4f;">
                        ✓ In Stock (<?= $product['stock'] ?> available)
                    </span>
                <?php else: ?>
                    <span style="color:#c0522a;">✗ Sold Out</span>
                <?php endif; ?>
            </div>

            <?php if ($product['stock'] > 0): ?>
                <button class="btn btn-vv-primary px-5 py-2
                               add-to-cart-btn"
                        data-id="<?= $product['id'] ?>"
                        data-name="<?= htmlspecialchars($product['name']) ?>">
                    Add to Cart
                </button>
                <a href="/vintage-vault/week1b/cart.php"
                   style="margin-left:12px; font-size:0.8rem;
                          color:var(--vv-brown);">
                    View Cart →
                </a>
            <?php else: ?>
                <button class="btn w-100 py-2" disabled
                        style="background:var(--vv-parchment);
                               color:var(--vv-muted);">
                    Sold Out
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews -->
    <div style="border-top:1px solid var(--vv-parchment);
                padding-top:40px;">
        <div class="vv-section-title">Customer Reviews</div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                    padding:24px; margin-bottom:32px; max-width:560px;">
            <h5 style="font-family:var(--font-serif); font-style:italic;
                       margin-bottom:16px;">Leave a Review</h5>

            <?php if ($reviewError): ?>
                <div class="vv-alert-error"><?= $reviewError ?></div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom:14px;">
                    <label style="font-size:0.7rem; letter-spacing:2px;
                                  text-transform:uppercase;
                                  color:var(--vv-brown); display:block;
                                  margin-bottom:6px;">Rating</label>
                    <div style="display:flex; gap:8px; font-size:1.6rem;">
                        <?php for ($i=1; $i<=5; $i++): ?>
                        <label style="cursor:pointer;">
                            <input type="radio" name="rating"
                                   value="<?= $i ?>"
                                   style="display:none;">
                            <span class="star-label">☆</span>
                        </label>
                        <?php endfor; ?>
                    </div>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:0.7rem; letter-spacing:2px;
                                  text-transform:uppercase;
                                  color:var(--vv-brown); display:block;
                                  margin-bottom:6px;">Comment</label>
                    <textarea name="comment" rows="3"
                              style="width:100%; padding:10px;
                                     border:1px solid var(--vv-gold);
                                     background:var(--vv-cream);
                                     font-family:var(--font-serif);
                                     resize:vertical;"
                              placeholder="Share your thoughts...">
                    </textarea>
                </div>
                <button type="submit" name="submit_review"
                        class="btn btn-vv-primary px-4">
                    Submit Review
                </button>
            </form>
        </div>
        <?php else: ?>
            <p style="font-size:0.85rem; color:var(--vv-muted);
                      margin-bottom:24px;">
                <a href="/vintage-vault/week1b/login.php">Log in</a>
                to leave a review.
            </p>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <p style="color:var(--vv-muted); font-size:0.88rem;">
                No reviews yet. Be the first!
            </p>
        <?php else: ?>
            <?php foreach ($reviews as $r): ?>
            <div style="border-bottom:1px solid var(--vv-parchment);
                        padding:16px 0;">
                <div style="display:flex; justify-content:space-between;
                            margin-bottom:6px;">
                    <strong style="font-size:0.9rem;">
                        <?= htmlspecialchars($r['user_name']) ?>
                    </strong>
                    <span style="font-size:0.75rem; color:var(--vv-muted);">
                        <?= date('M d, Y', strtotime($r['created_at'])) ?>
                    </span>
                </div>
                <div style="margin-bottom:6px;">
                    <?php for ($i=1; $i<=5; $i++): ?>
                        <?= $i <= $r['rating'] ? '⭐' : '☆' ?>
                    <?php endfor; ?>
                </div>
                <p style="font-size:0.88rem; color:var(--vv-text); margin:0;">
                    <?= htmlspecialchars($r['comment']) ?>
                </p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <div style="margin-top:48px;">
        <div class="vv-section-title">You may also like</div>
        <div class="row g-4">
            <?php foreach ($relatedProducts as $rp): ?>
            <div class="col-12 col-sm-4">
                <div class="card vv-product-card border-0 h-100">
                    <a href="/vintage-vault/week1b/product.php?id=<?= $rp['id'] ?>">
                        <div class="vv-product-img"
                             style="height:160px;">🏺</div>
                    </a>
                    <div class="card-body p-3">
                        <div class="vv-product-category">
                            <?= htmlspecialchars($rp['category_name']) ?>
                        </div>
                        <div class="vv-product-name"
                             style="font-size:0.9rem;">
                            <?= htmlspecialchars($rp['name']) ?>
                        </div>
                        <div class="vv-product-price">
                            $<?= number_format($rp['price'], 2) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
// Star rating interaction
document.querySelectorAll('.star-label').forEach((star, i, stars) => {
    star.addEventListener('click', () => {
        stars.forEach((s, j) => {
            s.textContent = j <= i ? '⭐' : '☆';
        });
        star.closest('label').querySelector('input').checked = true;
    });
    star.addEventListener('mouseover', () => {
        stars.forEach((s, j) => {
            s.textContent = j <= i ? '⭐' : '☆';
        });
    });
});
</script>

<?php require 'includes/footer.php'; ?>