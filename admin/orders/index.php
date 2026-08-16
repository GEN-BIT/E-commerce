<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$statusFilter = $_GET['status'] ?? '';
$validStatuses = [ORDER_PENDING, ORDER_CONFIRMED, ORDER_PROCESSING, ORDER_SHIPPED, ORDER_DELIVERED, ORDER_CANCELLED];

if ($statusFilter && in_array($statusFilter, $validStatuses, true)) {
    $stmt = $pdo->prepare(
        'SELECT o.id, o.total_amount, o.status, o.created_at, u.name AS customer_name
         FROM orders o JOIN users u ON u.id = o.user_id
         WHERE o.status = ?
         ORDER BY o.created_at DESC'
    );
    $stmt->execute([$statusFilter]);
} else {
    $stmt = $pdo->query(
        'SELECT o.id, o.total_amount, o.status, o.created_at, u.name AS customer_name
         FROM orders o JOIN users u ON u.id = o.user_id
         ORDER BY o.created_at DESC'
    );
}
$orders = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Manage Orders</h1>

<nav>
    <a href="<?= BASE_URL ?>/admin/orders/index.php">All</a>
    <?php foreach ($validStatuses as $s): ?>
        | <a href="<?= BASE_URL ?>/admin/orders/index.php?status=<?= $s ?>"><?= ucfirst($s) ?></a>
    <?php endforeach; ?>
</nav>

<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td>#<?= $o['id'] ?></td>
            <td><?= sanitize($o['customer_name']) ?></td>
            <td><?= format_price((float) $o['total_amount']) ?></td>
            <td><?= status_badge($o['status']) ?></td>
            <td><?= sanitize($o['created_at']) ?></td>
            <td><a href="<?= BASE_URL ?>/admin/orders/view.php?id=<?= $o['id'] ?>">View</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?>
        <tr><td colspan="6">No orders found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
