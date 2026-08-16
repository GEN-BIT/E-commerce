<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

if (!verify_csrf_token($_GET['csrf_token'] ?? null)) {
    redirect('admin/categories/index.php?msg=' . urlencode('Invalid request.'));
}

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM products WHERE category_id = ?');
$stmt->execute([$id]);
if ((int) $stmt->fetch()['cnt'] > 0) {
    redirect('admin/categories/index.php?msg=' . urlencode('Move or delete its products first.'));
}

$stmt = $pdo->prepare('SELECT image FROM categories WHERE id = ?');
$stmt->execute([$id]);
$category = $stmt->fetch();

if ($category) {
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$id]);

    if ($category['image'] && is_file(UPLOAD_CATEGORIES_DIR . $category['image'])) {
        unlink(UPLOAD_CATEGORIES_DIR . $category['image']);
    }
}

redirect('admin/categories/index.php?msg=' . urlencode('Category deleted.'));
