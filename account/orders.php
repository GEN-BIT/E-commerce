<?php
require_once __DIR__ . '/../config/config.php';
require_login();

$orders = get_orders_for_user(current_user_id());

include __DIR__ . '/../includes/header.php';
?>
<h1>My Orders</h1>

<?php if (empty($orders)): ?>
    <p>You haven't placed any orders yet. <a href="<?= BASE_URL ?>/products/index.php">Start shopping</a>.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= sanitize($o['created_at']) ?></td>
                <td><?= format_price((float) $o['total_amount']) ?></td>
                <td><?= status_badge($o['status']) ?></td>
                <td><a href="<?= BASE_URL ?>/account/order.php?id=<?= $o['id'] ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
