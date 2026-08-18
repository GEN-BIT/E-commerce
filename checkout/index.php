<?php
require_once __DIR__ . '/../config/config.php';
require_login();

if (is_admin()) {
    redirect('admin/index.php');
}

$items = get_cart_items(current_user_id());
$total = get_cart_total(current_user_id());

if (empty($items)) {
    redirect('cart/index.php?msg=' . urlencode('Your cart is empty.'));
}

$stmt = $pdo->prepare('SELECT address, phone FROM users WHERE id = ?');
$stmt->execute([current_user_id()]);
$user = $stmt->fetch();

$address = $user['address'] ?? '';
$phone   = $user['phone'] ?? '';

$errorMsg = $_GET['msg'] ?? '';

include __DIR__ . '/../includes/header.php';
?>
<div class="checkout-steps">
    <span class="step"><a href="<?= BASE_URL ?>/cart/index.php">1. Cart</a></span>
    <span class="sep">&rarr;</span>
    <span class="step active">2. Checkout</span>
    <span class="sep">&rarr;</span>
    <span class="step">3. Confirmation</span>
</div>

<h1>Checkout</h1>

<?php if ($errorMsg): ?>
    <p class="form-error"><?= sanitize($errorMsg) ?></p>
<?php endif; ?>

<div class="checkout-layout">
    <form method="post" action="<?= BASE_URL ?>/checkout/place-order.php">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <label for="address">Delivery Address</label>
        <textarea id="address" name="address" rows="3" required><?= sanitize($address) ?></textarea>

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?= sanitize($phone) ?>">

        <button type="submit">Place Order</button>
    </form>

    <aside class="cart-summary">
        <h2>Order Summary</h2>
        <?php foreach ($items as $item): ?>
            <div class="summary-row">
                <span><?= sanitize($item['name']) ?> &times; <?= (int) $item['quantity'] ?></span>
                <span><?= format_price((float) $item['price'] * $item['quantity']) ?></span>
            </div>
        <?php endforeach; ?>
        <div class="summary-total"><span>Total</span><span><?= format_price($total) ?></span></div>
    </aside>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
