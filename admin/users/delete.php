<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

if (!verify_csrf_token($_GET['csrf_token'] ?? null)) {
    redirect('admin/users/index.php?msg=' . urlencode('Invalid request.'));
}

$id = (int) ($_GET['id'] ?? 0);

if ($id === current_user_id()) {
    redirect('admin/users/index.php?msg=' . urlencode('You cannot delete your own account.'));
}

try {
    $pdo->beginTransaction();

    $pdo->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM reviews WHERE user_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM wishlist WHERE user_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM orders WHERE user_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);

    $pdo->commit();

    log_action(current_user_id(), 'user_deleted', "user_id={$id}");

    redirect('admin/users/index.php?msg=' . urlencode('User permanently deleted.'));
} catch (Exception $e) {
    $pdo->rollBack();
    redirect('admin/users/index.php?msg=' . urlencode('Delete failed: ' . $e->getMessage()));
}
