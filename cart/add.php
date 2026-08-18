<?php
require_once __DIR__ . '/../config/config.php';
require_login();

if (is_admin()) {
    redirect('admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirect('products/index.php?msg=' . urlencode('Invalid request.'));
}

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity  = (int) ($_POST['quantity'] ?? 1);

try {
    add_to_cart(current_user_id(), $productId, $quantity);
    redirect('cart/index.php');
} catch (InvalidArgumentException $e) {
    redirect('products/product.php?id=' . $productId . '&msg=' . urlencode($e->getMessage()));
}
