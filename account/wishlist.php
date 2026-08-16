<?php
require_once __DIR__ . '/../config/config.php';
require_login();

$items = get_wishlist(current_user_id());

include __DIR__ . '/../includes/header.php';
?>
<h1>My Wishlist</h1>

<?php if (empty($items)): ?>
    <p>Your wishlist is empty. <a href="<?= BASE_URL ?>/products/index.php">Browse products</a>.</p>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($items as $p): ?>
            <div class="product-card">
                <a href="<?= BASE_URL ?>/products/product.php?id=<?= $p['id'] ?>">
                    <?php if ($p['image']): ?>
                        <img src="<?= UPLOAD_PRODUCTS_URL . sanitize($p['image']) ?>" alt="<?= sanitize($p['description'] ?? $p['name']) ?>" title="<?= sanitize($p['description'] ?? $p['name']) ?>" width="160">
                    <?php endif; ?>
                    <h3><?= sanitize($p['name']) ?></h3>
                </a>
                <p><?= format_price((float) $p['price']) ?></p>
                <p><?= $p['stock'] > 0 ? $p['stock'] . ' in stock' : 'Out of stock' ?></p>
                <form method="post" action="<?= BASE_URL ?>/wishlist/toggle.php">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="redirect" value="account/wishlist.php">
                    <button type="submit" class="btn-wishlist active">♥ Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<p><a href="<?= BASE_URL ?>/account/index.php">&larr; Back to account</a></p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
