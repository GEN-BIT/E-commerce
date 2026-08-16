<?php
require_once __DIR__ . '/../config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirect('products/index.php?msg=' . urlencode('Invalid request.'));
}

$productId = (int) ($_POST['product_id'] ?? 0);
$redirectTo = $_POST['redirect'] ?? 'products/index.php';

if (is_in_wishlist(current_user_id(), $productId)) {
    remove_from_wishlist(current_user_id(), $productId);
} else {
    add_to_wishlist(current_user_id(), $productId);
}

redirect($redirectTo);
