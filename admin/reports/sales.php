<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$daily = $pdo->query(
    "SELECT DATE(created_at) AS day, COUNT(*) AS order_count, SUM(total_amount) AS revenue
     FROM orders
     WHERE status != 'cancelled' AND created_at >= (CURDATE() - INTERVAL 30 DAY)
     GROUP BY DATE(created_at)
     ORDER BY day DESC"
)->fetchAll();

$byStatus = $pdo->query(
    'SELECT status, COUNT(*) AS order_count, SUM(total_amount) AS revenue
     FROM orders
     GROUP BY status'
)->fetchAll();

$totalRevenue = (float) $pdo->query(
    "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE status != 'cancelled'"
)->fetch()['total'];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Sales Report</h1>

<p><strong>All-time revenue: <?= format_price($totalRevenue) ?></strong></p>

<h2>By Status</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>Status</th><th>Orders</th><th>Revenue</th></tr></thead>
    <tbody>
    <?php foreach ($byStatus as $row): ?>
        <tr>
            <td><?= sanitize(ucfirst($row['status'])) ?></td>
            <td><?= (int) $row['order_count'] ?></td>
            <td><?= format_price((float) $row['revenue']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Last 30 Days</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
    <tbody>
    <?php foreach ($daily as $row): ?>
        <tr>
            <td><?= sanitize($row['day']) ?></td>
            <td><?= (int) $row['order_count'] ?></td>
            <td><?= format_price((float) $row['revenue']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($daily)): ?>
        <tr><td colspan="3">No sales in the last 30 days.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
