<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$id = (int) ($_GET['id'] ?? 0);
$order = get_order($id); // admin access - no user_id scoping

if (!$order) {
    redirect('admin/orders/index.php?msg=' . urlencode('Order not found.'));
}

$stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
$stmt->execute([$order['user_id']]);
$customer = $stmt->fetch();

$validStatuses = [ORDER_PENDING, ORDER_CONFIRMED, ORDER_PROCESSING, ORDER_SHIPPED, ORDER_DELIVERED, ORDER_CANCELLED];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Order #<?= $order['id'] ?> <?= status_badge($order['status']) ?></h1>

<?php if (!empty($_GET['msg'])): ?>
    <p class="form-success"><?= sanitize($_GET['msg']) ?></p>
<?php endif; ?>

<p>Customer: <?= sanitize($customer['name']) ?> (<?= sanitize($customer['email']) ?>)</p>
<p>Placed: <?= sanitize($order['created_at']) ?></p>
<p>Shipping to: <?= sanitize($order['shipping_address']) ?><?= $order['shipping_phone'] ? ' — ' . sanitize($order['shipping_phone']) : '' ?></p>

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

<h2>Update Status</h2>
<form method="post" action="<?= BASE_URL ?>/admin/orders/update-status.php">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

    <label for="status">Status</label>
    <select id="status" name="status">
        <?php foreach ($validStatuses as $s): ?>
            <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Update Status</button>
</form>

<p><a href="<?= BASE_URL ?>/admin/orders/index.php">&larr; Back to orders</a></p>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
