<?php
require_once __DIR__ . '/../config/config.php';
require_login();

if (!verify_csrf_token($_GET['csrf_token'] ?? null)) {
    redirect('cart/index.php?msg=' . urlencode('Invalid request.'));
}

$cartItemId = (int) ($_GET['cart_item_id'] ?? 0);
remove_from_cart(current_user_id(), $cartItemId);

redirect('cart/index.php');
