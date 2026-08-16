<?php
require_once __DIR__ . '/../includes/admin-auth.php';

$productCount  = (int) $pdo->query('SELECT COUNT(*) AS cnt FROM products')->fetch()['cnt'];
$customerCount = (int) $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'customer'")->fetch()['cnt'];
$orderCount    = (int) $pdo->query('SELECT COUNT(*) AS cnt FROM orders')->fetch()['cnt'];
$revenue       = (float) $pdo->query(
    "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE status != 'cancelled'"
)->fetch()['total'];

$recentOrders = $pdo->query(
    'SELECT o.id, o.total_amount, o.status, o.created_at, u.name AS customer_name
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC
     LIMIT 10'
)->fetchAll();

$lowStock = $pdo->query(
    'SELECT id, name, stock FROM products WHERE stock <= 5 AND is_active = 1 ORDER BY stock ASC LIMIT 5'
)->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<h1>Admin Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card"><h3><?= $productCount ?></h3><p>Products</p></div>
    <div class="stat-card"><h3><?= $customerCount ?></h3><p>Customers</p></div>
    <div class="stat-card"><h3><?= $orderCount ?></h3><p>Orders</p></div>
    <div class="stat-card"><h3><?= format_price($revenue) ?></h3><p>Revenue</p></div>
</div>

<h2>Recent Orders</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($recentOrders as $o): ?>
        <tr>
            <td><a href="<?= BASE_URL ?>/admin/orders/view.php?id=<?= $o['id'] ?>">#<?= $o['id'] ?></a></td>
            <td><?= sanitize($o['customer_name']) ?></td>
            <td><?= format_price((float) $o['total_amount']) ?></td>
            <td><?= status_badge($o['status']) ?></td>
            <td><?= sanitize($o['created_at']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($recentOrders)): ?>
        <tr><td colspan="5">No orders yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($lowStock)): ?>
    <h2>Low Stock</h2>
    <ul>
        <?php foreach ($lowStock as $p): ?>
            <li>
                <a href="<?= BASE_URL ?>/admin/products/edit.php?id=<?= $p['id'] ?>"><?= sanitize($p['name']) ?></a>
                — <?= (int) $p['stock'] ?> left
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
