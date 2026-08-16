<?php
require_once __DIR__ . '/../config/config.php';

$products = $pdo->query(
    "SELECT p.*, COALESCE(SUM(oi.quantity), 0) AS units_sold
     FROM products p
     LEFT JOIN order_items oi ON oi.product_id = p.id
     LEFT JOIN orders o ON o.id = oi.order_id AND o.status != 'cancelled'
     WHERE p.is_active = 1
     GROUP BY p.id
     ORDER BY units_sold DESC, p.created_at DESC
     LIMIT 24"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h1>Best Sellers</h1>
<p class="muted">Ranked by units sold across all orders.</p>

<div class="product-grid">
    <?php foreach ($products as $p): ?>
        <?php include __DIR__ . '/../includes/product-card.php'; ?>
    <?php endforeach; ?>
    <?php if (empty($products)): ?>
        <p>No sales data yet.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
