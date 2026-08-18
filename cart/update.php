<?php
require_once __DIR__ . '/../config/config.php';
require_login();

if (is_admin()) {
    redirect('admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirect('cart/index.php?msg=' . urlencode('Invalid request.'));
}

$cartItemId = (int) ($_POST['cart_item_id'] ?? 0);
$quantity   = (int) ($_POST['quantity'] ?? 1);

try {
    update_cart_item(current_user_id(), $cartItemId, $quantity);
} catch (InvalidArgumentException $e) {
    redirect('cart/index.php?msg=' . urlencode($e->getMessage()));
}

redirect('cart/index.php');
