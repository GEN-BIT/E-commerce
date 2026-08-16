<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$bestSellers = $pdo->query(
    "SELECT p.id, p.name, SUM(oi.quantity) AS units_sold, SUM(oi.quantity * oi.price) AS revenue
     FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     JOIN products p ON p.id = oi.product_id
     WHERE o.status != 'cancelled'
     GROUP BY p.id
     ORDER BY units_sold DESC
     LIMIT 10"
)->fetchAll();

$stockLevels = $pdo->query(
    'SELECT id, name, stock, is_active FROM products ORDER BY stock ASC'
)->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Product Report</h1>

<h2>Best Sellers</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>Product</th><th>Units Sold</th><th>Revenue</th></tr></thead>
    <tbody>
    <?php foreach ($bestSellers as $p): ?>
        <tr>
            <td><a href="<?= BASE_URL ?>/admin/products/view.php?id=<?= $p['id'] ?>"><?= sanitize($p['name']) ?></a></td>
            <td><?= (int) $p['units_sold'] ?></td>
            <td><?= format_price((float) $p['revenue']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($bestSellers)): ?>
        <tr><td colspan="3">No sales yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<h2>Stock Levels</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>Product</th><th>Stock</th><th>Active</th></tr></thead>
    <tbody>
    <?php foreach ($stockLevels as $p): ?>
        <tr style="<?= $p['stock'] <= 5 ? 'color:#b00020;' : '' ?>">
            <td><a href="<?= BASE_URL ?>/admin/products/edit.php?id=<?= $p['id'] ?>"><?= sanitize($p['name']) ?></a></td>
            <td><?= (int) $p['stock'] ?></td>
            <td><?= bool_badge((bool) $p['is_active']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
