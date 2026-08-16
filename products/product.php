<?php
require_once __DIR__ . '/../config/config.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.is_active = 1'
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('products/index.php?msg=' . urlencode('Product not found.'));
}

$gallery = get_product_images($id);
$ratingSummary = get_product_rating_summary($id);
$reviews = get_product_reviews($id);

$reviewErrors = [];
$reviewSuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    require_login();

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $reviewErrors[] = 'Invalid form submission. Please try again.';
    } else {
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        try {
            add_review(current_user_id(), $id, $rating, $comment);
            redirect('products/product.php?id=' . $id . '#reviews');
        } catch (InvalidArgumentException $e) {
            $reviewErrors[] = $e->getMessage();
        }
    }
}

$related = [];
if ($product['category_id']) {
    $stmt = $pdo->prepare(
        'SELECT * FROM products WHERE is_active = 1 AND category_id = ? AND id != ? ORDER BY created_at DESC LIMIT 4'
    );
    $stmt->execute([$product['category_id'], $id]);
    $related = $stmt->fetchAll();
}

$inWishlist = is_logged_in() && is_in_wishlist(current_user_id(), $id);
$onSale = $product['compare_at_price'] && $product['compare_at_price'] > $product['price'];

include __DIR__ . '/../includes/header.php';
?>
<p><a href="<?= BASE_URL ?>/products/index.php">&larr; Back to products</a></p>

<div class="product-detail">
    <div class="product-gallery">
        <?php if ($product['image']): ?>
            <img id="mainImage" class="gallery-main" src="<?= UPLOAD_PRODUCTS_URL . sanitize($product['image']) ?>" alt="<?= sanitize($product['description'] ?? $product['name']) ?>" title="<?= sanitize($product['description'] ?? $product['name']) ?>">
        <?php else: ?>
            <div class="gallery-main gallery-main-empty"></div>
        <?php endif; ?>

        <?php if (!empty($gallery)): ?>
            <div class="gallery-thumbs">
                <?php if ($product['image']): ?>
                    <img class="gallery-thumb active" src="<?= UPLOAD_PRODUCTS_URL . sanitize($product['image']) ?>" data-full="<?= UPLOAD_PRODUCTS_URL . sanitize($product['image']) ?>" alt="<?= sanitize($product['description'] ?? $product['name']) ?>">
                <?php endif; ?>
                <?php foreach ($gallery as $img): ?>
                    <img class="gallery-thumb" src="<?= UPLOAD_PRODUCTS_URL . sanitize($img['image']) ?>" data-full="<?= UPLOAD_PRODUCTS_URL . sanitize($img['image']) ?>" alt="<?= sanitize($product['description'] ?? $product['name']) ?>">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="product-info">
        <h1><?= sanitize($product['name']) ?></h1>

        <?php if ($ratingSummary['count'] > 0): ?>
            <p class="rating-summary">
                <span class="stars"><?= render_stars($ratingSummary['average']) ?></span>
                <?= $ratingSummary['average'] ?> (<?= $ratingSummary['count'] ?> review<?= $ratingSummary['count'] === 1 ? '' : 's' ?>)
            </p>
        <?php else: ?>
            <p class="rating-summary muted">No reviews yet</p>
        <?php endif; ?>

        <p>Category: <?= sanitize($product['category_name']) ?></p>

        <p class="price-block">
            <?php if ($onSale): ?>
                <span class="price-current price-sale"><?= format_price((float) $product['price']) ?></span>
                <span class="price-compare"><?= format_price((float) $product['compare_at_price']) ?></span>
                <span class="badge badge-cancelled">SALE</span>
            <?php else: ?>
                <span class="price-current"><?= format_price((float) $product['price']) ?></span>
            <?php endif; ?>
        </p>

        <p><?= sanitize($product['description'] ?? '') ?></p>

        <?php if (!empty($_GET['msg'])): ?>
            <p class="form-error"><?= sanitize($_GET['msg']) ?></p>
        <?php endif; ?>

        <?php if ($product['stock'] > 0): ?>
            <p><?= (int) $product['stock'] ?> in stock</p>

            <?php if (is_logged_in()): ?>
                <form method="post" action="<?= BASE_URL ?>/cart/add.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= (int) $product['stock'] ?>">
                    <button type="submit">Add to Cart</button>
                </form>

                <form method="post" action="<?= BASE_URL ?>/wishlist/toggle.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="redirect" value="products/product.php?id=<?= $product['id'] ?>">
                    <button type="submit" class="btn-wishlist <?= $inWishlist ? 'active' : '' ?>">
                        <?= $inWishlist ? '♥ Saved to Wishlist' : '♡ Add to Wishlist' ?>
                    </button>
                </form>
            <?php else: ?>
                <p><a href="<?= BASE_URL ?>/auth/signin.php">Login</a> to add this to your cart or wishlist.</p>
            <?php endif; ?>
        <?php else: ?>
            <p><strong>Out of stock</strong></p>
        <?php endif; ?>
    </div>
</div>

<h2 id="reviews">Reviews</h2>

<?php foreach ($reviewErrors as $error): ?>
    <p class="form-error"><?= sanitize($error) ?></p>
<?php endforeach; ?>

<?php if (is_logged_in() && !user_has_reviewed(current_user_id(), $id)): ?>
    <form method="post" action="<?= BASE_URL ?>/products/product.php?id=<?= $id ?>#reviews" class="review-form">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="submit_review" value="1">

        <label for="rating">Your Rating</label>
        <select id="rating" name="rating" required>
            <option value="">-- Select --</option>
            <option value="5">★★★★★ Excellent</option>
            <option value="4">★★★★☆ Good</option>
            <option value="3">★★★☆☆ Average</option>
            <option value="2">★★☆☆☆ Poor</option>
            <option value="1">★☆☆☆☆ Bad</option>
        </select>

        <label for="comment">Comment (optional)</label>
        <textarea id="comment" name="comment" rows="3"></textarea>

        <button type="submit">Submit Review</button>
    </form>
<?php elseif (!is_logged_in()): ?>
    <p><a href="<?= BASE_URL ?>/auth/signin.php">Login</a> to leave a review.</p>
<?php endif; ?>

<div class="review-list">
    <?php foreach ($reviews as $r): ?>
        <div class="review-item">
            <p class="review-meta">
                <span class="stars"><?= render_stars((float) $r['rating']) ?></span>
                <strong><?= sanitize($r['reviewer_name']) ?></strong>
                <span class="muted"><?= sanitize($r['created_at']) ?></span>
            </p>
            <?php if ($r['comment']): ?>
                <p><?= sanitize($r['comment']) ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (empty($reviews)): ?>
        <p class="muted">Be the first to review this product.</p>
    <?php endif; ?>
</div>

<?php if (!empty($related)): ?>
    <h2>Related Products</h2>
    <div class="product-grid">
        <?php foreach ($related as $p): ?>
            <?php include __DIR__ . '/../includes/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
