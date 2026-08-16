<?php
require_once __DIR__ . '/../config/config.php';
require_login();

$items = get_cart_items(current_user_id());
$total = get_cart_total(current_user_id());

include __DIR__ . '/../includes/header.php';
?>
<div class="checkout-steps">
    <span class="step active">1. Cart</span>
    <span class="sep">&rarr;</span>
    <span class="step">2. Checkout</span>
    <span class="sep">&rarr;</span>
    <span class="step">3. Confirmation</span>
</div>

<h1>Your Cart</h1>

<?php if (!empty($_GET['msg'])): ?>
    <p class="form-error"><?= sanitize($_GET['msg']) ?></p>
<?php endif; ?>

<?php if (empty($items)): ?>
    <p>Your cart is empty. <a href="<?= BASE_URL ?>/products/index.php">Browse products</a>.</p>
<?php else: ?>
    <div class="cart-layout">
        <div class="cart-items">
            <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <?php if ($item['image']): ?>
                        <img src="<?= UPLOAD_PRODUCTS_URL . sanitize($item['image']) ?>" alt="<?= sanitize($item['name']) ?>">
                    <?php else: ?>
                        <div class="cart-item-thumb-empty"></div>
                    <?php endif; ?>

                    <div>
                        <p class="cart-item-name"><?= sanitize($item['name']) ?></p>
                        <p class="cart-item-price"><?= format_price((float) $item['price']) ?> each</p>
                    </div>

                    <form method="post" action="<?= BASE_URL ?>/cart/update.php" class="cart-item-form">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                        <div class="qty-stepper">
                            <button type="button" class="qty-decr" aria-label="Decrease quantity">&minus;</button>
                            <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" max="<?= (int) $item['stock'] ?>">
                            <button type="button" class="qty-incr" aria-label="Increase quantity">&plus;</button>
                        </div>
                        <button type="submit">Update</button>
                    </form>

                    <div class="cart-item-subtotal"><?= format_price((float) $item['price'] * $item['quantity']) ?></div>

                    <a class="cart-item-remove"
                       href="<?= BASE_URL ?>/cart/remove.php?cart_item_id=<?= $item['cart_item_id'] ?>&csrf_token=<?= generate_csrf_token() ?>"
                       onclick="return confirm('Remove this item?')">Remove</a>
                </div>
            <?php endforeach; ?>
        </div>

        <aside class="cart-summary">
            <h2>Order Summary</h2>
            <div class="summary-row"><span>Items</span><span><?= array_sum(array_column($items, 'quantity')) ?></span></div>
            <div class="summary-total"><span>Total</span><span><?= format_price($total) ?></span></div>
            <a class="btn-checkout" href="<?= BASE_URL ?>/checkout/index.php">Proceed to Checkout</a>
            <a class="link-clear"
               href="<?= BASE_URL ?>/cart/clear.php?csrf_token=<?= generate_csrf_token() ?>"
               onclick="return confirm('Empty your whole cart?')">Clear cart</a>
        </aside>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
