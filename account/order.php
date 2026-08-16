<?php
require_once __DIR__ . '/../config/config.php';
require_login();

$orderId = (int) ($_GET['id'] ?? 0);
$order = get_order($orderId, current_user_id());

if (!$order) {
    redirect('account/orders.php');
}

include __DIR__ . '/../includes/header.php';
?>
<h1>Order #<?= $order['id'] ?></h1>

<p>Status: <?= status_badge($order['status']) ?></p>
<p>Placed: <?= sanitize($order['created_at']) ?></p>
<p>Shipping to: <?= sanitize($order['shipping_address']) ?></p>

<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>Product</th><th>Qty</th><th>Price</th></tr></thead>
    <tbody>
    <?php foreach ($order['items'] as $item): ?>
        <tr>
            <td><?= sanitize($item['name']) ?></td>
            <td><?= (int) $item['quantity'] ?></td>
            <td><?= format_price((float) $item['price']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p><strong>Total: <?= format_price((float) $order['total_amount']) ?></strong></p>

<p><a href="<?= BASE_URL ?>/account/orders.php">&larr; Back to my orders</a></p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
