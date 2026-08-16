<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

if (!verify_csrf_token($_GET['csrf_token'] ?? null)) {
    redirect('admin/products/index.php?msg=' . urlencode('Invalid request.'));
}

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if ($product) {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);

    if ($product['image'] && is_file(UPLOAD_PRODUCTS_DIR . $product['image'])) {
        unlink(UPLOAD_PRODUCTS_DIR . $product['image']);
    }

    log_action(current_user_id(), 'product_deleted', "product_id={$id}");
}

redirect('admin/products/index.php?msg=' . urlencode('Product deleted.'));
