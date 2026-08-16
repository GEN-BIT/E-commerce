<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

if (!verify_csrf_token($_GET['csrf_token'] ?? null)) {
    redirect('admin/products/index.php?msg=' . urlencode('Invalid request.'));
}

$imageId   = (int) ($_GET['id'] ?? 0);
$productId = (int) ($_GET['product_id'] ?? 0);

$stmt = $pdo->prepare('SELECT image FROM product_images WHERE id = ?');
$stmt->execute([$imageId]);
$img = $stmt->fetch();

if ($img) {
    $stmt = $pdo->prepare('DELETE FROM product_images WHERE id = ?');
    $stmt->execute([$imageId]);

    if (is_file(UPLOAD_PRODUCTS_DIR . $img['image'])) {
        unlink(UPLOAD_PRODUCTS_DIR . $img['image']);
    }
}

redirect('admin/products/edit.php?id=' . $productId . '&msg=' . urlencode('Image removed.'));
