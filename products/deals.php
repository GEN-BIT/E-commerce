<?php
require_once __DIR__ . '/../config/config.php';

$products = $pdo->query(
    'SELECT * FROM products
     WHERE is_active = 1 AND compare_at_price IS NOT NULL AND compare_at_price > price
     ORDER BY (compare_at_price - price) DESC'
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h1>Today's Deals</h1>
<p class="muted">Products currently marked down from their regular price.</p>

<div class="product-grid">
    <?php foreach ($products as $p): ?>
        <?php include __DIR__ . '/../includes/product-card.php'; ?>
    <?php endforeach; ?>
    <?php if (empty($products)): ?>
        <p>No deals right now — check back soon.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
