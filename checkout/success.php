<?php
require_once __DIR__ . '/../config/config.php';
require_login();

if (is_admin()) {
    redirect('admin/index.php');
}

$orderId = (int) ($_GET['order_id'] ?? 0);
$order = get_order($orderId, current_user_id());

if (!$order) {
    redirect('account/orders.php');
}

include __DIR__ . '/../includes/header.php';
?>
<div class="checkout-steps">
    <span class="step"><a href="<?= BASE_URL ?>/cart/index.php">1. Cart</a></span>
    <span class="sep">&rarr;</span>
    <span class="step"><a href="<?= BASE_URL ?>/checkout/index.php">2. Checkout</a></span>
    <span class="sep">&rarr;</span>
    <span class="step active">3. Confirmation</span>
</div>

<h1>Order Confirmed</h1>

<div class="receipt-card">
    <div class="receipt-header">
        <span class="stamp">Order #<?= $order['id'] ?> &middot; <?= sanitize(strtoupper($order['status'])) ?></span>
        <p style="margin: 0;">Thanks for your order.</p>
    </div>

    <table>
        <tbody>
        <?php foreach ($order['items'] as $item): ?>
            <tr>
                <td><?= sanitize($item['name']) ?></td>
                <td><?= (int) $item['quantity'] ?>&times;</td>
                <td style="text-align: right;"><?= format_price((float) $item['price'] * $item['quantity']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="receipt-total"><span>Total</span><span><?= format_price((float) $order['total_amount']) ?></span></div>

    <p style="margin-top: 1.5rem; font-size: .9rem; color: var(--color-ink-soft);">
        Shipping to: <?= sanitize($order['shipping_address']) ?>
    </p>
</div>

<p><a href="<?= BASE_URL ?>/account/orders.php">View all my orders</a></p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
